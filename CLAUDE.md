# Mycodelic Forest

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

- Rebuild `seed` from `tests/_support/Data/db/dump.sql` with `bin/reset-seed-db.sh`. It mirrors
  the CI fixture step and refuses to run unless wp-cli resolves to `seed`.
- **The seed dump is public and must stay clean.** It is committed to a public repository, so it
  holds no personal data (users, emails, entries, logs, sessions), no credentials and nothing
  environment-specific. Acceptance tests never rely on existing users: every Cest creates its own
  in `_before`, so the dump keeps a single synthetic admin (`seedadmin`) and nothing else. To
  change the dump, edit `bin/scrub-seed.sql` / `bin/build-seed.sh` and run `bin/build-seed.sh`
  (it loads a source dump into `seed`, scrubs it, and exports); **never** commit a raw export
  (`wp db export`, Akeeba, `make setup_db`). `bin/check-seed-clean.php` verifies the result and
  runs first in CI, failing the build if a dump holds personal data or secrets.
- `test` is recreated by the integration suite itself.
- `make setup_db` imports into and exports from `local`, so it is a developer-only command.
- `wp-config.php` is tracked but flagged `skip-worktree`, so local edits to it never show in
  `git status`. Keep the three-way database switch intact when editing it.

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
- The helpers are tested by `tests/db/DbHelperCest.php` (`codecept run db`: MySQL only, no browser).

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
