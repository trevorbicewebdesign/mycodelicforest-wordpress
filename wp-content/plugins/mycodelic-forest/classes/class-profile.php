<?php

class MycodelicForestProfile{

    public function __construct()
    {
       
    }

    public function init()
    {
         // We'll hook into WordPress during construction or you can do it in init().
         add_action( 'show_user_profile', [ $this, 'show_extra_fields' ] );
         add_action( 'edit_user_profile', [ $this, 'show_extra_fields' ] );
 
         add_action( 'personal_options_update', [ $this, 'save_extra_fields' ] );
         add_action( 'edit_user_profile_update', [ $this, 'save_extra_fields' ] );

         add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_recaptcha_script'] );

         add_action( 'register_form', [ $this, 'add_recaptcha_to_registration'] );

         add_filter( 'registration_errors', 'verify_recaptcha_on_registration', 10, 3 );
    }

    public function enqueue_recaptcha_script() {
        wp_enqueue_script( 'google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true );
    }
    
    public function add_recaptcha_to_registration() {
        echo '<div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>';
    }

    public function verify_recaptcha_on_registration( $errors, $sanitized_user_login, $user_email ) {
        if ( isset( $_POST['g-recaptcha-response'] ) ) {
            $recaptcha_response = sanitize_text_field( $_POST['g-recaptcha-response'] );
            $response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret=YOUR_SECRET_KEY&response={$recaptcha_response}" );
            $response_body = wp_remote_retrieve_body( $response );
            $result = json_decode( $response_body, true );
            
            if ( ! isset( $result['success'] ) || true !== $result['success'] ) {
                $errors->add( 'captcha_invalid', __( '<strong>ERROR</strong>: reCAPTCHA verification failed, please try again.' ) );
            }
        } else {
            $errors->add( 'captcha_missing', __( '<strong>ERROR</strong>: Please complete the reCAPTCHA.' ) );
        }
        
        return $errors;
    }    

    public function extraFields()
    {
        // Street Address
        // Address Line 2
        // City
        // State
        // Zip
        // Phone
        // Attended burning man?
        // Years attended
    }

    /**
     * Display extra user profile fields on the "Edit Profile" screen.
     *
     * @param WP_User $user Current user object
     */
    public function show_extra_fields( $user ) {
        // Security check: Only show to users who can edit user profiles
        if ( ! current_user_can( 'edit_user', $user->ID ) ) {
            return;
        }
        ?>
        <h3><?php esc_html_e( 'MyCodelic Extra Profile Fields', 'textdomain' ); ?></h3>

        <table class="form-table">
            <tr>
            <th><label for="address_1"><?php esc_html_e( 'Address 1', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="text" 
                name="address_1" 
                id="address_1" 
                value="<?php echo esc_attr( get_user_meta( $user->ID, 'address_1', true ) ); ?>" 
                class="regular-text"
                />
            </td>
            </tr>
            <tr>
            <th><label for="address_2"><?php esc_html_e( 'Address 2', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="text" 
                name="address_2" 
                id="address_2" 
                value="<?php echo esc_attr( get_user_meta( $user->ID, 'address_2', true ) ); ?>" 
                class="regular-text"
                />
            </td>
            </tr>
            <tr>
            <th><label for="city"><?php esc_html_e( 'City', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="text" 
                name="city" 
                id="city" 
                value="<?php echo esc_attr( get_user_meta( $user->ID, 'city', true ) ); ?>" 
                class="regular-text"
                />
            </td>
            </tr>
            <tr>
            <th><label for="state"><?php esc_html_e( 'State', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="text" 
                name="state" 
                id="state" 
                value="<?php echo esc_attr( get_user_meta( $user->ID, 'state', true ) ); ?>" 
                class="regular-text"
                />
            </td>
            </tr>
            <tr>
            <th><label for="zip"><?php esc_html_e( 'Zip', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="text" 
                name="zip" 
                id="zip" 
                value="<?php echo esc_attr( get_user_meta( $user->ID, 'zip', true ) ); ?>" 
                class="regular-text"
                />
            </td>
            </tr>
            <tr>
            <th><label for="phone"><?php esc_html_e( 'Phone', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="text" 
                name="phone" 
                id="phone" 
                value="<?php echo esc_attr( get_user_meta( $user->ID, 'phone', true ) ); ?>" 
                class="regular-text"
                />
            </td>
            </tr>
            <tr>
            <th><label for="has_attended_burning_man"><?php esc_html_e( 'Has attended Burning Man', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="checkbox" 
                name="has_attended_burning_man" 
                id="has_attended_burning_man" 
                value="1" 
                <?php checked( get_user_meta( $user->ID, 'has_attended_burning_man', true ), 1 ); ?>
                />
            </td>
            </tr>
            <tr>
            <th><label for="years_attended"><?php esc_html_e( 'Years attended', 'textdomain' ); ?></label></th>
            <td>
                <input 
                type="text" 
                name="years_attended" 
                id="years_attended" 
                value="<?php echo esc_attr( get_user_meta( $user->ID, 'years_attended', true ) ); ?>" 
                class="regular-text"
                />
            </td>
            </tr>
        </table>
        <?php
    }

    /**
     * Save our extra user profile fields
     *
     * @param int $user_id
     */
    public function save_extra_fields( $user_id ) {
        // Security check: can the current user edit this user?
        if ( ! current_user_can( 'edit_user', $user_id ) ) {
            return false;
        }

        // Grab form inputs and sanitize
        $address_1 = isset( $_POST['address_1'] ) ? sanitize_text_field( $_POST['address_1'] ) : '';
        $address_2 = isset( $_POST['address_2'] ) ? sanitize_text_field( $_POST['address_2'] ) : '';
        $city = isset( $_POST['city'] ) ? sanitize_text_field( $_POST['city'] ) : '';
        $state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : '';
        $zip = isset( $_POST['zip'] ) ? sanitize_text_field( $_POST['zip'] ) : '';
        $phone = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
        $has_attended_burning_man = isset( $_POST['has_attended_burning_man'] ) ? 1 : 0;
        $years_attended = isset( $_POST['years_attended'] ) ? sanitize_text_field( $_POST['years_attended'] ) : '';

        // Save to user meta
        update_user_meta( $user_id, 'address_1', $address_1 );
        update_user_meta( $user_id, 'address_2', $address_2 );
        update_user_meta( $user_id, 'city', $city );
        update_user_meta( $user_id, 'state', $state );
        update_user_meta( $user_id, 'zip', $zip );
        update_user_meta( $user_id, 'phone', $phone );
        update_user_meta( $user_id, 'has_attended_burning_man', $has_attended_burning_man );
        update_user_meta( $user_id, 'years_attended', $years_attended );
    }

    public function saveProfileFields(array $profile)
    {
        // validate the profile fields
        if( !isset($profile['first_name']) || empty($profile['first_name']) ){
            return new WP_Error('missing_first_name', 'First name is required');
        }
    }



}