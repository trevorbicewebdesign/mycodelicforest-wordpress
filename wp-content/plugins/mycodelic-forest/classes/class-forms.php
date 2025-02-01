<?php

class MycodelicForestForms
{
    public function __construct()
    {

    }

    public function init()
    {
        add_action('gform_after_submission_4', array($this, 'contactFormHandler'), 10, 2);
        add_action('gform_after_submission_3', array($this, 'registrationFormHandler'), 10, 2);
    }

    public function contactFormHandler($entry, $form)
    {
        // die("Contact Form Was Submitted");

    }

    public function registrationFormHandler($entry, $form)
    {
        // Sanitize user input
        $username = sanitize_user($entry[1]);
        $email = sanitize_email($entry[2]);
        $password = $entry[3]; // Consider additional password strength validation

        $first_name = sanitize_text_field($entry[4.3]);
        $last_name = sanitize_text_field($entry[4.6]);

        $street_address = sanitize_text_field($entry[4]);
        $address_line_2 = sanitize_text_field($entry[5]);
        $city = sanitize_text_field($entry[6]);
        $state = sanitize_text_field($entry[7]);
        $zip = sanitize_text_field($entry[8]);
        $country = sanitize_text_field($entry[9]);

        $phone = sanitize_text_field($entry[10]);
        $bio = sanitize_textarea_field($entry[11]);

        $attended_burning_man = sanitize_text_field($entry[15.1]);
        $years_attended = sanitize_text_field($entry[14.1]);

        // Prepare usermeta
        $usermeta = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'street_address' => $street_address,
            'address_line_2' => $address_line_2,
            'city' => $city,
            'state' => $state,
            'zip' => $zip,
            'country' => $country,
            'phone' => $phone,
            'bio' => $bio,
            // You can add additional fields as needed.
        );

        // Initialize errors object
        $errors = new WP_Error();

        // Basic validation
        if (empty($username) || empty($email) || empty($password)) {
            $errors->add('field', 'Please fill in all required fields.');
        }
        if (!is_email($email)) {
            $errors->add('email_invalid', 'The email address is not valid.');
        }
        if (username_exists($username)) {
            $errors->add('username_exists', 'That username is already registered.');
        }
        if (email_exists($email)) {
            $errors->add('email_exists', 'That email address is already registered.');
        }

        // If there are no errors, create the user
        if (empty($errors->errors)) {
            $userdata = array(
                'user_login' => $username,
                'user_email' => $email,
                'user_pass' => $password,  // WordPress hashes this automatically
                'role' => 'subscriber',  // Or any default role
            );

            $user_id = wp_insert_user($userdata);

            if (is_wp_error($user_id)) {
                // Handle error returned from user creation
                echo '<p>Error: ' . esc_html($user_id->get_error_message()) . '</p>';
            } else {
                // Insert the user meta
                foreach ($usermeta as $key => $value) {
                    update_user_meta($user_id, $key, $value);
                }

                // Generate a unique confirmation token
                $confirmation_token = wp_generate_password(20, false);
                update_user_meta($user_id, 'account_activation_token', $confirmation_token);
                update_user_meta($user_id, 'account_activation_status', 'pending');

                // Prepare the confirmation URL
                // Make sure to create a page or endpoint that handles this action.
                $confirm_url = add_query_arg(
                    array(
                        'action' => 'confirm_registration',
                        'user_id' => $user_id,
                        'token' => $confirmation_token,
                    ),
                    home_url('/confirm-registration/')
                );

                // Send the confirmation email
                $subject = 'Please confirm your registration';
                $message = "Hi {$username},\n\n";
                $message .= "Please click the following link to activate your account:\n\n";
                $message .= $confirm_url . "\n\n";
                $message .= "If you did not register, please ignore this email.";

                wp_mail($email, $subject, $message);

                // Inform the user to check their email for confirmation (do not log them in)
                // echo '<p>Thank you for registering! Please check your email to confirm your account before logging in.</p>';
            }
        } else {
            // Display error messages
            foreach ($errors->get_error_messages() as $message) {
                // echo '<p>' . esc_html($message) . '</p>';
            }
        }
    }


}
