-- Row-level scrubbing of the seed database. Run by bin/build-seed.sh (which fills in the
-- table prefix and database name), never by hand elsewhere.
--
-- The seed is committed to git, so it must hold no personal data, no
-- credentials and nothing environment-specific. Acceptance tests never depend on existing
-- users: each Cest creates its own (testadmin, testuser, ...) in _before, so the only user
-- the seed keeps is the synthetic admin that build-seed.sh creates afterwards.
--
-- Tables that hold only people or logs (users, entries, debug events, ...) are emptied by
-- build-seed.sh; this file handles the tables that also hold content worth keeping.

-- Refuse to run against anything but the disposable seed database.
SET @q = IF(DATABASE() = '__SEED_DB__', 'SELECT 1', 'SELECT * FROM refusing_to_scrub_a_database_that_is_not_seed');
PREPARE guard FROM @q; EXECUTE guard; DEALLOCATE PREPARE guard;

-- Options: caches that embed IPs, emails and feed content; one-time keys; credentials.
DELETE FROM `__PREFIX__options` WHERE option_name LIKE '\_transient\_%' OR option_name LIKE '\_site\_transient\_%';
DELETE FROM `__PREFIX__options` WHERE option_name IN (
    'recovery_keys', 'recovery_mode_email_last_sent', 'adminhash',
    'gf_telemetry_data', 'gf_last_telemetry_run', 'gf_upgrade_lock', 'gform_version_info',
    'rsssl_404_cache',
    'wp_google_login_settings',          -- Google OAuth client id/secret
    'redirection_options',               -- holds the plugin's API token
    'wp_mail_smtp_mail_key',             -- key that encrypts the stored SMTP password
    'revslider-uid', 'rs-tracking-data', 'revslider-connection',
    'wfls_last_role_change', 'user_count',
    'mailserver_login', 'mailserver_pass',
    'wpseo_ms', 'jetpack_private_options', 'jetpack_secrets',
    'woocommerce_stripe_settings', 'woocommerce_paypal_settings', 'woocommerce_ppcp-settings',
    'mc4wp', 'mailchimp_api_key', 'wordpress_api_key',
    'updraftplus_options', 'ai1wm_secret_key', 'duplicator_settings', 'wpvivid_common_setting'
);
-- Keys, tokens and passwords by name, whatever plugin wrote them. Blunt on purpose: a plugin
-- that loses its licence key in the seed still runs, and a leaked key cannot be taken back.
DELETE FROM `__PREFIX__options`
 WHERE option_name REGEXP '(api[_-]?key|apikey|secret|license[_-]?key|licence[_-]?key|access[_-]?token|refresh[_-]?token|oauth|smtp[_-]?pass|password)'
   AND option_name NOT IN ('default_password_nag');
-- And by shape, whatever the option is called: the same shapes bin/check-seed-clean.php
-- refuses. An option that embeds a key goes as a whole; the plugin falls back to its defaults.
DELETE FROM `__PREFIX__options`
 WHERE REGEXP_LIKE(option_value, 'AIza[0-9A-Za-z_-]{30,}|gh[pousr]_[A-Za-z0-9]{30,}|xox[abprs]-[A-Za-z0-9-]{10,}|AKIA[0-9A-Z]{16}|[sr]k_live_[A-Za-z0-9]{10,}|-----BEGIN [A-Z ]*PRIVATE KEY-----|discord(app)?\\.com/api/webhooks/', 'c');

-- Contact details the setup wizards recorded, and the site admin address.
UPDATE `__PREFIX__options` SET option_value = 'seedadmin@seed.test'
 WHERE option_name IN ('admin_email', 'new_admin_email', 'rg_gforms_email');
UPDATE `__PREFIX__options` SET option_value = 'Seed' WHERE option_name IN ('rg_gforms_organization');

-- Google's documented reCAPTCHA v2 test keys (always pass), instead of the real pair.
UPDATE `__PREFIX__options` SET option_value = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI'
 WHERE option_name = 'rg_gforms_captcha_public_key';
UPDATE `__PREFIX__options` SET option_value = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe'
 WHERE option_name = 'rg_gforms_captcha_private_key';

-- Content: keep only published pages/posts. Revisions, drafts, private and trashed posts can
-- carry earlier or unpublished text. Everything left is authored by the one seed admin (ID 1).
DELETE FROM `__PREFIX__posts`
 WHERE post_type = 'revision'
    OR post_status IN ('draft', 'auto-draft', 'trash', 'private', 'pending', 'future');
DELETE pm FROM `__PREFIX__postmeta` pm LEFT JOIN `__PREFIX__posts` p ON p.ID = pm.post_id WHERE p.ID IS NULL;
UPDATE `__PREFIX__posts` SET post_author = 1;

-- (E-mail addresses, in every table, are replaced by build-seed.sh with wp search-replace,
-- which keeps serialized values valid.)
