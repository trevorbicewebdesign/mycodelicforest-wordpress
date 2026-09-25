#!/usr/bin/env bash
# Build the committed, sanitized seed dump used by the functional and acceptance suites.
#
#   bin/build-seed.sh [SOURCE_DUMP [OUTPUT_DUMP]]
#
# The dump is committed to a public repository, so it must contain no personal data, no
# credentials and nothing environment-specific. This script never touches the developer's
# `local` database: it loads SOURCE_DUMP into the disposable `seed` database, scrubs it
# there, and exports the result. Never commit a raw site export (wp db export / Akeeba).
#
# Acceptance tests never rely on existing users: every Cest creates its own in _before.
# The seed therefore keeps exactly one synthetic admin and no real accounts, entries or logs.
#
# What is baked in is config that does not depend on this repo's code (plugin state, dummy
# keys). What stays in CI (see "Prepare test fixtures" in codeception-test.yml) is anything
# that follows the repo: camp-manager's tables (created when it is activated, so schema
# changes are picked up), the profile form fixture, and `wp core update-db`.
#
# Needs `wp` (PHP 8.4) and `mysql`/`mysqldump` on PATH pointed at Local's MySQL
# (Local's "Open site shell" does this).
set -euo pipefail
cd "$(dirname "$0")/.."

SRC="${1:-tests/_support/Data/db/dump.sql}"
OUT="${2:-tests/_support/Data/db/dump.sql}"
[ -f "$SRC" ] || { echo "Source dump not found: $SRC" >&2; exit 1; }

# wp-config.php selects the seed database when this is set (PHP CLI mirrors env into $_SERVER).
export HTTP_X_TEST_REQUEST=1
# CiviCRM has no tables in seed and fatals on boot on a machine with a civicrm.settings.php.
wp() { command wp --skip-plugins=civicrm "$@"; }

mysql -u root -proot -e "DROP DATABASE IF EXISTS seed; CREATE DATABASE seed"
mysql -u root -proot seed < "$SRC"

# Take only the last line: PHP startup warnings from a mismatched local PHP go to stdout.
db=$(wp eval 'global $wpdb; echo $wpdb->dbname;' | tail -n 1)
if [ "$db" != "seed" ]; then
    echo "wp-cli is pointed at '$db', not 'seed'; refusing to continue." >&2
    exit 1
fi

# People, logs and submissions: empty the tables that hold nothing else. Run again before
# the export, because running WordPress below queues new Action Scheduler rows and transients.
empty_personal_tables() {
    mysql -u root -proot seed -N -e "
        SELECT CONCAT('TRUNCATE TABLE \`', table_name, '\`;') FROM information_schema.tables
        WHERE table_schema = 'seed' AND table_name REGEXP
          '^wp_(users|usermeta|comments|commentmeta|gf_entry|gf_entry_meta|gf_entry_notes|gf_draft_submissions|gf_form_view|wpmailsmtp_debug_events|wpmailsmtp_tasks_meta|redirection_404|redirection_logs|actionscheduler_actions|actionscheduler_logs|actionscheduler_claims|wf[a-z0-9_]*)$'
    " | mysql -u root -proot seed
}

# 1. Empty them (all of them, users included; the synthetic admin is created below).
empty_personal_tables

# 2. Row-level scrub of options and content.
mysql -u root -proot seed < bin/scrub-seed.sql

# Addresses written into published content (done here, not in SQL: see scrub-seed.sql).
wp eval '
    global $wpdb;
    $re = "/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/";
    foreach ($wpdb->get_results("SELECT ID, post_title, post_content, post_excerpt FROM {$wpdb->posts}") as $p) {
        $new = [
            "post_title"   => preg_replace($re, "contact@seed.test", $p->post_title),
            "post_content" => preg_replace($re, "contact@seed.test", $p->post_content),
            "post_excerpt" => preg_replace($re, "contact@seed.test", $p->post_excerpt),
        ];
        if ($new !== ["post_title" => $p->post_title, "post_content" => $p->post_content, "post_excerpt" => $p->post_excerpt]) {
            $wpdb->update($wpdb->posts, $new, ["ID" => $p->ID]);
        }
    }
' >/dev/null

# 3. The one synthetic admin (ID 1: the auto-increment restarted with the TRUNCATE above,
#    and the scrub pointed every post at it). The password is the CI test password.
wp user create seedadmin seedadmin@seed.test --role=administrator --user_pass='password123!test' --porcelain >/dev/null
[ "$(wp eval 'echo count(get_users()) . ":" . get_users()[0]->ID;' | tail -n 1)" = "1:1" ] || { echo "Expected exactly one user, with ID 1." >&2; exit 1; }

# 4. Config that does not follow the repo.
wp core update-db
# Never show the "verify your admin e-mail" interstitial on admin logins.
wp option update admin_email_lifespan 2533080438
# Cloudflare Turnstile always-pass test keys, so the registration form can be submitted by a
# headless browser (production keys only work on the real domain).
wp option update gravityformsaddon_ss88-gravity-forms-turnstile_settings \
    '{"ct_site_key":"1x00000000000000000000AA","ct_secret_key":"1x0000000000000000000000000000000AA"}' --format=json
# Route outgoing mail through PHP mail() (Mailpit in CI); the option may not exist.
wp option patch update wp_mail_smtp mail mailer mail || true
# CiviCRM is listed active but has no tables/settings here; it would only add installer notices.
wp plugin deactivate civicrm || true
wp plugin deactivate camp-manager || true   # CI activates it, which creates its tables

# 5. Sweep what step 4 regenerated (running WordPress caches transients and queues actions),
#    then export and prove the result is clean before it replaces the committed file.
mysql -u root -proot seed -e "DELETE FROM wp_options WHERE option_name LIKE '\\_transient\\_%' OR option_name LIKE '\\_site\\_transient\\_%'"
mysql -u root -proot seed -N -e "
    SELECT CONCAT('TRUNCATE TABLE \`', table_name, '\`;') FROM information_schema.tables
    WHERE table_schema = 'seed' AND table_name REGEXP '^wp_(actionscheduler_actions|actionscheduler_logs|actionscheduler_claims|wpmailsmtp_debug_events|wpmailsmtp_tasks_meta)$'
" | mysql -u root -proot seed
tmp="$(mktemp)"
trap 'rm -f "$tmp"' EXIT
mysqldump -u root -proot --skip-comments --no-tablespaces --skip-lock-tables --set-gtid-purged=OFF \
    --default-character-set=utf8mb4 seed > "$tmp"
php bin/check-seed-clean.php "$tmp"
chmod 644 "$tmp"
mv "$tmp" "$OUT"
trap - EXIT
echo "Wrote $OUT ($(wc -c < "$OUT" | tr -d ' ') bytes)."
