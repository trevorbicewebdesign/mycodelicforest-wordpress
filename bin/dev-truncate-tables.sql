SET
    FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE civicrm_activity;

TRUNCATE TABLE civicrm_activity_contact;

TRUNCATE TABLE civicrm_cache;

TRUNCATE TABLE civicrm_relationship_cache;

TRUNCATE TABLE civicrm_contribution;

TRUNCATE TABLE civicrm_address;

TRUNCATE TABLE civicrm_contact;

TRUNCATE TABLE civicrm_email;

TRUNCATE TABLE civicrm_group_contact;

TRUNCATE TABLE civicrm_log;

TRUNCATE TABLE civicrm_mailing_recipients;

TRUNCATE TABLE civicrm_phone;

TRUNCATE TABLE wp_users;

TRUNCATE TABLE wp_usermeta;

TRUNCATE TABLE log_civicrm_acl;

TRUNCATE TABLE log_civicrm_acl_entity_role;

TRUNCATE TABLE log_civicrm_action_mapping;

TRUNCATE TABLE log_civicrm_action_schedule;

TRUNCATE TABLE log_civicrm_activity;

TRUNCATE TABLE log_civicrm_activity_contact;

TRUNCATE TABLE log_civicrm_address;

TRUNCATE TABLE log_civicrm_address_format;

TRUNCATE TABLE log_civicrm_afform_submission;

TRUNCATE TABLE log_civicrm_batch;

TRUNCATE TABLE log_civicrm_campaign;

TRUNCATE TABLE log_civicrm_campaign_group;

TRUNCATE TABLE log_civicrm_case;

TRUNCATE TABLE log_civicrm_case_activity;

TRUNCATE TABLE log_civicrm_case_contact;

TRUNCATE TABLE log_civicrm_case_type;

TRUNCATE TABLE log_civicrm_component;

TRUNCATE TABLE log_civicrm_contact;

TRUNCATE TABLE log_civicrm_contact_type;

TRUNCATE TABLE log_civicrm_contribution;

TRUNCATE TABLE log_civicrm_contribution_page;

TRUNCATE TABLE log_civicrm_contribution_product;

TRUNCATE TABLE log_civicrm_contribution_recur;

TRUNCATE TABLE log_civicrm_contribution_soft;

TRUNCATE TABLE log_civicrm_contribution_widget;

TRUNCATE TABLE log_civicrm_country;

TRUNCATE TABLE log_civicrm_county;

TRUNCATE TABLE log_civicrm_currency;

TRUNCATE TABLE log_civicrm_custom_field;

TRUNCATE TABLE log_civicrm_custom_group;

TRUNCATE TABLE log_civicrm_cxn;

TRUNCATE TABLE log_civicrm_dashboard;

TRUNCATE TABLE log_civicrm_dashboard_contact;

TRUNCATE TABLE log_civicrm_dedupe_exception;

TRUNCATE TABLE log_civicrm_dedupe_rule;

TRUNCATE TABLE log_civicrm_dedupe_rule_group;

TRUNCATE TABLE log_civicrm_discount;

TRUNCATE TABLE log_civicrm_domain;

TRUNCATE TABLE log_civicrm_email;

TRUNCATE TABLE log_civicrm_entity_batch;

TRUNCATE TABLE log_civicrm_entity_file;

TRUNCATE TABLE log_civicrm_entity_financial_account;

TRUNCATE TABLE log_civicrm_entity_financial_trxn;

TRUNCATE TABLE log_civicrm_entity_tag;

TRUNCATE TABLE log_civicrm_event;

TRUNCATE TABLE log_civicrm_events_in_carts;

TRUNCATE TABLE log_civicrm_event_carts;

TRUNCATE TABLE log_civicrm_extension;

TRUNCATE TABLE log_civicrm_file;

TRUNCATE TABLE log_civicrm_financial_account;

TRUNCATE TABLE log_civicrm_financial_item;

TRUNCATE TABLE log_civicrm_financial_trxn;

TRUNCATE TABLE log_civicrm_financial_type;

TRUNCATE TABLE log_civicrm_grant;

TRUNCATE TABLE log_civicrm_group;

TRUNCATE TABLE log_civicrm_group_contact;

TRUNCATE TABLE log_civicrm_group_nesting;

TRUNCATE TABLE log_civicrm_group_organization;

TRUNCATE TABLE log_civicrm_im;

TRUNCATE TABLE log_civicrm_install_canary;

TRUNCATE TABLE log_civicrm_job;

TRUNCATE TABLE log_civicrm_line_item;

TRUNCATE TABLE log_civicrm_location_type;

