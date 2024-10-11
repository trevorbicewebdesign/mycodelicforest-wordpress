include tests/.env
export WORDPRESS_DOMAIN
# Makefile for WordPress Environment projects configured for local development with WP Local.
.PHONY: shell setup_db start clean-database commit-seed-database update_composer

# Opens a shell for the project.
shell:
	./bin/manage/manage.py shell

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
	@echo "WARNING: This operation will delete all untracked files and directories except 'wp-content/uploads/'."
	@echo "Any uncommitted changes in Git will be lost."
	@while true; do \
		read -r -p "Are you sure you want to proceed? (yes/no): " confirm; \
		case "$$confirm" in \
			yes | YES ) break;; \
			no | NO ) echo "Operation canceled."; exit 1;; \
			* ) echo "Please type 'yes' or 'no'.";; \
		esac \
	done
	
	git clean -fdx
	composer install
	git checkout composer.json
	@echo "Checking for uploads.zip file in the `mycodelicforest/app/` folder..."
	unzip_flag="no"; \
	if [ -f "../uploads.zip" ]; then \
		echo "Unzipping uploads.zip to wp-content/uploads..."; \
		unzip ../uploads.zip; \
	else \
		while true; do \
			read -r -p "uploads.zip not found in the `mycodelicforest/app/` folder. Do you want to continue anyway? (yes/no): " cont; \
			case "$$cont" in \
				yes | YES ) break;; \
				no | NO ) echo "Operation canceled."; exit 1;; \
				* ) echo "Please type 'yes' or 'no'.";; \
			esac \
		done; \
	fi

	@echo "Do you want to keep the existing database or recreate it?"; \
	db_action="keep"; \
	while true; do \
		read -r -p "Keep existing ('keep') or Recreate ('recreate')? " db_confirm; \
		case "$$db_confirm" in \
			keep | KEEP ) echo "Keeping existing database."; break;; \
			recreate | RECREATE ) echo "Recreating the database..."; db_action="recreate"; break;; \
			* ) echo "Please type 'keep' or 'recreate'.";; \
		esac \
	done; \
	if [ "$$db_action" = "recreate" ]; then \
		echo "Setting up database..."; \
		$(MAKE) setup_db; \
		echo "Database setup complete."; \
	fi
	@./vendor/bin/codecept chromedriver:update
	@netstat -an | grep 9515 || ./vendor/bin/codecept dev:restart


# Cleans sensitive data from the database before exporting for commit to ensure security.
clean-database:
	wp option update home "https://$$WORDPRESS_DOMAIN"
	wp option update siteurl "https://$$WORDPRESS_DOMAIN"

	mysql -u root -proot -h localhost local -e " \
          TRUNCATE TABLE wp_users; \
          TRUNCATE TABLE wp_usermeta; \
        "
	
	wp user create admin admin@example.com --role=administrator --user_pass=password123!test
	wp core update-db
	wp plugin deactivate --all
	wp plugin activate --all

# Commits a cleaned and sanitized version of the seed database to the repository.
commit-seed-database:
	$(MAKE) clean-database
	wp db export wp-content/mysql.sql --allow-root
	git add wp-content/mysql.sql
	git commit -m "Updated seed database"
	git push

# Updates all Composer-managed dependencies and commits changes to the repository.
update_composer:
	cp -r ./wp-content/uploads ../uploads; \
	git clean -fdx
	composer update
	git checkout composer.json
	cp -r ../uploads/* ./wp-content/uploads/; \
	git add composer.json composer.lock
	git commit -m "Updated composer dependencies"
	git push

