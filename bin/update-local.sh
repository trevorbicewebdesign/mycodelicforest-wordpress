#!/usr/bin/env bash
# Bring this machine's site to the versions the repo pins, after pulling a merged update PR.
# Developer only: it changes the site's files and upgrades the site's own database.
#
#   bin/update-local.sh
set -euo pipefail
cd "$(dirname "$0")/.."
# shellcheck disable=SC1091
[ -f bin/local-env.sh ] && source bin/local-env.sh

# Plugins, theme and WordPress core at the locked versions (Composer runs bin/install-core.php
# afterwards, which copies core from vendor/ into the web root).
composer install --no-interaction
wp core update-db
wp plugin list --fields=name,status,version
