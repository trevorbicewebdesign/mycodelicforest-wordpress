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

        // add_action( 'init', [$this, 'mycodelic_add_rewrite_rules'] );
        //add_filter( 'query_vars', [$this, 'mycodelic_query_vars'] );

        add_shortcode('mycodelic_profile_form', [$this, 'render_profile_form']);
        
        
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
                'type' => 'text',
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
                'type' => 'text',
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
    public function show_extra_fields($user) {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }
        
        // Get Gravity Form ID 6.
        $form = GFAPI::get_form(6); 

        print_r($form);
        
        echo '<style>
                .gform-grid-col { width: 100%; display: inline-block; direction: rtl; text-align: left; }
                .gform-grid-col label { display: inline-block; min-width: 160px; }
              </style>';
        echo '<h3>MyCodelic Profile Fields</h3>';
        echo '<table class="form-table">';
        
        foreach ($form['fields'] as $field) {
            // Skip fields that you don't want to show.
            if ( in_array($field->id, [16, 18]) ) {
                continue;
            }
        
            $meta_key = '';
            $value = '';
        
            // Special handling for field 14 (years_attended)
            if ($field->id == 14) {
                // Retrieve the stored value (assumed to be JSON)
                $stored = get_user_meta($user->ID, 'years_attended', true);
                $stored = json_decode($stored, true);
                // Generate the field HTML with the pre-populated value.
                $field_html = GFCommon::get_field_input($field, $stored, $form);
                // Force all checkboxes for field 14 to have the same name attribute.
                $field_html = preg_replace('/name=[\'"]years_attended\.\d+[\'"]/', 'name="years_attended[]"', $field_html);
            } else {
                // For non–field 14, use your mapping.
                // Check for composite fields (with multiple inputs)
                if (!empty($field->inputs) && is_array($field->inputs)) {
                    $composite_values = array();
                    foreach ($field->inputs as $input) {
                        $input_id = (string)$input['id'];
                        // Map the input id to a meta key.
                        switch ($input_id) {
                            case '16.3':
                                $meta_key = 'first_name';
                                break;
                            case '16.6':
                                $meta_key = 'last_name';
                                break;
                            case '5':
                                $meta_key = 'user_phone';
                                break;
                            case '9.1':
                                $meta_key = 'address_1';
                                break;
                            case '9.2':
                                $meta_key = 'address_2';
                                break;
                            case '9.3':
                                $meta_key = 'city';
                                break;
                            case '9.4':
                                $meta_key = 'state';
                                break;
                            case '9.5':
                                $meta_key = 'zip';
                                break;
                            case '9.6':
                                $meta_key = 'country';
                                break;
                            case '13':
                                $meta_key = 'user_about_me';
                                break;
                            case '6':
                                $meta_key = 'playa_name';
                                break;
                            case '19':
                                $meta_key = 'has_attended_burning_man';
                                break;
                            default:
                                $meta_key = 'input_' . $input_id;
                        }
                        $composite_values[$input['id']] = get_user_meta($user->ID, $meta_key, true);
                    }
                    $value = $composite_values;
                } else {
                    // Single input field.
                    if (!empty($field->adminLabel)) {
                        $meta_key = $field->adminLabel;
                    } else {
                        $field_id = (string)$field->id;
                        switch ($field_id) {
                            case '16.3':
                                $meta_key = 'first_name';
                                break;
                            case '16.6':
                                $meta_key = 'last_name';
                                break;
                            case '5':
                                $meta_key = 'user_phone';
                                break;
                            case '6':
                                $meta_key = 'playa_name';
                                break;
                            case '13':
                                $meta_key = 'user_about_me';
                                break;
                            case '19':
                                $meta_key = 'has_attended_burning_man';
                                break;
                            case '9.1':
                                $meta_key = 'address_1';
                                break;
                            case '9.2':
                                $meta_key = 'address_2';
                                break;
                            case '9.3':
                                $meta_key = 'city';
                                break;
                            case '9.4':
                                $meta_key = 'state';
                                break;
                            case '9.5':
                                $meta_key = 'zip';
                                break;
                            case '9.6':
                                $meta_key = 'country';
                                break;
                            default:
                                $meta_key = 'input_' . $field->id;
                        }
                    }
                    $value = get_user_meta($user->ID, $meta_key, true);
                }
        
                $field_html = GFCommon::get_field_input($field, $value, $form);
        
                // Replace default input markers with your custom names.
                $patterns = [
                    '/input_5/',
                    '/input_9_1/',
                    '/input_9\.1/',
                    '/input_9_2/',
                    '/input_9\.2/',
                    '/input_9_3/',
                    '/input_9_4/',
                    '/input_9_5/',
                    '/input_9_6/',
                    '/input_13/',
                    '/input_6/',
                    '/input_19/',
                    '/input_14/',
                ];
                $replacements = [
                    'user_phone',
                    'address_1',
                    'address_1',
                    'address_2',
                    'address_2',
                    'city',
                    'state',
                    'zip',
                    'country',
                    'user_about_me',
                    'playa_name',
                    'has_attended_burning_man',
                    'years_attended',
                ];
                $field_html = preg_replace($patterns, $replacements, $field_html);
            }
            
            echo '<tr>';
            echo '<th><label>' . esc_html($field->label) . '</label></th>';
            echo "<td>{$field_html}</td>";
            echo '</tr>';
        }
        
        echo '</table>';
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

    public function render_profile_form()
    {
        if (!is_user_logged_in()) {
            return '<p>' . esc_html__('You must be logged in to update your profile.', 'textdomain') . '</p>';
        }

        $user_id  = get_current_user_id();
        $fields   = $this->get_extra_fields_definitions();
        $output   = '';

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_profile'])) {
            check_admin_referer('update_profile_nonce');

            $this->update_extra_fields($user_id, $_POST);
            $output .= '<p>' . esc_html__('Profile updated successfully.', 'textdomain') . '</p>';
        }

        // Start form
        $output .= '<form method="post">';
        $output .= wp_nonce_field('update_profile_nonce', '_wpnonce', true, false);

        foreach ($fields as $key => $field) {
            $value = get_user_meta($user_id, $key, true);
            $output .= '<p>';
            $output .= '<label for="' . esc_attr($key) . '">' . esc_html($field['label']) . ':</label><br>';

            if ('checkbox' === $field['type']) {
                $output .= '<input type="checkbox" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" value="1" ' . checked($value, 1, false) . ' />';
            } else {
                $output .= '<input type="text" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" value="' . esc_attr($value) . '" />';
            }

            $output .= '</p>';
        }

        $output .= '<p><input type="submit" name="submit_profile" value="' . esc_attr__('Update Profile', 'textdomain') . '"></p>';
        $output .= '</form>';

        return $output;
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