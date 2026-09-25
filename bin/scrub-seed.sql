-- Row-level scrubbing of the `seed` database. Run by bin/build-seed.sh, never by hand elsewhere.
--
-- The seed is committed to a public repository, so it must hold no personal data, no
-- credentials and nothing environment-specific. Acceptance tests never depend on existing
-- users: each Cest creates its own (testadmin, testuser, ...) in _before, so the only user
-- the seed keeps is the synthetic admin that build-seed.sh creates afterwards.
--
-- Tables that hold only people or logs (users, entries, debug events, ...) are emptied by
-- build-seed.sh; this file handles the tables that also hold content worth keeping.

-- Refuse to run against anything but the disposable seed database.
SET @q = IF(DATABASE() = 'seed', 'SELECT 1', 'SELECT * FROM refusing_to_scrub_a_database_that_is_not_seed');
PREPARE guard FROM @q; EXECUTE guard; DEALLOCATE PREPARE guard;

-- Options: caches that embed IPs, emails and feed content; one-time keys; credentials.
DELETE FROM wp_options WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%';
DELETE FROM wp_options WHERE option_name IN (
    'recovery_keys', 'recovery_mode_email_last_sent', 'adminhash',
    'gf_telemetry_data', 'gf_last_telemetry_run', 'gf_upgrade_lock', 'gform_version_info',
    'rsssl_404_cache',
    'wp_google_login_settings',          -- Google OAuth client id/secret
    'redirection_options',               -- holds the plugin's API token
    'wp_mail_smtp_mail_key',             -- key that encrypts the stored SMTP password
    'revslider-uid', 'rs-tracking-data', 'revslider-connection',
    'wfls_last_role_change', 'user_count'
);

-- Contact details the setup wizards recorded, and the site admin address.
UPDATE wp_options SET option_value = 'seedadmin@seed.test'
 WHERE option_name IN ('admin_email', 'new_admin_email', 'rg_gforms_email');
UPDATE wp_options SET option_value = 'Seed' WHERE option_name IN ('rg_gforms_organization');

-- Google's documented reCAPTCHA v2 test keys (always pass), instead of the real pair.
UPDATE wp_options SET option_value = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'
 WHERE option_name = 'rg_gforms_captcha_public_key';
UPDATE wp_options SET option_value = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'
 WHERE option_name = 'rg_gforms_captcha_private_key';

-- Content: keep only published pages/posts. Revisions, drafts, private and trashed posts can
-- carry earlier or unpublished text. Everything left is authored by the one seed admin (ID 1).
DELETE FROM wp_posts
 WHERE post_type = 'revision'
    OR post_status IN ('draft', 'auto-draft', 'trash', 'private', 'pending', 'future');
DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts p ON p.ID = pm.post_id WHERE p.ID IS NULL;
UPDATE wp_posts SET post_author = 1;

-- (Addresses written into published content are replaced by build-seed.sh, in PHP: MySQL's
-- REGEXP_REPLACE has a server-wide time limit that large posts exceed.)
