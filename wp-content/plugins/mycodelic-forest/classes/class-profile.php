<?php
class MycodelicForestProfile
{

    protected $messages;
    public function __construct(MycodelicForestMessages $messages)
    {
        $this->messages = $messages;
    }

    public function init()
    {
        // Admin side: display and save extra fields on the user profile page.
        add_action('show_user_profile', [$this, 'show_extra_fields']);
        add_action('edit_user_profile', [$this, 'show_extra_fields']);
        add_action('personal_options_update', [$this, 'admin_save_extra_fields']);
        add_action('edit_user_profile_update', [$this, 'admin_save_extra_fields']);

        // Other hooks (recaptcha, redirection, etc.) remain the same.
        add_action('wp_enqueue_scripts', [$this, 'enqueue_recaptcha_script']);
        add_action('register_form', [$this, 'add_recaptcha_to_registration']);
        add_action('template_redirect', [$this, 'mycodelic_redirect_incomplete_profile']);
        
        add_action('gform_after_submission_6', [$this, 'update_user_profile_from_gravity'], 10, 2);

        // Hook into Gravity Forms dynamic population for form ID 6
        add_filter('gform_field_value_first_name', [$this, 'populate_first_name']);
        add_filter('gform_field_value_last_name', [$this, 'populate_last_name']);
        add_filter('gform_field_value_user_email', [$this, 'populate_user_email']);
        add_filter('gform_field_value_user_phone', [$this, 'populate_user_phone']);
        add_filter('gform_field_value_address_1', [$this, 'populate_address']);
        add_filter('gform_field_value_city', [$this, 'populate_city']);
        add_filter('gform_field_value_state', [$this, 'populate_state']);
        add_filter('gform_field_value_country', [$this, 'populate_country']);
        add_filter('gform_field_value_zip', [$this, 'populate_zip']);
        add_filter('gform_field_value_user_about_me', [$this, 'populate_user_about_me']);
        add_filter('gform_field_value_attended_burning_man', [$this, 'populate_attended_burning_man']);
        add_filter('gform_field_value_playa_name', [$this, 'populate_playa_name']);
        add_filter('gform_field_value_years_attended', [$this, 'populate_years_attended']);

        add_filter('gform_entry_id_pre_save_lead', [$this, 'prevent_gravity_entry_save'], 10, 2);

        add_filter('gform_form_tag', function ($form_tag, $form) {
            if ($form['id'] == 6) {
                $form_tag = preg_replace('/action=[\'"].*?[\'"]/', 'action="' . esc_url($_SERVER['REQUEST_URI']) . '"', $form_tag);
            }
            return $form_tag;
        }, 10, 2);        

        add_action('gform_after_submission_6', [$this, 'gform_after_submission_6'], 10, 2);

        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_phone_mask']);
    }

    public function gform_after_submission_6($entry, $form) {
        $this->messages->set_message('Profile updated successfully!', 'success');
        
    }

    public function prevent_gravity_entry_save($entry_id, $form) {
    if ($form['id'] == 6) {
        return null; // Prevents the entry from being saved
    }
    return $entry_id;
}

    public function enqueue_recaptcha_script()
    {
        wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
    }

    public function add_recaptcha_to_registration()
    {
        echo '<div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>';
    }

    public function verify_recaptcha_on_registration($errors, $sanitized_user_login, $user_email)
    {
        if (isset($_POST['g-recaptcha-response'])) {
            $recaptcha_response = sanitize_text_field($_POST['g-recaptcha-response']);
            $response = wp_remote_get("https://www.google.com/recaptcha/api/siteverify?secret=YOUR_SECRET_KEY&response={$recaptcha_response}");
            $response_body = wp_remote_retrieve_body($response);
            $result = json_decode($response_body, true);

            if (!isset($result['success']) || true !== $result['success']) {
                $errors->add('captcha_invalid', __('<strong>ERROR</strong>: reCAPTCHA verification failed, please try again.'));
            }
        } else {
            $errors->add('captcha_missing', __('<strong>ERROR</strong>: Please complete the reCAPTCHA.'));
        }

        return $errors;
    }

