<?php

class MycodelicForestForms
{
    const REGISTRATION_FORM_ID = 3;

    public function __construct()
    {

    }

    public function init()
    {
        add_action('gform_after_submission_4', array($this, 'contactFormHandler'), 10, 2);

        // Registration: validate before the entry is created so the user sees inline errors,
        // then create the account after submission.
        add_filter('gform_validation_' . self::REGISTRATION_FORM_ID, array($this, 'registrationFormValidation'));
        add_action('gform_after_submission_' . self::REGISTRATION_FORM_ID, array($this, 'registrationFormHandler'), 10, 2);
        add_filter('gform_confirmation_' . self::REGISTRATION_FORM_ID, array($this, 'registrationConfirmation'), 10, 4);

        add_filter( 'gform_confirmation_anchor', '__return_true' );
    }

    public function contactFormHandler($entry, $form)
    {
        // die("Contact Form Was Submitted");

    }

    /**
     * Registration form field IDs:
     *   1   = Username
     *   2   = Email
     *   4.3 = First name (Gravity Forms "advanced" name field: .3 is first, .6 is last)
     *   4.6 = Last name
     *   5   = Phone (optional)
     */

    /**
     * Returns true if the phone number is already stored on any user, under either
     * the registration meta key ('phone') or the profile meta key ('user_phone').
     */
    public function phoneRegistered($phone, $exclude_user_id = 0)
    {
        $phone = trim((string) $phone);
        if ($phone === '') {
            return false;
        }

        $args = [
            'fields'      => 'ID',
            'number'      => 1,
            'count_total' => true,
            'meta_query'  => [
                'relation' => 'OR',
                [
                    'key'   => 'phone',
                    'value' => $phone,
                ],
                [
                    'key'   => 'user_phone',
                    'value' => $phone,
                ],
            ],
        ];
        if ($exclude_user_id) {
            $args['exclude'] = [(int) $exclude_user_id];
        }

        $user_query = new WP_User_Query($args);

        return $user_query->get_total() > 0;
    }

    /**
     * gform_validation: reject duplicate usernames, emails and phone numbers with
     * inline field errors before Gravity Forms stores the entry.
     */
    public function registrationFormValidation($validation_result)
    {
        $form = $validation_result['form'];

        $username = sanitize_user(rgpost('input_1'));
        $email    = sanitize_email(rgpost('input_2'));
        $phone    = sanitize_text_field(rgpost('input_5'));

        foreach ($form['fields'] as $field) {
            $message = '';

            switch ((int) $field->id) {
                case 1:
                    if ($username === '' || !validate_username($username)) {
                        $message = 'Please choose a username using letters, numbers, spaces, dots, dashes or underscores.';
                    } elseif (username_exists($username)) {
                        $message = 'That username is already registered.';
                    }
                    break;

                case 2:
                    if (!is_email($email)) {
                        $message = 'The email address is not valid.';
                    } elseif (email_exists($email)) {
                        $message = 'That email address is already registered. Try logging in or resetting your password.';
                    }
                    break;

                case 5:
                    if ($phone !== '' && $this->phoneRegistered($phone)) {
                        $message = 'That phone number is already registered.';
                    }
                    break;
            }

            if ($message !== '') {
                $field->failed_validation  = true;
                $field->validation_message = $message;
                $validation_result['is_valid'] = false;
            }
        }

        $validation_result['form'] = $form;

        return $validation_result;
    }

    /**
     * gform_confirmation: tell the new member what happens next.
     */
    public function registrationConfirmation($confirmation, $form, $entry, $ajax)
    {
        return '<div class="mycodelic-registration-confirmation">'
            . '<p><strong>Thanks for registering!</strong></p>'
            . '<p>We just emailed you a link to activate your account and set your password. '
            . 'If it does not show up within a few minutes, please check your spam folder.</p>'
            . '</div>';
    }

    public function registrationFormHandler($entry, $form)
    {
        // Sanitize user input. Entry keys for multi-input fields are strings like "4.3";
        // never index with a float literal, PHP truncates it to 4.
        $username   = sanitize_user(rgar($entry, '1'));
        $email      = sanitize_email(rgar($entry, '2'));
        $first_name = sanitize_text_field(rgar($entry, '4.3'));
        $last_name  = sanitize_text_field(rgar($entry, '4.6'));
        $phone      = sanitize_text_field(rgar($entry, '5'));

        // Defensive re-check; registrationFormValidation should already have caught these,
        // but two submissions can race.
        if ($username === '' || !is_email($email) || username_exists($username) || email_exists($email)) {
            error_log(sprintf(
                'Mycodelic Forest registration skipped for entry %d: username "%s" / email "%s" invalid or already registered.',
                (int) rgar($entry, 'id'), $username, $email
            ));
            return;
        }

        $userdata = array(
            'user_login' => $username,
            'user_email' => $email,
            // Random throwaway password; the member sets their own via the activation link.
            'user_pass'  => wp_generate_password(32, true, true),
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'role'       => get_option('default_role', 'subscriber'),
        );

        $user_id = wp_insert_user($userdata);
        if (is_wp_error($user_id)) {
            error_log(sprintf(
                'Mycodelic Forest registration failed for entry %d (%s): %s',
                (int) rgar($entry, 'id'), $email, $user_id->get_error_message()
            ));
            return;
        }

        // Store the phone under both keys so the profile form pre-populates and the
        // duplicate check covers either source. Never store an empty phone.
        if ($phone !== '') {
            update_user_meta($user_id, 'phone', $phone);
            update_user_meta($user_id, 'user_phone', $phone);
        }

        // Remember which Gravity Forms entry created this account.
        update_user_meta($user_id, 'registration_entry_id', (int) rgar($entry, 'id'));

        $user = get_userdata($user_id);

        // Generate a password reset key; this doubles as the activation link.
        $reset_key = get_password_reset_key($user);
        if (is_wp_error($reset_key)) {
            error_log('Mycodelic Forest registration: could not create activation key for user ' . $user_id . ': ' . $reset_key->get_error_message());
            return;
        }

        $confirm_url = network_site_url("wp-login.php?action=rp&key={$reset_key}&login=" . rawurlencode($user->user_login), 'login');

        $subject  = 'Please confirm your registration';
        $message  = "Hi {$user->user_login},\n\n";
        $message .= "Please click the following link to activate your account and set a new password:\n\n";
        $message .= $confirm_url . "\n\n";
        $message .= "This link expires in 24 hours. If it has expired, use the \"Lost your password?\" link on the login page to request a new one.\n\n";
        $message .= "If you did not register, please ignore this email.";
        $headers  = array('From: Mycodelic Forest <no-reply@mycodelicforest.org>');

        if (!wp_mail($email, $subject, $message, $headers)) {
            error_log('Mycodelic Forest registration: activation email failed to send to ' . $email);
        }
    }
}
