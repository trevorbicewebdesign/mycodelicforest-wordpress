#!/usr/bin/env php
<?php

require_once 'consoleColors.php';

/**
 * This script replicates the “clean-database” target from the Makefile.
 * Usage: php clean-database.php [WORDPRESS_DOMAIN]
 */

class CleanDatabase
{
    /**
     * @var int $argc Number of command-line arguments
     */
    private $argc;

    /**
     * @var array $argv Array of command-line arguments
     */
    private $argv;

    /**
     * @var string $wordpressDomain The domain to be used for WP updates
     */
    private $wordpressDomain;

    /**
     * Constructor. Validates arguments and sets properties.
     *
     * @param int   $argc
     * @param array $argv
     */
    public function __construct(int $argc, array $argv)
    {
        $this->argc = $argc;
        $this->argv = $argv;

        // 1. Parse arguments (e.g., from command line).
        if ($this->argc < 2) {
            fwrite(STDERR, "Usage: php clean-database.php <WORDPRESS_DOMAIN>\n");
            exit(1);
        }

        $this->wordpressDomain = $this->argv[1];
    }

    /**
     * Runs the entire cleanup and plugin reactivation sequence.
     */
    public function run(): void
    {
        // 2. Construct commands for WP-CLI and MySQL operations.
        $commands = [
            // Update WordPress home/siteurl
            "wp option update home \"https://{$this->wordpressDomain}\"",
            "wp option update siteurl \"https://{$this->wordpressDomain}\"",

            // Run search-replace
            "wp search-replace '//wordpress.mycodelicforest.org' \"//{$this->wordpressDomain}\" --all-tables",

            // Import dev-drop-tables.sql
            "wp db import bin/dev-drop-tables.sql",
            // Optionally import dev-truncate-tables if you separated that logic
            "wp db import bin/dev-truncate-tables.sql",
        ];

        $this->runCommands($commands);

        $finalCommands = [
            "wp user create admin admin@example.com --role=administrator --user_pass=password123!test",
            "wp core update-db",
            "wp plugin deactivate --all",
        ];
        $this->runCommands($finalCommands);

        $this->activatePlugins([
            "civicrm",
            "email-address-obfuscation",
            "google-site-kit",
            "gravityforms",
            "really-simple-ssl",
            "redirection",
            "wp-mail-smtp",
            "ultimate-addons-for-gutenberg",
            "ultimate-faqs",
            "mycodelic-forest",
        ]);

        echo "Database cleanup and plugin reactivation complete!\n";

        $this->runCommands([
            "wp cache flush",
            "wp rewrite flush",
            "wp db export wp-content/mysql.sql --allow-root",
        ]);
    }

    private function activatePlugins(array $plugins): void
    {
        $plugins = implode(' ', $plugins);
        $cmd = "wp plugin activate {$plugins}";
        echo ConsoleColors::colorize("$cmd\n", ConsoleColors::BLUE);
        $returnCode = 0;
        system($cmd, $returnCode);
        if ($returnCode !== 0) {
            fwrite(STDERR, "Command failed with code $returnCode: $cmd\n");
            exit($returnCode);
        }
    }

    /**
     * Helper method to run an array of shell commands in sequence.
     *
     * @param string[] $commands
     * @return void
     */
    private function runCommands(array $commands): void
    {
        foreach ($commands as $cmd) {
            echo ConsoleColors::colorize("$cmd\n", ConsoleColors::BLUE);
            $returnCode = 0;
            system($cmd, $returnCode);
            if ($returnCode !== 0) {
                fwrite(STDERR, "Command failed with code $returnCode: $cmd\n");
                exit($returnCode);
            }
        }
    }
}

// Instantiate and run
$script = new CleanDatabase($argc, $argv);
$script->run();
exit(0);