    public function mycodelic_redirect_incomplete_profile()
    {
        if (!is_user_logged_in()) {
            return; // Only logged-in users need a profile.
        }

        // Prevent redirect if already on the profile page
        if (is_page('profile')) {
            return;
        }

        // Check if the user has completed their profile
        if (!$this->profileComplete()) {
            wp_redirect(home_url('/profile/'));
            exit;
        }
    }

    public function profileComplete($user_id=NULL)
    {
        if ($user_id==NULL) {
            $user_id = get_current_user_id();
        }
    
        // Required fields for a valid profile
        $required_fields = [
            'address_1',
            'city',
            'state',
            'zip',
            'country',
            'user_phone',
        ];
    
        // Check required text fields are not empty
        foreach ($required_fields as $key) {
            $value = get_user_meta($user_id, $key, true);
            if (empty($value)) {
                return false;
            }
        }
    
        // Validate phone number (basic format check)
        $phone = get_user_meta($user_id, 'user_phone', true);
        if (!preg_match('/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/', $phone)) {
            return false; // Invalid phone format
        }
    
        // Validate ZIP code (basic check for numbers only, can be expanded)
        $zip = get_user_meta($user_id, 'zip', true);
        if (!preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
            return false; // Invalid ZIP format
        }
    
        // Check if "Has Attended Burning Man" is set
        $has_attended = get_user_meta($user_id, 'has_attended_burning_man', true) == '1';
        $years_attended = json_decode(get_user_meta($user_id, 'years_attended', true), true);
    
        if ($has_attended) {
            // If user has attended, they must have at least one year selected
            if (empty($years_attended) || !is_array($years_attended)) {
                return false;
            }
        } else {
            // If user has NOT attended, remove any stored years
            delete_user_meta($user_id, 'years_attended');
        }
    
        return true; // All checks passed, profile is complete
    }
    


    /**
     * Define the extra fields as an associative array.
     *
     * Each key is a user meta key, and its value is an array with a label and type.
     */
    public function get_extra_fields_definitions()
    {
        return [
            'address_1' => [
                'label' => __('Street Address', 'textdomain'),
                'type' => 'text',
            ],
            'address_2' => [
                'label' => __('Address Line 2', 'textdomain'),
                'type' => 'text',
            ],
            'city' => [
                'label' => __('City', 'textdomain'),
                'type' => 'text',
            ],
            'state' => [
                'label' => __('State / Province / Region', 'textdomain'),
                'type' => 'text',
            ],
            'country' => [
                'label' => __('Country', 'textdomain'),
                'type' => 'select',
                'options' => [
                    '' => __('Please select a country', 'textdomain'),
                    'United States' => __('United States', 'textdomain'),
                    'Canada' => __('Canada', 'textdomain'),
                    'United Kingdom' => __('United Kingdom', 'textdomain'),
                    'Australia' => __('Australia', 'textdomain'),
                    'France' => __('France', 'textdomain'),
                ],
            ],
            'zip' => [
                'label' => __('ZIP / Postal Code', 'textdomain'),
                'type' => 'text',
            ],
            'user_phone' => [
                'label' => __('Phone Number', 'textdomain'),
                'type' => 'text',
            ],
            'has_attended_burning_man' => [
                'label' => __('Have you been to Burning Man before?', 'textdomain'),
                'type' => 'radio',
                'options' => [
                    'Yes' => __('Yes', 'textdomain'),
                    'No' => __('No', 'textdomain'),
                ],
            ],
            'years_attended' => [
                'label' => __('Years attended', 'textdomain'),
                'type' => 'checkbox',
                'options' => [
                    '2024' => __('2024', 'textdomain'),
                    '2023' => __('2023', 'textdomain'),
                    '2022' => __('2022', 'textdomain'),
                    '2021' => __('2021', 'textdomain'),
                    '2020' => __('2020', 'textdomain'),
                    '2019' => __('2019', 'textdomain'),
                    '2018' => __('2018', 'textdomain'),
                    '2017' => __('2017', 'textdomain'),
                    '2016' => __('2016', 'textdomain'),
                    '2015' => __('2015', 'textdomain'),
                    '2014' => __('2014', 'textdomain'),
                    '2013' => __('2013', 'textdomain'),
                    '2012' => __('2012', 'textdomain'),
                    '2011' => __('2011', 'textdomain'),
                    '2010' => __('2010', 'textdomain'),
                    '2009' => __('2009', 'textdomain'),
                    '2008' => __('2008', 'textdomain'),
                    '2007' => __('2007', 'textdomain'),
                    '2006' => __('2006', 'textdomain'),
                    '2005' => __('2005', 'textdomain'),
                    '2004' => __('2004', 'textdomain'),
                    '2003' => __('2003', 'textdomain'),
                    '2002' => __('2002', 'textdomain'),
                    '2001' => __('2001', 'textdomain'),
                    '2000' => __('2000', 'textdomain'),
                    '1999' => __('1999', 'textdomain'),
                    '1998' => __('1998', 'textdomain'),
                    '1997' => __('1997', 'textdomain'),
                    '1996' => __('1996', 'textdomain'),
                    '1995' => __('1995', 'textdomain'),
                    '1994' => __('1994', 'textdomain'),
                    '1993' => __('1993', 'textdomain'),
                    '1992' => __('1992', 'textdomain'),
                    '1991' => __('1991', 'textdomain'),
                    '1990' => __('1990', 'textdomain'),
                    '1989' => __('1989', 'textdomain'),
                    '1988' => __('1988', 'textdomain'),
                    '1987' => __('1987', 'textdomain'),
                    '1986' => __('1986', 'textdomain'),
                ],
            ],
            'playa_name' => [
                'label' => __('Playa Name', 'textdomain'),
                'type' => 'text',
            ],
            'user_about_me' => [
                'label' => __('About Me', 'textdomain'),
                'type' => 'textarea',
            ],
        ];
    }

