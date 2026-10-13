#!/bin/bash
#
# deploy.sh - deploy this repo into the docroot. Runs ON THE SERVER, from a checkout of the repo.
#
# MODEL: the repo tracks the site's own code and the tooling, but NOT WordPress core and NOT the
# third-party plugins and theme: Composer installs those (composer.lock), and core is pinned in
# composer.lock too (roots/wordpress-no-content). So a deploy is four steps:
#
#   1. update the checkout
#   2. composer install --no-dev   (puts the locked plugins/theme into the checkout, and
#                                   WordPress core from vendor/: bin/install-core.php)
#   3. rsync, in two passes:
#        a. the checkout as an OVERLAY, WITHOUT --delete (a --delete would wipe wp-admin,
#           wp-includes and uploads). What must not be deployed (wp-config.php, uploads, cache,
#           vendor, all the dev tooling) is declared in the repo's own .deployignore.
#        b. each Composer-managed plugin/theme folder on its own, WITH --delete, so files an
#           update removed do not linger in the docroot.
#   4. database upgrades (needs wp-cli), then fix permissions.
#
#   deploy.sh [branch] [--yes] [--allow-downgrade]
#
# It refuses to replace a plugin or theme with an OLDER version than the server runs: that means
# the server was updated outside the repo, and the lock file has to catch up first (merge the
# update PR, or deploy its branch). --allow-downgrade overrides, for a deliberate rollback.
#
# The repo is private: the checkout needs a credential that can fetch it (a deploy key), and
# Composer needs one for the private packages (composer config -g github-oauth.github.com <token>).

set -euo pipefail

# ---- config -------------------------------------------------------------
SITE_NAME="${SITE_NAME:-mycodelicforest.org}"
LOCALSITES="${LOCALSITES:-/volume1/LocalSites}"
SITE_ROOT="${LOCALSITES}/${SITE_NAME}"
REPO="${SITE_ROOT}/app/deploy-repo"
PUBLIC="${SITE_ROOT}/app/public"
FIX_PERMS="${LOCALSITES}/bin/fix_cms_perms-v2.sh"
SUDO="${SUDO-sudo}"   # SUDO= runs without it, for a server where the deploy user owns the docroot

BRANCH=""
ASSUME_YES="${DEPLOY_YES:-0}"
ALLOW_DOWNGRADE=0
for arg in "$@"; do
    case "${arg}" in
        -y|--yes) ASSUME_YES=1 ;;
        --allow-downgrade) ALLOW_DOWNGRADE=1 ;;
        '') ;;
        *) BRANCH="${arg}" ;;   # optional branch; empty = use origin's default branch
    esac
done

confirm() {
    [ "${ASSUME_YES}" = "1" ] && return 0
    local ans
    read -r -p "$1 [y/N] " ans
    case "${ans}" in y|Y) return 0 ;; *) return 1 ;; esac
}

# ---- sanity checks ------------------------------------------------------
case "${SITE_NAME}" in
    ''|CHANGE-ME*) echo "ERROR: set SITE_NAME at the top of $0 (the folder under ${LOCALSITES})"; exit 1 ;;
esac
[ -d "${REPO}/.git" ] || { echo "ERROR: no git repo at ${REPO}"; exit 1; }
# Through sudo: the docroot belongs to the web server, and the deploy user may not see into it.
${SUDO} test -d "${PUBLIC}" || { echo "ERROR: docroot missing: ${PUBLIC}"; exit 1; }
${SUDO} test -f "${PUBLIC}/wp-load.php" || { echo "ERROR: ${PUBLIC} holds no WordPress (no wp-load.php)"; exit 1; }
command -v composer >/dev/null || { echo "ERROR: composer not found: the plugins cannot be installed"; exit 1; }

# ---- 1. update the checkout ---------------------------------------------
echo "==> Fetching latest..."
git -C "${REPO}" fetch --prune origin

