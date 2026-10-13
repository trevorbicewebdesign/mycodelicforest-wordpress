# Mycodelic Forest

WordPress site, set up with the WordPress test kit (`joomla-test-kit/wordpress`). The repo root
is the web root. WordPress core and third-party plugins are **not** in git: Composer installs
all of them (`composer.json`); core is the package `roots/wordpress-no-content`, which
`bin/install-core.php` copies from `vendor/` into the web root after every `composer install`.
The site's own code is tracked: the plugins `camp-manager`, `house-ledger`, `mycodelic-forest`
and `civicrm`, and the theme `mycodelic-forest-child`.

## Databases (Local site)

There are three databases on Local's MySQL. `wp-config.php` picks one per request:

| Database | Who it is for | Selected when |
|---|---|---|
| `local` | **The developer only.** Their working site, logins and data. | Normal browser requests and plain `wp` CLI calls |
| `seed` | Acceptance/functional tests | `X-Test-Request: 1` header, `webdriver_test_request=1` cookie, `HTTP_X_TEST_REQUEST=1` env, `php -S`, or `APPLICATION_ENV=test` server var |
| `test` | Integration tests (WPLoader, `test_` prefix) | `WORDPRESS_DB_TEST_URL` or `APPLICATION_ENV=test` in the environment |

**Never write to `local`.** Only the developer changes it. That rules out activating or deactivating
plugins, updating options, flushing rewrites, creating posts or users, importing dumps, and,
above all, changing or resetting any user's password. Reading from it is fine. If a change
needs a real WordPress to try against, use `seed`: send the test header or cookie, or run
`HTTP_X_TEST_REQUEST=1 wp ...`. If something truly has to change in `local`, ask the developer
to do it.

- Rebuild `seed` from `tests/_support/Data/db/dump.sql` with `bin/reset-seed-db.sh`. It
  does what CI does and refuses to run unless wp-cli resolves to `seed`.