    public function enqueue_admin_phone_mask($hook) {
        // Only load on profile pages
        if ($hook === 'user-edit.php' || $hook === 'profile.php') {

            // Custom script to apply the mask
            wp_enqueue_script('jquery-masked-input', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.maskedinput/1.4.1/jquery.maskedinput.min.js', ['jquery'], '1.4.1', true);
            wp_add_inline_script('jquery-masked-input', "
                jQuery(document).ready(function($) {
                    jQuery('#user_phone').mask('(999) 999-9999');
                });
            ");
        }
    }

    
    /**
     * Retrieves the profile information for a given user.
     *
     * @param int|null $user_id The ID of the user whose profile is to be retrieved. If null, the current user's ID will be used.
     * @return array An associative array containing the user's profile information.
     * @throws \Exception If no user ID is provided and the current user is not logged in.
     */
    public function get_profile($user_id=NULL)
    {
        print_r($user_id);

        if ($user_id==NULL) {
            $user_id = get_current_user_id();
            if(is_wp_error($user_id)) {
                throw new \Exception('Error getting current user ID.');
            }
        }

        if(is_wp_error($user_id) || $user_id==0) {
            throw new \Exception('No user ID provided and no user is logged in.');
        }

        // Removed debug lines

        $fields = [
            'first_name',
            'last_name',
            'playa_name',
            'user_phone',
            'address_1',
            'address_2',
            'city',
            'state',
            'zip',
            'country',
            'user_about_me',
            'has_attended_burning_man',
            'years_attended',
        ];
        $profile = [];
        foreach ($fields as $field) {
            $profile[$field] = get_user_meta($user_id, $field, true);
        }
        $user = get_userdata($user_id);
        if ($user) {
            $profile['user_email'] = $user->user_email;
        }
        return $profile;
    }

    public function gravity_forms_profile_field_map()
    {
        return [
            'first_name' => 'input_16_3',
            'last_name' => 'input_16_6',
            'user_phone' => 'input_5',
            'address_1' => 'input_9_1',
            'address_2' => 'input_9_2',
            'city' => 'input_9_3',
            'state' => 'input_9_4',
            'zip' => 'input_9_5',
            'country' => 'input_9_6',
            'user_about_me' => 'input_13',
            'playa_name' => 'input_6',
            'has_attended_burning_man' => 'input_19',
            'years_attended' => 'input_14',
        ];
    }

    /**
     * Output extra fields for the admin profile pages.
     *
     * @param WP_User $user
     */
    public function show_extra_fields($user)
    {
        // Security check.
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }
        $fields = $this->get_extra_fields_definitions();
        // Get current value for the burning man attendance
        $has_attended = get_user_meta($user->ID, 'has_attended_burning_man', true);
        ?>
        <hr/>
        <h3><?php esc_html_e('Extra Profile Fields', 'textdomain'); ?></h3>
        <table class="form-table">
            <?php foreach ($fields as $key => $field): 
                // If this is the years_attended field, add an ID and conditional style
                $row_attributes = '';
                if ('years_attended' === $key) {
                    $display = ('Yes' === $has_attended) ? 'table-row' : 'none';
                    $row_attributes = 'id="years_attended_row" style="display:' . $display . ';"';
                }
                ?>
                <tr <?php echo $row_attributes; ?>>
                    <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
                    <td>
                        <?php
                        $value = get_user_meta($user->ID, $key, true);
                        if ('checkbox' === $field['type']) {
                            if (!empty($field['options']) && is_array($field['options'])) {
                                $value = json_decode($value, true); // Decode the JSON value
                                $counter = 0;
                                foreach ($field['options'] as $option_value => $option_label) {
                                    if ($counter % 3 == 0) {
                                        echo '<div style="clear:both;"></div>'; // Clear floats every 3 items
                                    }
                                    ?>
                                    <label style="display:inline-block;">
                                        <input type="checkbox" name="<?php echo esc_attr($key); ?>[]" value="<?php echo esc_attr($option_value); ?>"
                                            <?php if (is_array($value) && in_array($option_value, $value)) echo 'checked="checked"'; ?> />
                                        <?php echo esc_html($option_label); ?>
                                    </label>
                                    <?php
                                    $counter++;
                                }
                                echo '<div style="clear:both;"></div>'; // Clear floats at the end
                            }
                        } elseif ('radio' === $field['type']) {
                            if (!empty($field['options']) && is_array($field['options'])) {
                                foreach ($field['options'] as $option_value => $option_label) {
                                    ?>
                                    <label>
                                        <input type="radio" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($option_value); ?>"
                                            <?php checked($value, $option_value); ?> />
                                        <?php echo esc_html($option_label); ?>
                                    </label><br>
                                    <?php
                                }
                            }
                        } elseif ('select' === $field['type']) {
                            if (!empty($field['options']) && is_array($field['options'])) {
                                ?>
                                <select name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>">
                                    <?php foreach ($field['options'] as $option_value => $option_label) { ?>
                                        <option value="<?php echo esc_attr($option_value); ?>" <?php selected($value, $option_value); ?>>
                                            <?php echo esc_html($option_label); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <?php
                            }
                        } elseif ('textarea' === $field['type']) {
                            ?>
                            <textarea name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" rows="5" cols="30"><?php echo esc_textarea($value); ?></textarea>
                            <?php
                        } else {
                            ?>
                            <input type="text" name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>"
                                value="<?php echo esc_attr($value); ?>" class="regular-text" />
                            <?php
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <hr/>
        <script type="text/javascript">
        jQuery(document).ready(function($){
            // Listen for changes on the "has_attended_burning_man" radio buttons
            $('input[name="has_attended_burning_man"]').on('change', function(){
                if($('input[name="has_attended_burning_man"]:checked').val() == 'Yes'){
                    $('#years_attended_row').show();
                } else {
                    $('#years_attended_row').hide();
                }
            });
        });
        </script>
        <?php
    }


    /**
     * Update extra fields based on a provided data array.
     *
     * This function can be used both on the admin side and on the front end.
     *
     * @param int   $user_id
     * @param array $data
     */
    public function update_extra_fields($user_id, array $data)
    {
        $fields = $this->get_extra_fields_definitions();
        foreach ($fields as $key => $field) {
            if (isset($data[$key])) {
                if ('checkbox' === $field['type']) {
                    update_user_meta($user_id, $key, json_encode($data[$key]));
                } else {
                    update_user_meta($user_id, $key, sanitize_text_field($data[$key]));
                }
            } else {
                // For checkboxes, if not set, save a value of 0.
                if ('checkbox' === $field['type']) {
                    update_user_meta($user_id, $key, 0);
                }
            }
        }

        // If the user indicates they have NOT attended Burning Man, clear the years_attended data.
        if (isset($data['has_attended_burning_man']) && $data['has_attended_burning_man'] === 'No') {
            delete_user_meta($user_id, 'years_attended');
        }
    }

    /**
     * Save extra fields on the admin profile update.
     *
     * This simply calls our common update_extra_fields() method.
     *
     * @param int $user_id
     */
    public function admin_save_extra_fields($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return false;
        }
        $this->update_extra_fields($user_id, $_POST);
    }

    public function mycodelic_add_rewrite_rules() {
        // Add a rewrite rule for the URL /profile/
        add_rewrite_rule( '^profile/?$', 'index.php?profile_page=1', 'top' );
    }
    
    
    public function mycodelic_query_vars( $query_vars ) {
        $query_vars[] = 'profile_page';
        return $query_vars;
    }


    // Populate first name
    public function populate_first_name() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'first_name', true) : '';
    }

