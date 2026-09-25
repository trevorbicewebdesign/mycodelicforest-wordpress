#!/usr/bin/env php
<?php
/**
 * Fails when a committed test database dump holds personal data or credentials.
 *
 * The dumps are committed to a public repository, so this is a tripwire, not a scrubber:
 * bin/build-seed.sh produces a clean dump and this proves it stayed that way. It never
 * prints matched values, only what kind of thing was found and where.
 *
 * Usage: php bin/check-seed-clean.php [dump.sql ...]   (default: tests/_support/Data/db/*.sql)
 */

$root  = dirname(__DIR__);
$files = array_slice($argv, 1) ?: glob($root . '/tests/_support/Data/db/*.sql');
if (!$files) {
    fwrite(STDERR, "No dump files to check.\n");
    exit(2);
}

// Tables that only ever hold people or logs; a clean seed has no rows in them.
$mustBeEmpty = [
    'users' => 1, // exactly one synthetic user is allowed (see below)
    'comments', 'commentmeta', 'gf_entry', 'gf_entry_meta', 'gf_entry_notes',
    'gf_draft_submissions', 'wpmailsmtp_debug_events', 'wpmailsmtp_tasks_meta',
    'redirection_404', 'redirection_logs', 'actionscheduler_actions', 'actionscheduler_logs',
    'actionscheduler_claims',
];
$forbiddenOptions = '(_transient_|_site_transient_|recovery_keys|adminhash|gf_telemetry_data|rsssl_404_cache|wp_google_login_settings|redirection_options|wp_mail_smtp_mail_key)';
// Addresses that are reserved for documentation/tests or shipped as WordPress defaults.
$allowedEmail = '/@(seed\.test|example\.(com|org|net))$/i';
// Secrets that are recognisable by shape. Long random-looking strings cannot be checked
// generically (RevSlider and Gravity Forms store legitimate ones), so these are prefixes.
$secretShapes = [
    'private key block'  => '/-----BEGIN [A-Z ]*PRIVATE KEY-----/',
    'GitHub token'       => '/\bgh[pousr]_[A-Za-z0-9]{30,}/',
    'Slack token'        => '/\bxox[abprs]-[A-Za-z0-9-]{10,}/',
    'AWS access key'     => '/\bAKIA[0-9A-Z]{16}\b/',
    'Google API key'     => '/\bAIza[0-9A-Za-z_-]{30,}/',
    'Stripe live key'    => '/\b[sr]k_live_[A-Za-z0-9]{10,}/',
    'Discord webhook'    => '#discord(?:app)?\.com/api/webhooks/\d+/[\w-]+#',
    'Bearer credential'  => '/Bearer\s+[A-Za-z0-9._~+\/-]{20,}/',
];

$problems = [];
foreach ($files as $file) {
    $name = basename($file);
    $sql  = (string) file_get_contents($file);
    $bad  = function (string $what) use (&$problems, $name) { $problems[] = "$name: $what"; };

    foreach ($mustBeEmpty as $key => $value) {
        [$table, $max] = is_int($key) ? [$value, 0] : [$key, $value];
        if (preg_match_all('/^INSERT INTO `[^`]*' . preg_quote($table, '/') . '` VALUES (.*);$/m', $sql, $m)) {
            $rows = 0;
            foreach ($m[1] as $stmt) {
                $rows += $table === 'users'
                    ? preg_match_all('/\((\d+),\'[^\']*\',\'\$(?:P|wp)\$/', $stmt)
                    : substr_count($stmt, '),(') + 1;
            }
            if ($rows > $max) {
                $bad("table {$table} has {$rows} row(s); allowed {$max}");
            }
        }
    }

    if (preg_match('/^INSERT INTO `[^`]*usermeta` VALUES .*session_tokens/m', $sql)) {
        $bad('usermeta holds session_tokens (live login sessions)');
    }
    if (preg_match('/^INSERT INTO `[^`]*usermeta` VALUES .*application_passwords/m', $sql)) {
        $bad('usermeta holds application passwords');
    }
    $hashes = preg_match_all('/\'\$(?:P\$B|wp\$2y\$|2y\$)[^\']{20,}\'/', $sql);
    if ($hashes > 1) {
        $bad("{$hashes} password hashes (at most the one synthetic admin is allowed)");
    }

    if (preg_match_all('/^INSERT INTO `[^`]*options` VALUES (.*);$/m', $sql, $m)) {
        foreach ($m[1] as $stmt) {
            if (preg_match_all('/\(\d+,\'' . $forbiddenOptions . '[^\']*\'/', $stmt, $hits)) {
                $bad(count($hits[0]) . ' forbidden option row(s) (caches, one-time keys or credentials)');
            }
        }
    }

    $bad_domains = [];
    if (preg_match_all('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/', $sql, $m)) {
        foreach (array_unique($m[0]) as $email) {
            if (!preg_match($allowedEmail, $email)) {
                $bad_domains[substr(strrchr($email, '@'), 1)] = ($bad_domains[substr(strrchr($email, '@'), 1)] ?? 0) + 1;
            }
        }
    }
    foreach ($bad_domains as $domain => $count) {
        $bad("{$count} email address(es) @{$domain} (only seed.test / example.* are allowed)");
    }

    foreach ($secretShapes as $label => $pattern) {
        if (preg_match($pattern, $sql)) {
            $bad("looks like a {$label}");
        }
    }
}

if ($problems) {
    fwrite(STDERR, "Seed dump is NOT clean:\n  - " . implode("\n  - ", $problems) . "\n");
    fwrite(STDERR, "Rebuild the dump with bin/build-seed.sh (see CLAUDE.md), or delete it if nothing uses it.\n");
    fwrite(STDERR, "Never commit a raw site export (wp db export, Akeeba, make setup_db).\n");
    exit(1);
}
echo "Seed dump(s) clean: " . implode(', ', array_map('basename', $files)) . "\n";
