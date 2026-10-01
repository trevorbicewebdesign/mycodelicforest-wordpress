#!/usr/bin/env bash
# Rebuild the seed database (acceptance/functional tests) from the committed dump, exactly as
# the "Prepare test fixtures" step in .github/workflows/codeception-test.yml does.
#
# Databases on this site (see bin/seed.conf):
#   SITE_DB - the developer's site. Only the developer changes it; no script or test writes here.
#   SEED_DB - acceptance/functional tests (requests with X-Test-Request: 1 / webdriver cookie).
#   TEST_DB - integration tests (WPLoader installs into it with the test_ prefix).
set -euo pipefail
# shellcheck disable=SC1091
source "$(dirname "$0")/_common.sh"

mysql_ -e "DROP DATABASE IF EXISTS \`$SEED_DB\`; CREATE DATABASE \`$SEED_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE DATABASE IF NOT EXISTS \`$TEST_DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
mysql_ "$SEED_DB" < tests/_support/Data/db/dump.sql
use_seed_database

bin/ci-fixtures.sh
