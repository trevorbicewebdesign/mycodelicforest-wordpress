SET SESSION group_concat_max_len = 100000;

-- Drop tables with "civicrm" in the name
SET @tables = NULL;

SELECT GROUP_CONCAT(table_name SEPARATOR ',') INTO @tables
FROM information_schema.tables 
WHERE table_schema = DATABASE() 
AND table_name LIKE '%civicrm%';

-- Only execute DROP TABLE if @tables is NOT NULL
SET @query = IF(@tables IS NOT NULL, CONCAT('DROP TABLE ', @tables), 'SELECT "No tables to drop"');
PREPARE stmt FROM @query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Drop triggers with "civicrm" in the name
SET @triggers = NULL;

SELECT GROUP_CONCAT(trigger_name SEPARATOR ', ') INTO @triggers
FROM information_schema.triggers 
WHERE trigger_schema = DATABASE() 
AND trigger_name LIKE '%civicrm%';

-- Only execute DROP TRIGGER if @triggers is NOT NULL
SET @drop_triggers = NULL;

SELECT GROUP_CONCAT(CONCAT('DROP TRIGGER ', trigger_schema, '.', trigger_name) SEPARATOR '; ') INTO @drop_triggers
FROM information_schema.triggers 
WHERE trigger_schema = DATABASE() 
AND trigger_name LIKE '%civicrm%';

SET @final_triggers = IF(@drop_triggers IS NOT NULL, @drop_triggers, 'SELECT "No triggers to drop"');
PREPARE stmt FROM @final_triggers;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
