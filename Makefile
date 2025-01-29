include tests/.env
export WORDPRESS_DOMAIN
# Makefile for WordPress Environment projects configured for local development with WP Local.
.PHONY: setup_db setup clean-database

# Initializes and configures the WordPress database with essential settings and plugins.
setup_db:
	@echo "Importing database..."
	wp db import ./wp-content/mysql.sql
	@echo "Cleaning database..."
	$(MAKE) clean-database
	@echo "Exporting database..."
	wp db export ./tests/_support/Data/db/dump.sql

# Cleans the environment and initializes development settings.
setup:
	php ./bin/setup.php

# Cleans sensitive data from the database before exporting for commit to ensure security.
clean-database:
	php ./bin/clean-database.php