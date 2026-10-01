#!/usr/bin/env bash
# What the seed database needs on top of the committed dump, run by CI and by
# bin/reset-seed-db.sh so both prepare it the same way.
#
# The dump holds config that does not follow the repo. Anything that follows the code under
# test belongs here, so it can never go stale: database upgrades, activating the site's own
# plugins (which creates their tables from the current schema), fixtures loaded from files.
set -euo pipefail
cd "$(dirname "$0")/.."
# shellcheck disable=SC1091
[ -f bin/local-env.sh ] && source bin/local-env.sh

# wp-config.php selects the seed database when this is set.
export HTTP_X_TEST_REQUEST=1

# CiviCRM has no tables in the seed (bin/seed.conf deactivates it); on a machine with a
# civicrm.settings.php it fatals on boot, so it is never loaded here.
wp() { command wp --skip-plugins=civicrm "$@"; }

# A no-op while the dump matches the core version. After a WordPress bump, without this
# WordPress bounces every wp-admin request (and logins) to /wp-admin/upgrade.php, which is
# how an update PR exercises the upgrade path.
wp core update-db

# --- Mycodelic Forest --------------------------------------------------------------------

# The dump's Profile form (id 6) is an old revision: it ends with a text confirmation and
# has no pre-population params, while production redirects back to /profile/ and pre-fills
# from the user. Load the production definition (exported with GFAPI::get_form) and use the
# non-AJAX page block.
wp eval '$f = json_decode(file_get_contents("tests/_support/Data/forms/form-6-profile.json"), true); $r = GFAPI::update_form($f); if (is_wp_error($r)) { fwrite(STDERR, $r->get_error_message()); exit(1); }'
wp post update 275 --post_content='<!-- wp:gravityforms/form {"formId":"6","inputPrimaryColor":"#204ce5"} /-->'

# Cloudflare Turnstile always-pass test keys, so the registration form can be submitted by a
# headless browser (production keys only work on the real domain).
wp option update gravityformsaddon_ss88-gravity-forms-turnstile_settings \
    '{"ct_site_key":"1x00000000000000000000AA","ct_secret_key":"1x0000000000000000000000000000000AA"}' --format=json

# camp-manager must be active (its admin pages are under test and activation creates the
# mf_* tables from the current schema, so it is deliberately not baked into the dump).
wp plugin is-active camp-manager || wp plugin activate camp-manager

wp option get siteurl; wp option get home
wp plugin list --status=active --field=name | tr '\n' ' '; echo