if [ -z "${BRANCH}" ]; then
    BRANCH="$(git -C "${REPO}" symbolic-ref --quiet --short refs/remotes/origin/HEAD 2>/dev/null | sed 's|^origin/||')"
    [ -z "${BRANCH}" ] && BRANCH="$(git -C "${REPO}" rev-parse --abbrev-ref HEAD)"
fi
echo "==> Deploying branch: ${BRANCH}"

git -C "${REPO}" checkout "${BRANCH}"
git -C "${REPO}" reset --hard "origin/${BRANCH}"
# Leaves ignored files alone, which is where Composer's folders and vendor/ live.
git -C "${REPO}" clean -fd

NEWREV="$(git -C "${REPO}" rev-parse --short HEAD)"
echo "==> At commit ${NEWREV}: $(git -C "${REPO}" log -1 --pretty=%s)"

# ---- 2. the plugins and theme the lock file names ------------------------
echo "==> Installing Composer packages (no dev tooling)..."
( cd "${REPO}" && composer install --no-dev --no-interaction --no-progress --optimize-autoloader )

# Folders Composer owns, from the lock file: type wordpress-plugin / wordpress-theme.
MANAGED="$(cd "${REPO}" && php -r '
    $lock = json_decode(file_get_contents("composer.lock"), true);
    foreach ($lock["packages"] as $p) {
        $dir = ["wordpress-plugin" => "plugins", "wordpress-theme" => "themes"][$p["type"] ?? ""] ?? null;
        if ($dir) {
            echo "wp-content/{$dir}/" . basename($p["name"]) . "\t" . $p["version"] . "\n";
        }
    }
')"
[ -n "${MANAGED}" ] || echo "WARN: composer.lock names no plugin or theme."

# Folders tracked in git because Composer cannot install them. They update themselves on the
# server (or are updated by hand), so the repo's copy can be the older one.
TRACKED="$(git -C "${REPO}" ls-tree -d --name-only HEAD wp-content/plugins/ wp-content/themes/ 2>/dev/null || true)"

# The version a plugin or theme folder declares, or nothing. $1 = folder, $2 = sudo or empty.
version_in() {
    local main
    main="$($2 grep -lE '^[ \t/*#@]*(Plugin Name|Theme Name):' "$1"/*.php "$1/style.css" 2>/dev/null | head -n 1 || true)"
    [ -n "${main}" ] && $2 sed -n 's/^[ \t\/*#@]*Version:[ \t]*//p' "${main}" | head -n 1 | tr -d '\r'
    return 0
}
is_older() {   # is $2 older than $1 ?
    [ -n "$1" ] && [ -n "$2" ] && [ "$1" != "$2" ] && [ "$(printf '%s\n%s\n' "$1" "$2" | sort -V | tail -n 1)" = "$1" ]
}

WANT_CORE="$(cd "${REPO}" && php -r 'foreach (json_decode(file_get_contents("composer.lock"), true)["packages"] as $p) { if ($p["name"] === "roots/wordpress-no-content") { echo ltrim($p["version"], "v"); } }' 2>/dev/null || true)"
HAVE_CORE="$(${SUDO} sed -n "s/^\$wp_version = '\([^']*\)';.*/\1/p" "${PUBLIC}/wp-includes/version.php")"

# ---- confirm ------------------------------------------------------------
echo
echo "    WordPress core: ${HAVE_CORE} on the server, ${WANT_CORE:-?} in the repo"
echo "    Composer-managed folders (replaced, removed files are deleted):"
DOWNGRADES=0
while IFS=$'\t' read -r dir version; do
    [ -n "${dir}" ] || continue
    have="$(version_in "${PUBLIC}/${dir}" "${SUDO}")"
    want="${version#v}"
    note=""
    case "${want}" in
        dev-*) ;;   # a branch: nothing to compare
        *) if is_older "${have}" "${want}"; then note="   <-- DOWNGRADE"; DOWNGRADES=$((DOWNGRADES + 1)); fi ;;
    esac
    printf '      %-52s %s -> %s%s\n' "${dir}" "${have:--}" "${want}" "${note}"