- **The seed dump is public and must stay clean.** It is committed to a public repository, so it
  holds no personal data (users, emails, entries, logs, sessions), no credentials and nothing
  environment-specific. Tests never rely on existing users: every Cest creates its own in
  `_before`, so the dump keeps a single synthetic admin (`seedadmin`) and nothing else. To change
  the dump, edit `bin/scrub-seed.sql` / `bin/build-seed.sh` and run `bin/build-seed.sh`
  (`bin/build-seed.sh site` starts from a fresh, read-only copy of the site's database); **never**
  commit a raw export (`wp db export`, Akeeba, `make setup_db`). `bin/check-seed-clean.php`
  verifies the result and runs first in CI, failing the build if a dump holds personal data or
  secrets.
- Anything the seed needs that follows the code goes in `bin/ci-fixtures.sh`, which CI and
  `bin/reset-seed-db.sh` both run: `wp core update-db`, the production Profile form (GF form 6)
  and its page block, the Turnstile always-pass test keys, and activating `camp-manager` (its
  `mf_*` tables are created from the current schema, so the dump never carries them).
- `test` is recreated by the integration suite itself.
- `make setup_db` imports into and exports from `local`, so it is a developer-only command.
- `wp-config.php` answers requests it served from the seed with an `X-WP-Test-Kit-Database: seed`
  response header. The acceptance suite checks it before every test and refuses to run without
  it; functional tests assert it with `$I->seeResponseCameFromTheSeedDatabase()`.
- `wp-config.php` is not tracked. Keep the three-way database switch intact when editing it; CI
  uses `tests/wp-config.ci.php`.

## Running tests

```bash
./tests/run                     # integration, the fast one
./tests/run functional
./tests/run acceptance          # needs the site running in Local, and Chrome
./tests/run acceptance --group camp-roster
./tests/run db
```

`tests/run` carries the PHP this site runs on (Local's 8.4 binary, from `bin/local-env.sh`).
Functional and acceptance tests go through the web server, so the site has to be started in Local.

- The integration suite loads only `mycodelic-forest` (with the parent theme `spectra-one`); the
  Camp Manager tests activate `camp-manager` themselves, and WPLoader turns tables created inside
  a test into TEMPORARY tables, which MySQL cannot reference twice in one query.
- Every acceptance Cest carries a `#[Group]` (`camp-budgets`, `camp-receipts`, `camp-roster`,
  `camp-ledger`, `profile`, `site`); CI runs one runner per group, so a new Cest needs one.

## Test data: reset to the seed, then create what the test needs

The seed holds no people and no camp data (one synthetic `seedadmin`, empty Camp Manager tables).
Acceptance/functional tests never rely on anything else existing, and never on a fixed ID. Use
`tests/_support/Helper/DbHelper.php` (enabled in the acceptance, functional and `db` suites):

```php
$I->resetSeedState();                              // only seedadmin, no mf_* rows, no form entries
$this->adminId = $I->createTestAdmin()['id'];      // testadmin, complete profile, password123!test
$categories    = $I->createDefaultBudgetCategories();
$item          = $I->createBudgetItem(['category_id' => $categories['Power']['id']]);
```

- Same shape as the Mothership repo's `DbHelper`: `createXData($data)` merges defaults and returns
  the row; `createX($data)` inserts it and returns it with its `id`. Defaults cover only what a row
  cannot exist without, so a test states the values it asserts on.
- Add a helper here when a second test needs the same fixture; do not copy insert blocks between Cests.
- The helpers are tested by `tests/db/DbHelperCest.php` (`./tests/run db`: MySQL only, no browser).

## Updates

`.github/workflows/update-wordpress-and-plugins.yml` runs nightly: it updates `composer.lock`
(WordPress core included), opens a PR and runs the Tests workflow on it. Plugins are updated through
that PR, not from wp-admin. After merging, `bin/update-local.sh` brings this machine to the
same versions (developer only: it upgrades the `local` database).

- `composer install` also puts WordPress core in place (`bin/install-core.php`, run by Composer
  afterwards). On this machine that changes the site's files, and the site's database then needs
  `wp core update-db`: that is what `bin/update-local.sh` does.
- A plugin from wordpress.org: `composer require wpackagist-plugin/<slug>`.
- **No third-party code is in git.** Plugins Composer cannot get from wordpress.org come from
  mirrors: `trevorbicewebdesign/akeebabackupwp` (public, tagged per release) and the private
  `gravityforms`, `revslider`, `advanced-custom-fields-pro` and `spectra-pro`. A new Akeeba
  release is published to its mirror with `joomla-test-kit/wordpress/bin/release-plugin-mirror.sh`;
  a paid plugin is updated by unzipping the vendor release over its private repo clone and
  tagging `vX.Y.Z` (ACF Pro: keep our `composer.json`, not WP Engine's). The nightly PR then
  picks it up. CI needs the `COMPOSER_AUTH` secret for the private ones.
- **Tests run without Wordfence and CiviCRM** (`SEED_DEACTIVATE_PLUGINS` in `bin/seed.conf`):
  Wordfence is still installed and updated by Composer; CiviCRM is tracked but has no tables in
  the seed and fatals on a machine with a `civicrm.settings.php`, so the seed scripts skip it.
  `camp-manager` is on that list too, so a rebuilt dump never carries its tables (see above).
- The site's own plugin or theme: add it to the allowlist in `.gitignore`, to `plugins:` in
  `tests/integration.suite.yml` if the integration suite should load it, and activate it in
  `bin/ci-fixtures.sh`.

## Deploying

`bin/deploy.sh` runs on the NAS, from a checkout of this repo at `app/deploy-repo`. It fetches
the branch, runs `composer install --no-dev`, syncs into the docroot and brings WordPress core to
the locked version (`SITE_NAME` is `mycodelicforest.org`).

- The repo is synced as an overlay without `--delete`; `.deployignore` lists what never reaches
  the docroot (`wp-config.php`, uploads, the tooling). Composer-managed plugin and theme folders
  are synced one by one with `--delete`, so files an update removed do not linger; `.deploykeep`
  lists what a plugin writes into its own folder on the server (Akeeba's backups and settings),
  which a deploy never deletes.
- Nothing in it writes to a database except `wp core update-db` on the server.

## Theme: header and footer menus live in the child theme source

The active theme is `mycodelic-forest-child` (parent: Spectra One, a block theme). The header
and footer are the template parts `wp-content/themes/mycodelic-forest-child/parts/header.html`
and `parts/footer.html`, and that is where menu changes belong. Edit the block markup in those
files and deploy; do not add links through the Site Editor, the database navigation menus
(`wp_navigation` posts), or wp-cli scripts.

- A `<!-- wp:navigation {"ref":N} /-->` block pulls its links from the database, so links in it
  cannot be changed from source. To add or change a link in such a column, replace the `ref`
  block with an inline `<!-- wp:navigation ... -->` block that wraps `wp:navigation-link` blocks
  (the footer's About Us column is done this way; use it as the template).
- Use site-relative URLs (`/history/`) with `"kind":"custom"` so the markup works on Local, CI
  and production without page IDs or hostnames.
- Links to members-only pages (History, Roster) get `"className":"mf-members-only"` in the
  block attributes. `MycodelicForestHistory::hideMembersOnlyBlocks()` (a `render_block`
  filter in the mycodelic-forest plugin) drops any block with that class unless the viewer is
  a Mycodelic Forest Member or can edit content, the same test as the history pages. Spectra's
  own display conditions are not enough: "userstate" only tells logged in from logged out, and
  "userRole" hides a block *from* a role rather than showing it only to one.
- The database holds `wp_template_part` overrides for `footer` and `header`, but they belong to
  the `spectra-one` theme, so they do not shadow the child theme files. If a change to a part
  file does not show up, check whether an override for `mycodelic-forest-child` was saved from
  the Site Editor and delete it.
- To check the rendering without touching the `local` database, parse the part file and call
  `render_block()` through wp-cli with `HTTP_X_TEST_REQUEST=1` (the seed database), once as a
  logged-out user and once after `wp_set_current_user()` with an administrator.
