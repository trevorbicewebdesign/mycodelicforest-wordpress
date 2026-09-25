#!/usr/bin/env bash
# Rebuild the `seed` database (acceptance/functional tests) from the committed dump,
# mirroring the "Prepare seed database" step in .github/workflows/codeception-test.yml.
#
# Databases on this site:
#   local - the developer's site. Only the developer changes it; no script or test writes here.
#   seed  - acceptance/functional tests (requests with X-Test-Request: 1 / webdriver cookie).
#   test  - integration tests (WPLoader installs into it with the test_ prefix).
#
# Needs `wp` and `mysql` on PATH pointed at Local's MySQL (Local's "Open site shell" does this).
set -euo pipefail
cd "$(dirname "$0")/.."

# wp-config.php selects the seed database when this is set (PHP CLI mirrors env into $_SERVER).
export HTTP_X_TEST_REQUEST=1

# CiviCRM has no tables in seed; on a machine with a civicrm.settings.php it fatals on boot,
# so never load it here (the dump already has it deactivated).
wp() { command wp --skip-plugins=civicrm "$@"; }

mysql -u root -proot -e "DROP DATABASE IF EXISTS seed; CREATE DATABASE seed"
mysql -u root -proot seed < tests/_support/Data/db/dump.sql

# Last line only: PHP startup warnings from a mismatched local PHP go to stdout.
db=$(wp eval 'global $wpdb; echo $wpdb->dbname;' | tail -n 1)
if [ "$db" != "seed" ]; then
    echo "wp-cli is pointed at '$db', not 'seed'; refusing to continue." >&2
    exit 1
fi

# Mirrors the "Prepare test fixtures" step in .github/workflows/codeception-test.yml. The dump
# itself is built by bin/build-seed.sh and already holds the config that does not follow the repo.
wp core update-db
wp eval '$f = json_decode(file_get_contents("tests/_support/Data/forms/form-6-profile.json"), true); $r = GFAPI::update_form($f); if (is_wp_error($r)) { fwrite(STDERR, $r->get_error_message()); exit(1); }'
wp post update 275 --post_content='<!-- wp:gravityforms/form {"formId":"6","inputPrimaryColor":"#204ce5"} /-->'
wp plugin activate camp-manager
wp plugin list --status=active --field=name | tr '\n' ' '; echo