done <<< "${MANAGED}"

SKIP_TRACKED=()
if [ -n "${TRACKED}" ]; then
    echo "    Tracked in git (overlaid, nothing is deleted):"
    while IFS= read -r dir; do
        [ -n "${dir}" ] || continue
        have="$(version_in "${PUBLIC}/${dir}" "${SUDO}")"
        want="$(version_in "${REPO}/${dir}" "")"
        note=""
        if is_older "${have}" "${want}"; then
            # Writing older files over a newer plugin would leave a mix of two versions.
            note="   <-- server is newer: LEFT ALONE, update the repo's copy"
            SKIP_TRACKED+=(--exclude="/${dir}/")
        fi
        printf '      %-52s %s -> %s%s\n' "${dir}" "${have:--}" "${want:-?}" "${note}"
    done <<< "${TRACKED}"
fi
echo

if [ "${DOWNGRADES}" -gt 0 ]; then
    echo "    ${DOWNGRADES} package(s) would be replaced with an OLDER version than the server runs."
    echo "    The server was updated outside the repo. A plugin older than its own database"
    echo "    tables can break the site, so the lock file has to catch up first:"
    echo "      - merge the update PR and deploy again, or"
    echo "      - deploy the update branch:  $0 update-wordpress-and-plugins"
    if [ "${ALLOW_DOWNGRADE}" != "1" ]; then
        echo "    Nothing was changed. (--allow-downgrade overrides, for a deliberate rollback.)"
        exit 1
    fi
    echo "    Continuing because of --allow-downgrade."
    echo
fi

# Core comes with the overlay, from the checkout. Never older than what the server runs.
CORE_EXCLUDES=()
if [ -n "${WANT_CORE}" ] && [ -n "${HAVE_CORE}" ] && is_older "${HAVE_CORE}" "${WANT_CORE}"; then
    echo "    WordPress ${HAVE_CORE} on the server is newer than ${WANT_CORE} in the repo: core is LEFT ALONE."
    echo "    Update the lock file (the nightly PR does), or the server was updated from wp-admin."
    echo
    for p in wp-admin wp-includes index.php wp-activate.php wp-blog-header.php wp-comments-post.php \
             wp-config-sample.php wp-cron.php wp-links-opml.php wp-load.php wp-login.php wp-mail.php \
             wp-settings.php wp-signup.php wp-trackback.php xmlrpc.php license.txt readme.html; do
        CORE_EXCLUDES+=(--exclude="/${p}")
    done
fi

confirm "Sync into ${PUBLIC} ?" || { echo "Aborted."; exit 0; }

# From here on files change. Whatever happens next, ownership and permissions are put right
# before the script ends: files copied with sudo are root's until then, and the web server
# may not be able to read them.
fix_permissions() {
    if [ -x "${FIX_PERMS}" ]; then
        echo "==> Fixing permissions..."
        ${SUDO} bash "${FIX_PERMS}" "${SITE_NAME}" || echo "WARN: ${FIX_PERMS} failed - run it by hand."
    else
        echo "WARN: ${FIX_PERMS} not found/executable - skipping perms fix."
    fi
}
trap 'status=$?; trap - EXIT; [ "${status}" -eq 0 ] || echo "ERROR: the deploy stopped early (exit ${status})."; fix_permissions; exit "${status}"' EXIT

# ---- 3a. rsync overlay into docroot (NO --delete) -----------------------
echo "==> Syncing files..."
IGNORE_ARG=()
[ -f "${REPO}/.deployignore" ] && IGNORE_ARG=(--exclude-from="${REPO}/.deployignore")

# The Composer-managed folders are left to pass 3b.
MANAGED_EXCLUDES=()
while IFS=$'\t' read -r dir _; do
    [ -n "${dir}" ] && MANAGED_EXCLUDES+=(--exclude="/${dir}/")
