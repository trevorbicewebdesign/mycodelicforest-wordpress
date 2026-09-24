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
- `test` is recreated by the integration suite itself.
- `make setup_db` imports into and exports from `local`, so it is a developer-only command.
- `wp-config.php` is tracked but flagged `skip-worktree`, so local edits to it never show in
  `git status`. Keep the three-way database switch intact when editing it.

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
- Members-only pages get Spectra's display condition on the link so logged-out visitors do not
  see a link that redirects to login: add
  `"UAGLoggedOut":true,"UAGDisplayConditions":"userstate"` to the link's attributes.
- The database holds `wp_template_part` overrides for `footer` and `header`, but they belong to
  the `spectra-one` theme, so they do not shadow the child theme files. If a change to a part
  file does not show up, check whether an override for `mycodelic-forest-child` was saved from
  the Site Editor and delete it.
- To check the rendering without touching the `local` database, parse the part file and call
  `render_block()` through wp-cli with `HTTP_X_TEST_REQUEST=1` (the seed database), once as a
  logged-out user and once after `wp_set_current_user()` with an administrator.
