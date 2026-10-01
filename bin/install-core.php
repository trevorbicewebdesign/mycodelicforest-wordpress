#!/usr/bin/env php
<?php
/**
 * Puts WordPress core into the web root (this folder) from the Composer package that holds it,
 * so that `composer install` brings the whole site. Composer runs it after every install and
 * update (see "scripts" in composer.json); it can also be run by hand.
 *
 * Core is the package roots/wordpress-no-content: Composer downloads it from wordpress.org,
 * caches it and records its version in composer.lock like any other package, and the nightly
 * update bumps it. Composer keeps it under vendor/, because it cannot manage the folder that
 * holds composer.json itself (a root install is wiped on the first update). This copies it
 * into place: wp-admin and wp-includes are mirrored (files a release dropped are removed),
 * the root files are copied over, and wp-content is never touched, nor is wp-config.php.
 * A no-op when the installed version already matches; refuses to downgrade.
 */

declare(strict_types=1);

$root   = dirname(__DIR__) . '/';
$source = $root . 'vendor/roots/wordpress-no-content/';

$version = static function (string $dir): ?string {
    $file = $dir . 'wp-includes/version.php';
    if (is_file($file) && preg_match("/^\\\$wp_version = '([^']+)';/m", (string) file_get_contents($file), $m)) {
        return $m[1];
    }
    return null;
};

$want = $version($source);
if ($want === null) {
    fwrite(STDERR, "install-core: roots/wordpress-no-content is not installed under vendor/ (run composer install); leaving WordPress core alone.\n");
    exit(1);
}
$have = $version($root);
if ($have === $want) {
    echo "WordPress {$want} already in place.\n";
    exit(0);
}
if ($have !== null && version_compare($have, $want, '>') && getenv('WP_ALLOW_DOWNGRADE') !== '1') {
    fwrite(STDERR, "install-core: WordPress {$have} is installed, newer than {$want} in composer.lock; not downgrading. Update the lock file, or set WP_ALLOW_DOWNGRADE=1.\n");
    exit(1);
}
echo 'WordPress ' . ($have ?? 'none') . " -> {$want}\n";

$copyFile = static function (string $from, string $to): void {
    is_dir(dirname($to)) || mkdir(dirname($to), 0755, true);
    if (!copy($from, $to)) {
        fwrite(STDERR, "install-core: could not write {$to}\n");
        exit(1);
    }
};
/** Makes $to hold exactly what $from holds. */
$mirror = static function (string $from, string $to) use ($copyFile): int {
    $keep = [];
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($from, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($it as $item) {
        $rel = substr($item->getPathname(), strlen($from));
        $keep[$rel] = true;
        $item->isDir() ? (is_dir($to . $rel) || mkdir($to . $rel, 0755, true)) : $copyFile($item->getPathname(), $to . $rel);
    }
    $removed = 0;
    if (is_dir($to)) {
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($to, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($it as $item) {
            $rel = substr($item->getPathname(), strlen($to));
            if (!isset($keep[$rel])) {
                $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
                $removed++;
            }
        }
    }
    return $removed;
};

$removed = 0;
foreach (['wp-admin/', 'wp-includes/'] as $dir) {
    $removed += $mirror($source . $dir, $root . $dir);
}
$copied = 0;
foreach (new DirectoryIterator($source) as $item) {
    if ($item->isFile()) {
        $copyFile($item->getPathname(), $root . $item->getFilename());
        $copied++;
    }
}
echo "WordPress {$want} in place (wp-admin and wp-includes mirrored, {$removed} old file(s) removed, {$copied} root file(s) copied). Run the database upgrade next: wp core update-db (bin/update-local.sh does).\n";
