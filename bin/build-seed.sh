#!/usr/bin/env bash
# Build the committed, sanitized seed dump used by the functional and acceptance suites.
#
#   bin/build-seed.sh site                 from a fresh copy of the site's database (read only)
#   bin/build-seed.sh [SOURCE_DUMP [OUT]]  from a dump file (default: re-scrub the committed dump)
#
# The dump is committed to git, so it must contain no personal data, no credentials and nothing
# environment-specific. This script never writes to the developer's own database: it loads the
# source into the disposable seed database, scrubs it there, and exports the result. Never
# commit a raw site export (wp db export, Akeeba, a hosting backup).
#
# Acceptance tests never rely on existing users: every Cest creates its own in _before.
# The seed therefore keeps exactly one synthetic admin and no real accounts, entries or logs.
#
# What is baked in is config that does not depend on this repo's code (plugin state, dummy
# keys). What follows the repo is applied afterwards by bin/ci-fixtures.sh, in CI and locally.
set -euo pipefail
# shellcheck disable=SC1091
source "$(dirname "$0")/_common.sh"

OUT="${2:-tests/_support/Data/db/dump.sql}"
src_tmp=""
if [ "${1:-}" = "site" ]; then
    src_tmp="$(mktemp)"
    # Reads the site's database; --single-transaction keeps it from locking anything.
    mysqldump_ --single-transaction --no-tablespaces --skip-lock-tables --set-gtid-purged=OFF "$SITE_DB" > "$src_tmp"
    SRC="$src_tmp"
else
    SRC="${1:-tests/_support/Data/db/dump.sql}"
fi
[ -s "$SRC" ] || { echo "Source dump not found or empty: $SRC" >&2; exit 1; }

tmp="$(mktemp)"
trap 'rm -f "$tmp" "$src_tmp"' EXIT

mysql_ -e "DROP DATABASE IF EXISTS \`$SEED_DB\`; CREATE DATABASE \`$SEED_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql_ "$SEED_DB" < "$SRC"
use_seed_database

# The site's plugins run while the seed is being built, and one that is broken or missing must
# not stop it; the scrub itself needs none of them.
wp() { command wp --skip-plugins --skip-themes "$@"; }

# People, logs, submissions and caches: empty the tables that hold nothing else. Run again
# before the export, because running WordPress below queues new rows and transients.
STANDARD='users|usermeta|comments|commentmeta|gf_entry|gf_entry_meta|gf_entry_notes|gf_draft_submissions|gf_form_view|gf_form_revisions|wpmailsmtp_[a-z0-9_]*|redirection_404|redirection_logs|actionscheduler_actions|actionscheduler_logs|actionscheduler_claims|wf[a-z0-9_]*|um_metadata|ak_[a-z0-9_]*|akeeba_common|wpgmza_nominatim_geocode_cache|flamingo_[a-z0-9_]*|wc_(orders|order_[a-z0-9_]*|customer_lookup|webhooks)|woocommerce_(sessions|api_keys|log)|e_submissions[a-z0-9_]*|wpforms_(entries|entry_[a-z0-9_]*|logs|payments[a-z0-9_]*)|frm_(items|item_metas)|nf3_(objects|object_meta|relationships)|simple_history[a-z0-9_]*|itsec_[a-z0-9_]*|aiowps_[a-z0-9_]*|yoast_indexable[a-z0-9_]*|litespeed_[a-z0-9_]*'
empty_tables() {
    local pattern="$STANDARD"
    [ -n "$SEED_EMPTY_TABLES_EXTRA" ] && pattern="$pattern|$SEED_EMPTY_TABLES_EXTRA"
    {
        echo 'SET FOREIGN_KEY_CHECKS=0;'
        mysql_ "$SEED_DB" -N -e "
            SELECT CONCAT('TRUNCATE TABLE \`', table_name, '\`;') FROM information_schema.tables
            WHERE table_schema = '$SEED_DB' AND table_name REGEXP '^${DB_PREFIX}(${pattern})\$'"
        echo 'SET FOREIGN_KEY_CHECKS=1;'
    } | mysql_ "$SEED_DB"
}

# 1. Empty them (all of them, users included; the synthetic admin is created below).
empty_tables

# 2. Row-level scrub of options and content.
sed "s/__PREFIX__/${DB_PREFIX}/g; s/__SEED_DB__/${SEED_DB}/g" bin/scrub-seed.sql | mysql_ "$SEED_DB" >/dev/null

# Every e-mail address left, in any table. wp-cli rewrites serialized values correctly, which
# SQL cannot. The lookahead spares file names such as logo@2x.png.
wp search-replace \
    '[A-Za-z0-9._%+-]+@(?![A-Za-z0-9.-]*\.(?:png|jpe?g|gif|webp|avif|svg|css|js)\b)[A-Za-z0-9.-]+\.[A-Za-z]{2,}' \
    'contact@seed.test' --regex --all-tables-with-prefix --skip-columns=guid --quiet

# 3. The one synthetic admin (ID 1: the auto-increment restarted with the TRUNCATE above,
#    and the scrub pointed every post at it). The password is the CI test password.
wp user create seedadmin seedadmin@seed.test --role=administrator --user_pass='password123!test' --porcelain >/dev/null
[ "$(wp eval 'echo count(get_users()) . ":" . get_users()[0]->ID;' | tail -n 1)" = "1:1" ] || { echo "Expected exactly one user, with ID 1." >&2; exit 1; }

# 4. Config that does not follow the repo.
wp core update-db
# Never show the "verify your admin e-mail" interstitial on admin logins.
wp option update admin_email_lifespan 2533080438
# Route outgoing mail through PHP mail() (Mailpit locally and in CI) and drop stored credentials.
wp eval '
    if (get_option("wp_mail_smtp") !== false) {
        update_option("wp_mail_smtp", ["mail" => ["mailer" => "mail", "from_email" => "wordpress@seed.test", "from_name" => "Seed", "return_path" => false]]);
    }
'
# Plugins that exist on this machine but not in CI (not Composer-managed, not tracked).
for plugin in $SEED_DEACTIVATE_PLUGINS; do
    wp plugin deactivate "$plugin" || true
done

# 5. Sweep what step 4 regenerated, then export and prove the result is clean before it
#    replaces the committed file.
mysql_ "$SEED_DB" -e "DELETE FROM \`${DB_PREFIX}options\` WHERE option_name LIKE '\\_transient\\_%' OR option_name LIKE '\\_site\\_transient\\_%'"
mysql_ "$SEED_DB" -N -e "
    SELECT CONCAT('TRUNCATE TABLE \`', table_name, '\`;') FROM information_schema.tables
    WHERE table_schema = '$SEED_DB' AND table_name REGEXP '^${DB_PREFIX}(actionscheduler_actions|actionscheduler_logs|actionscheduler_claims|wpmailsmtp_[a-z0-9_]*)\$'
" | mysql_ "$SEED_DB"

mysqldump_ --skip-comments --no-tablespaces --skip-lock-tables --set-gtid-purged=OFF "$SEED_DB" > "$tmp"
php bin/check-seed-clean.php "$tmp"
mkdir -p "$(dirname "$OUT")"
chmod 644 "$tmp"
mv "$tmp" "$OUT"
echo "Wrote $OUT ($(wc -c < "$OUT" | tr -d ' ') bytes)."
echo "Now run bin/reset-seed-db.sh to prepare the seed database for tests."