done <<< "${MANAGED}"

# Contents only, never attributes: -a would stamp the checkout's owner and mode onto the docroot
# and every folder in it, and lock the web server out until the permissions fix has run. On a
# Synology the checkout's mode bits are all zeros (access comes from an ACL, which rsync does
# not copy), so new files and folders are given plain modes rather than the checkout's.
RSYNC=(rsync -rlt --omit-dir-times --no-perms --no-owner --no-group --chmod=Du=rwx,Dgo=rx,Fu=rw,Fgo=r)

${SUDO} "${RSYNC[@]}" --stats \
    --exclude='.git/' \
    --exclude='deploy-repo/' \
    --exclude='/wp-config.php' \
    --exclude='/wp-content/uploads/' \
    --exclude='/vendor/' \
    --exclude='/tests/' \
    --exclude='/bin/' \
    ${MANAGED_EXCLUDES[@]+"${MANAGED_EXCLUDES[@]}"} \
    ${SKIP_TRACKED[@]+"${SKIP_TRACKED[@]}"} \
    ${CORE_EXCLUDES[@]+"${CORE_EXCLUDES[@]}"} \
    ${IGNORE_ARG[@]+"${IGNORE_ARG[@]}"} \
    "${REPO}/" "${PUBLIC}/"

# ---- 3b. Composer-managed folders, one by one (WITH --delete) ------------
# .deploykeep names what a plugin wrote into its own folder on the server (backups, generated
# settings). rsync "protects" those: they are never deleted, whatever the repo holds.
KEEP=()
if [ -f "${REPO}/.deploykeep" ]; then
    while IFS= read -r pattern; do
        case "${pattern}" in ''|'#'*) continue ;; esac
        KEEP+=(--filter="P ${pattern}")
    done < "${REPO}/.deploykeep"
fi
while IFS=$'\t' read -r dir _; do
    [ -n "${dir}" ] || continue
    [ -d "${REPO}/${dir}" ] || { echo "WARN: ${dir} is in composer.lock but was not installed - skipped."; continue; }
    echo "==> ${dir}"
    ${SUDO} mkdir -p "${PUBLIC}/${dir}"
    ${SUDO} "${RSYNC[@]}" --delete ${KEEP[@]+"${KEEP[@]}"} --exclude='.git/' "${REPO}/${dir}/" "${PUBLIC}/${dir}/"
done <<< "${MANAGED}"

# ---- 4. database upgrades -----------------------------------------------
WP=()
if command -v wp >/dev/null; then
    # wp-cli runs as whoever owns the uploads folder, which this script never writes to (the
    # files it just copied are root's until the permissions fix). GNU stat, then BSD stat.
    OWNER="$(${SUDO} stat -c '%U' "${PUBLIC}/wp-content/uploads" 2>/dev/null \
        || ${SUDO} stat -f '%Su' "${PUBLIC}/wp-content/uploads" 2>/dev/null || true)"
    if [ -z "${SUDO}" ]; then
        WP=(wp --path="${PUBLIC}")
    elif [ -n "${OWNER}" ] && [ "${OWNER}" != "UNKNOWN" ]; then
        WP=(${SUDO} -u "${OWNER}" wp --path="${PUBLIC}")
    else
        echo "WARN: could not tell who owns ${PUBLIC}/wp-content/uploads - skipping the wp-cli steps."
    fi
fi

if [ "${#WP[@]}" -gt 0 ]; then
    echo "==> Database upgrades..."
    "${WP[@]}" core update-db || echo "WARN: wp core update-db failed - open wp-admin to finish the upgrade."
    "${WP[@]}" cache flush >/dev/null 2>&1 || true
fi

# ---- 5. fix ownership / permissions: the EXIT trap above ----------------
echo "==> Done. Deployed ${SITE_NAME} @ ${NEWREV} (branch ${BRANCH})."