TRUNCATE TABLE log_civicrm_loc_block;

TRUNCATE TABLE log_civicrm_mailing;

TRUNCATE TABLE log_civicrm_mailing_abtest;

TRUNCATE TABLE log_civicrm_mailing_bounce_pattern;

TRUNCATE TABLE log_civicrm_mailing_bounce_type;

TRUNCATE TABLE log_civicrm_mailing_component;

TRUNCATE TABLE log_civicrm_mailing_group;

TRUNCATE TABLE log_civicrm_mailing_job;

TRUNCATE TABLE log_civicrm_mailing_spool;

TRUNCATE TABLE log_civicrm_mailing_trackable_url;

TRUNCATE TABLE log_civicrm_mail_settings;

TRUNCATE TABLE log_civicrm_managed;

TRUNCATE TABLE log_civicrm_mapping;

TRUNCATE TABLE log_civicrm_mapping_field;

TRUNCATE TABLE log_civicrm_membership;

TRUNCATE TABLE log_civicrm_membership_block;

TRUNCATE TABLE log_civicrm_membership_payment;

TRUNCATE TABLE log_civicrm_membership_status;

TRUNCATE TABLE log_civicrm_membership_type;

TRUNCATE TABLE log_civicrm_msg_template;

TRUNCATE TABLE log_civicrm_navigation;

TRUNCATE TABLE log_civicrm_note;

TRUNCATE TABLE log_civicrm_openid;

TRUNCATE TABLE log_civicrm_option_group;

TRUNCATE TABLE log_civicrm_option_value;

TRUNCATE TABLE log_civicrm_participant;

TRUNCATE TABLE log_civicrm_participant_payment;

TRUNCATE TABLE log_civicrm_participant_status_type;

TRUNCATE TABLE log_civicrm_payment_processor;

TRUNCATE TABLE log_civicrm_payment_processor_type;

TRUNCATE TABLE log_civicrm_payment_token;

TRUNCATE TABLE log_civicrm_pcp;

TRUNCATE TABLE log_civicrm_pcp_block;

TRUNCATE TABLE log_civicrm_phone;

TRUNCATE TABLE log_civicrm_pledge;

TRUNCATE TABLE log_civicrm_pledge_block;

TRUNCATE TABLE log_civicrm_pledge_payment;

TRUNCATE TABLE log_civicrm_preferences_date;

TRUNCATE TABLE log_civicrm_premiums;

TRUNCATE TABLE log_civicrm_premiums_product;

TRUNCATE TABLE log_civicrm_price_field;

TRUNCATE TABLE log_civicrm_price_field_value;

TRUNCATE TABLE log_civicrm_price_set;

TRUNCATE TABLE log_civicrm_price_set_entity;

TRUNCATE TABLE log_civicrm_print_label;

TRUNCATE TABLE log_civicrm_product;

TRUNCATE TABLE log_civicrm_queue;

TRUNCATE TABLE log_civicrm_recurring_entity;

TRUNCATE TABLE log_civicrm_relationship;

TRUNCATE TABLE log_civicrm_relationship_type;

TRUNCATE TABLE log_civicrm_report_instance;

TRUNCATE TABLE log_civicrm_saved_search;

TRUNCATE TABLE log_civicrm_search_display;

TRUNCATE TABLE log_civicrm_search_segment;

TRUNCATE TABLE log_civicrm_setting;

TRUNCATE TABLE log_civicrm_site_token;

TRUNCATE TABLE log_civicrm_sms_provider;

TRUNCATE TABLE log_civicrm_state_province;

TRUNCATE TABLE log_civicrm_status_pref;

TRUNCATE TABLE log_civicrm_survey;

TRUNCATE TABLE log_civicrm_tag;

TRUNCATE TABLE log_civicrm_tell_friend;

TRUNCATE TABLE log_civicrm_timezone;

TRUNCATE TABLE log_civicrm_translation;

TRUNCATE TABLE log_civicrm_uf_field;

TRUNCATE TABLE log_civicrm_uf_group;

TRUNCATE TABLE log_civicrm_uf_join;

TRUNCATE TABLE log_civicrm_uf_match;

TRUNCATE TABLE log_civicrm_user_job;

TRUNCATE TABLE log_civicrm_value_custom_1;

TRUNCATE TABLE log_civicrm_value_emergency_con_2;

TRUNCATE TABLE log_civicrm_value_medical_notes_3;

TRUNCATE TABLE log_civicrm_website;

TRUNCATE TABLE log_civicrm_word_replacement;

TRUNCATE TABLE log_civicrm_worldregion;

SET
    FOREIGN_KEY_CHECKS = 1;
