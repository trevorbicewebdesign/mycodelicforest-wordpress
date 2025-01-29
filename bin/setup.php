#!/usr/bin/env php
<?php

require_once 'consoleColors.php';

class SetupScript
{
    public function run(): void
    {
        $this->printWarning();
        $this->confirmOperation();

        $this->gitClean();
        $this->composerInstall();
        $this->gitCheckoutComposerJson();

        $this->checkUploadsZip();
        $this->promptDatabaseAction();

        $this->updateChromedriver();
        $this->restartCodeceptIfNeeded();
    }

    /**
     * Display initial warnings about deleting untracked files.
     */
    private function printWarning(): void
    {
        echo "WARNING: This operation will delete all untracked files and directories except 'wp-content/uploads/'.\n";
        echo "Any uncommitted changes in Git will be lost.\n";
    }

    /**
     * Ask the user if they really want to proceed. Exit on "no".
     */
    private function confirmOperation(): void
    {
        while (true) {
            $confirm = $this->prompt("Are you sure you want to proceed? (yes/no): ");
            if (strcasecmp($confirm, 'yes') === 0) {
                break;
            } elseif (strcasecmp($confirm, 'no') === 0) {
                echo "Operation canceled.\n";
                exit(1);
            } else {
                echo "Please type 'yes' or 'no'.\n";
            }
        }
    }

    /**
     * Run 'git clean -fdx' to remove untracked files.
     */
    private function gitClean(): void
    {
        // Define the paths to be cleaned
        $pathsToClean = [
            'wp-admin',
            'wp-includes',
            'wp-content/plugins',
            'wp-content/themes',
            'vendor',
        ];

        // Loop through each path and remove untracked files
        foreach ($pathsToClean as $path) {
            $this->runCommand("git clean -fdx $path");
        }

        // Remove untracked files in the root WordPress folder that start with 'wp-' except wp-config.php
        $rootFiles = glob('wp-*');
        foreach ($rootFiles as $file) {
            if ($file !== 'wp-config.php' && is_file($file)) {
                $this->runCommand("git clean -fdx $file");
            }
        }

        // temporarily rename wp-config.php to prevent deletion
        if (file_exists('wp-config.php')) {
            rename('wp-config.php', 'wp-config.php.bak');
        }
    }
    /**
     * Run 'composer install' to install dependencies.
     */
    private function composerInstall(): void
    {
        $this->runCommand("composer install");

        // temporarily rename wp-config.php to prevent deletion
        if (file_exists('wp-config.php.bak')) {
            rename('wp-config.php.bak', 'wp-config.php');
        }

        $this->runCommand("git reset composer.json");
    }

    /**
     * Restore composer.json if changed.
     */
    private function gitCheckoutComposerJson(): void
    {
        $this->runCommand("git checkout composer.json");
    }

    /**
     * Check for ../uploads.zip; if missing, prompt user to continue or abort.
     */
    private function checkUploadsZip(): void
    {
        echo "Checking for uploads.zip file in the `wordpress-mycodelicforest/app/` folder...\n";
        if (file_exists("../uploads.zip")) {
            echo "Unzipping uploads.zip to wp-content/uploads...\n";
            // Requires 'unzip' to be available on PATH
            $this->runCommand("unzip ../uploads.zip -d wp-content");
        } else {
            // Prompt user to continue anyway or cancel
            while (true) {
                $cont = $this->prompt("uploads.zip not found. Continue anyway? (yes/no): ");
                if (strcasecmp($cont, 'yes') === 0) {
                    break;
                } elseif (strcasecmp($cont, 'no') === 0) {
                    echo "Operation canceled.\n";
                    exit(1);
                } else {
                    echo "Please type 'yes' or 'no'.\n";
                }
            }
        }
    }

    /**
     * Ask if we keep the existing DB or recreate it. If recreate, call "make setup_db".
     */
    private function promptDatabaseAction(): void
    {
        echo "Do you want to keep the existing database or recreate it?\n";

        $dbAction = 'keep';
        while (true) {
            $choice = $this->prompt("Keep existing ('keep') or Recreate ('recreate')? ");
            if (strcasecmp($choice, 'keep') === 0) {
                echo "Keeping existing database.\n";
                break;
            } elseif (strcasecmp($choice, 'recreate') === 0) {
                echo "Recreating the database...\n";
                $dbAction = 'recreate';
                break;
            } else {
                echo "Please type 'keep' or 'recreate'.\n";
            }
        }

        if ($dbAction === 'recreate') {
            // Here we're calling Make to run "setup_db", but you could directly call your
            // own "clean-database.php" script or any other logic that sets up the DB.
            $this->runCommand("make setup_db");
            echo "Database setup complete.\n";
        }
    }

    /**
     * Update chromedriver via Codeception.
     */
    private function updateChromedriver(): void
    {
        $this->runCommand("./vendor/bin/codecept chromedriver:update");
    }

    /**
     * Check if port 4444 is in use (via netstat/grep); if not, run "codecept dev:restart".
     */
    private function restartCodeceptIfNeeded(): void
    {
        // Example: netstat -an | grep 4444 || ./vendor/bin/codecept dev:restart
        // We'll do a quick check in PHP:
        $output = [];
        $returnVar = 0;
        exec("netstat -an", $output, $returnVar);

        // Look for '4444' in the output
        $portOpen = false;
        foreach ($output as $line) {
            if (strpos($line, '4444') !== false) {
                $portOpen = true;
                break;
            }
        }

        if (!$portOpen) {
            $this->runCommand("./vendor/bin/codecept dev:restart");
        }
    }

    /**
     * Generic helper to run a shell command and exit if it fails.
     */
    private function runCommand(string $cmd): void
    {
        echo "Running: $cmd\n";
        $returnCode = 0;
        system($cmd, $returnCode);

        if ($returnCode !== 0) {
            echo "Command failed with code $returnCode: $cmd\n";
            exit($returnCode);
        }
    }

    /**
     * Prompt user on the command line, returning the trimmed input.
     */
    private function prompt(string $message): string
    {
        echo $message;
        return trim(fgets(STDIN));
    }
}

// ---------- Execute the Script ----------
$script = new SetupScript();
$script->run();
exit(0);