    // Populate last name
    public function populate_last_name() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'last_name', true) : '';
    }

    // Populate email
    public function populate_user_email() {
        $user = wp_get_current_user();
        return $user->user_email;
    }

    // Populate phone
    public function populate_user_phone() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'user_phone', true) : '';
    }

    // Populate street address
    public function populate_address() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'address_1', true) : '';
    }

    // Populate city
    public function populate_city() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'city', true) : '';
    }

    // Populate state
    public function populate_state() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'state', true) : '';
    }

    // Populate zip code
    public function populate_zip() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'zip', true) : '';
    }
    
    public function populate_country(){
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'country', true) : '';
    }
    public function populate_playa_name() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'playa_name', true) : '';
    }

    // Populate "About Me" field
    public function populate_user_about_me() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'user_about_me', true) : '';
    }

    public function populate_attended_burning_man() {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'has_attended_burning_man', true) : '';
    }

    public function populate_years_attended() {
        $user_id = get_current_user_id();
        $years_attended = $user_id ? get_user_meta($user_id, 'years_attended', true) : '';
        if (!empty($years_attended)) {
            return json_decode($years_attended);
            // return maybe_unserialize($years_attended); // Use this if saving as serialized array
        }
        return '';
    }

    public function update_user_profile_from_gravity($entry, $form) {
        // Get current user ID
        $user_id = get_current_user_id();
        
        // Ensure user is logged in
        if (!$user_id) {
            return;
        }
    
        // Map Gravity Forms fields to user meta fields
        $fields = [
            'first_name'             => rgar($entry, '16.3'),
            'last_name'              => rgar($entry, '16.6'),
            'playa_name'             => rgar($entry, '6'),
            'user_phone'             => rgar($entry, '5'),
            'address_1'              => rgar($entry, '9.1'),
            'address_2'              => rgar($entry, '9.2'),
            'city'                   => rgar($entry, '9.3'),
            'state'                  => rgar($entry, '9.4'),
            'zip'                    => rgar($entry, '9.5'),
            'country'                => rgar($entry, '9.6'),
            'user_about_me'          => rgar($entry, '13'),
            'has_attended_burning_man' => rgar($entry, '19'),
        ];
    
        // Check if the user has attended Burning Man
        $attended_burning_man = rgar($entry, '19'); // Checkbox field, should be 1 if checked
    
        // Handle Multi-Checkbox Field: "Years Attended" (Field ID: 14)
        $years_attended = [];
        if ($attended_burning_man == 'Yes') { // User has attended
            foreach ($form['fields'] as $field) {
                if ($field->id == 14 && !empty($field->inputs) && is_array($field->inputs)) {
                    foreach ($field->inputs as $input) {
                        if (!empty($entry[$input['id']])) {
                            $years_attended[] = sanitize_text_field($entry[$input['id']]);
                        }
                    }
                }
            }
    
            // Save the selected years if any
            if (!empty($years_attended)) {
                update_user_meta($user_id, 'years_attended', json_encode($years_attended));
            }
        } else {
            // If user has not attended, remove any previously stored years
            delete_user_meta($user_id, 'years_attended');
        }
    
        // Update user meta for other fields
        foreach ($fields as $key => $value) {
            if (!empty($value)) {
                update_user_meta($user_id, $key, sanitize_text_field($value));
            }
        }
    
        // Update user email if provided
        if (!empty($fields['user_email'])) {
            wp_update_user([
                'ID'         => $user_id,
                'user_email' => sanitize_email($fields['user_email']),
            ]);
        }
    }   

}