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
        add_filter( 'gform_confirmation_anchor', '__return_true' );
    }

    public function contactFormHandler($entry, $form)
    {
        // die("Contact Form Was Submitted");

    }
    
    // Array ( [id] => 9 [status] => active [form_id] => 3 [ip] => 127.0.0.1 [source_url] => https://local.mycodelicforest.org/register/ [currency] => USD 
    // [post_id] => [date_created] => 2025-02-27 00:43:14 [date_updated] => 2025-02-27 00:43:14 [is_starred] => 0 [is_read] => 0 
    // [user_agent] => Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36 
    // [payment_status] => [payment_date] => [payment_amount] => [payment_method] => [transaction_id] => [is_fulfilled] => [created_by] => 1 
    // [transaction_type] => [source_id] => 44 [4.2] => [4.3] => 
    // Test [4.4] => [4.6] => Smith [4.8] => [1] => 1234qwerasf [2] => t1234qwer@mailinator.com [5] => (555) 555-5555 )

    public function registrationFormHandler($entry, $form)
    {
        // Sanitize user input
        $username = sanitize_user($entry[1]);
        $email = sanitize_email($entry[2]);
        $first_name = sanitize_text_field($entry[4.3]);
        $last_name = sanitize_text_field($entry[4.6]);
        $phone = sanitize_text_field($entry[5]);

        // Prepare usermeta
        $usermeta = array(
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            // You can add additional fields as needed.
        );

        // Initialize errors object
        $errors = new WP_Error();

        // Basic validation
        if (empty($username) || empty($email) ) {
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

        // Check if their phone number is already registered
        $user_query = new WP_User_Query([
            'meta_query' => [
                [
                    'key' => 'phone',
                    'value' => $phone,
                ],
            ],
        ]);
        if($user_query->get_total() > 0){
            $errors->add('phone_exists', 'That phone number is already registered.');
        }

        // If there are no errors, create the user
        if (empty($errors->errors)) {
            $userdata = array(
                'user_login' => $username,
                'user_email' => $email,
                'role' => 'subscriber',  // Or any default role
            );

            // Insert the user
            $user_id = wp_insert_user( $userdata );
            if ( is_wp_error( $user_id ) ) {
                echo '<p>Error: ' . esc_html( $user_id->get_error_message() ) . '</p>';
            } else {
                // Insert the user meta.
                foreach ( $usermeta as $key => $value ) {
                    update_user_meta( $user_id, $key, $value );
                }
            
                // Get the user object.
                $user = get_userdata( $user_id );
            
                // Generate a proper password reset key.
                $reset_key = get_password_reset_key( $user );
            
                // Build the confirmation URL.
                $confirm_url = home_url( "/wp-login.php?action=rp&key={$reset_key}&login=" . rawurlencode( $user->user_login ) );
            
                // Send the confirmation email.
                $subject = 'Please confirm your registration';
                $message  = "Hi {$user->user_login},\n\n";
                $message .= "Please click the following link to activate your account and set a new password:\n\n";
                $message .= $confirm_url . "\n\n";
                $message .= "If you did not register, please ignore this email.";
                $headers  = array( 'From: Mycodelic Forest <no-reply@mycodelicforest.org>' );
                wp_mail( $email, $subject, $message, $headers );
            }
        } else {
            // Store error messages in a session variable
            $_SESSION['form_errors'] = $errors->get_error_messages();

            // Redirect back to the form page
            wp_redirect($_SERVER['HTTP_REFERER']);
            exit;
        }
    }
}
