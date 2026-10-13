# Sourced by the scripts in bin/. Loads the settings and points wp-cli at the seed database.
cd "$(dirname "${BASH_SOURCE[0]}")/.."
# shellcheck disable=SC1091
[ -f bin/local-env.sh ] && source bin/local-env.sh
# shellcheck disable=SC1091
source bin/seed.conf

# Over the socket when this machine has one (Local's MySQL often has only root@localhost, which
# refuses TCP), over TCP otherwise (CI).
if [ -n "${DB_SOCKET:-}" ] && [ -S "$DB_SOCKET" ]; then
    db_connection=(--protocol=SOCKET -S "$DB_SOCKET")
else
    db_connection=(--protocol=TCP -h"$DB_HOST" -P"$DB_PORT")
fi
mysql_()     { MYSQL_PWD="$DB_PASS" mysql -u"$DB_USER" "${db_connection[@]}" --default-character-set=utf8mb4 "$@"; }
mysqldump_() { MYSQL_PWD="$DB_PASS" mysqldump -u"$DB_USER" "${db_connection[@]}" --default-character-set=utf8mb4 "$@"; }

if ! mysql_ -e 'SELECT 1' >/dev/null 2>&1; then
    echo "Cannot reach MySQL (${db_connection[*]}). Is the site started in Local?" >&2
    mysql_ -e 'SELECT 1' 2>&1 | grep -v 'Using a password' >&2 || true
    exit 1
fi

# wp-config.php selects the seed database when this is set (PHP CLI mirrors env into $_SERVER).
use_seed_database() {
    export HTTP_X_TEST_REQUEST=1
    local db
    # Last line only: PHP startup warnings from a mismatched local PHP go to stdout.
    db=$(wp eval 'global $wpdb; echo $wpdb->dbname;' | tail -n 1)
    if [ "$db" != "$SEED_DB" ]; then
        echo "wp-cli is pointed at '$db', not '$SEED_DB'; refusing to continue." >&2
        exit 1
    fi
}
