<?php
class MycodelicForestProfile
{
    protected $messages;
    protected $civicrm;
    public function __construct(MycodelicForestMessages $messages, MycodelicForestCiviCRM $civicrm)
    {
        $this->messages = $messages;
        $this->civicrm = $civicrm;
    }

    public function init()
    {
        // Admin side: display and save extra fields on the user profile page.
        add_action('show_user_profile', [$this, 'show_extra_fields']);
        add_action('edit_user_profile', [$this, 'show_extra_fields']);
        add_action('personal_options_update', [$this, 'admin_save_extra_fields']);
        add_action('edit_user_profile_update', [$this, 'admin_save_extra_fields']);

        // Other hooks (recaptcha, redirection, etc.) remain the same.
        add_action('template_redirect', [$this, 'mycodelic_redirect_incomplete_profile']);

        // Members-only per-user profile page at /profile/{user_nicename}/, built on
        // WordPress's native author archive machinery (author.html template in the
        // child theme), not a virtual page. The rewrite just points that URL at the
        // same query WP already uses for /author/{nicename}/.
        add_action('init', [$this, 'mycodelic_add_rewrite_rules']);
        // Canonicalize to /profile/ first, so an anonymous visit to /author/
        // ends up sent to login with the nicer /profile/ redirect_to target.
        add_action('template_redirect', [$this, 'redirect_author_archive_to_profile']);
        add_action('template_redirect', [$this, 'require_login_for_author_archive']);

        // Expose the profile fields with no native block equivalent (playa name,
        // about me, years attended, location, roles, roster history, avatar) to
        // the Block Bindings API, so the author.html template can bind ordinary
        // Heading/Paragraph/Image blocks straight to them in the Site Editor —
        // no shortcodes, no PHP in the template itself.
        add_action('init', [$this, 'register_profile_meta']);
        add_action('init', [$this, 'register_profile_binding_source']);
        add_filter('render_block', [$this, 'maybe_hide_edit_profile_button'], 10, 2);

        // The profile hero's background photo can't be driven by Block Bindings
        // (see set_profile_hero_background() for why) — same class of exception
        // as the edit-profile-button visibility, just for a background-image.
        add_filter('render_block', [$this, 'set_profile_hero_background'], 10, 2);

        // Flat "Roles" taxonomy on users (loosely Holacracy-inspired — org roles,
        // not WP capability roles). Admin-assignable from the user-edit screen;
        // terms themselves are managed at Users -> Roles in wp-admin.
        add_action('init', [$this, 'register_roles_taxonomy']);
        add_action('show_user_profile', [$this, 'show_roles_field']);
        add_action('edit_user_profile', [$this, 'show_roles_field']);
        add_action('personal_options_update', [$this, 'save_roles_field']);
        add_action('edit_user_profile_update', [$this, 'save_roles_field']);

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



        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_phone_mask']);

        add_action('after_setup_theme', [$this, 'hide_admin_bar_for_non_privileged_users']);

        add_filter('manage_users_columns', [$this, 'my_custom_user_columns']);
        add_filter('manage_users_custom_column', [$this, 'my_custom_user_column_content'], 10, 3);
        add_filter('manage_users_sortable_columns', [$this, 'my_sortable_user_columns']);
        add_action('pre_get_users', [$this, 'my_users_orderby']);
    }


    // Add custom columns to the Users table.
    public function my_custom_user_columns($columns)
    {
        // Insert custom columns after the username column.
        $new_columns = [];
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ('username' === $key) {
                $new_columns['playa_name'] = __('Playa Name', 'textdomain');
                $new_columns['profile_updated'] = __('Profile Updated', 'textdomain');
                $new_columns['complete'] = __('Profile Complete', 'textdomain');
                $new_columns['activated'] = __('Activated', 'textdomain');
            }
        }
        return $new_columns;
    }


    // Output the content for our custom columns.
    public function my_custom_user_column_content($value, $column_name, $user_id)
    {
        switch ($column_name) {
            case 'playa_name':
                $playa_name = get_user_meta($user_id, 'playa_name', true);
                return $playa_name ? esc_html($playa_name) : '—';

            case 'profile_updated':
                // Example: Check if the user profile is complete.
                $profile_updated = get_user_meta($user_id, 'profile_updated', true);
                if ($profile_updated) {
                    $time_diff = human_time_diff(strtotime($profile_updated), current_time('timestamp'));
                    return sprintf(__('%s ago', 'textdomain'), $time_diff);
                } else {
                    return '—';
                }

            case 'complete':
                // Example: Check if the user confirmed their account.
                $confirmed = $this->profileComplete($user_id);
                if ($confirmed) {
                    return '<span style="color: green;">&#10004;</span>'; // Green checkmark
                } else {
                    return '<span style="color: red;">&#10008;</span>'; // Red X
                }
            
            case 'activated':
                // Example: Check if the user confirmed their account.
                $user = get_userdata($user_id);
                if ($user && $user->user_activation_key == '') {
                    return '<span style="color: green;">&#10004;</span>'; // Green checkmark
                } else {
                    return '<span style="color: red;">&#10008;</span>'; // Red X
                }
                $activated = get_user_meta($user_id, 'user_activation_key', true);
                if (empty($activated)) {
                    return '<span style="color: green;">&#10004;</span>'; // Green checkmark
                } else {
                    return '<span style="color: red;">&#10008;</span>'; // Red X
                }
        }
        return $value;
    }

    // Make the Playa Name and Profile Updated columns sortable.
    public function my_sortable_user_columns($columns)
    {
        $columns['playa_name'] = 'playa_name';
        $columns['profile_updated'] = 'profile_updated';
        return $columns;
    }

    // Adjust the query to sort by our custom meta keys.
    public function my_users_orderby($query)
    {
        $orderby = $query->get('orderby');
        if ('playa_name' === $orderby) {
            $query->set('meta_key', 'playa_name');
            $query->set('orderby', 'meta_value');
        }
        if ('profile_updated' === $orderby) {
            $query->set('meta_key', 'profile_updated');
            // If profile_updated is stored as a numeric value, you might use meta_value_num:
            // $query->set('orderby', 'meta_value_num');
            $query->set('orderby', 'meta_value');
        }
    }


    public function hide_admin_bar_for_non_privileged_users()
    {
        if (
            !current_user_can('administrator') &&
            !current_user_can('editor') &&
            !current_user_can('author') &&
            !current_user_can('contributor')
        ) {
            show_admin_bar(false);
        }
    }


    public function prevent_gravity_entry_save($entry_id, $form)
    {
        if ($form['id'] == 6) {
            return null; // Prevents the entry from being saved
        }
        return $entry_id;
    }

    public function mycodelic_redirect_incomplete_profile()
    {
        if (!is_user_logged_in()) {
            return; // Only logged-in users need a profile.
        }

        // Site admins manage the site; never lock them out of the front end.
        if (current_user_can('manage_options')) {
            return;
        }

        // Prevent redirect if already on the profile edit page, or on YOUR OWN
        // member profile view at /profile/{your-nicename}/ (a native author
        // archive under the hood, so is_author() is what flags it — not
        // is_page()). Someone else's /profile/{nicename}/ still counts as
        // "the logged in portion of the site" and stays gated, otherwise an
        // incomplete profile could browse forever by visiting other members'
        // pages instead of finishing their own.
        if (is_page('profile')) {
            return;
        }
        if (is_author()) {
            $viewed = get_queried_object();
            if ($viewed instanceof WP_User && $viewed->ID === get_current_user_id()) {
                return;
            }
        }

        // Check if the user has completed their profile
        if (!$this->profileComplete()) {
            $this->messages->set_message("You must complete your profile before you can access the logged in portion of the site.", 'error');
            wp_redirect(home_url('/profile/'));
            exit;
        }
    }

    public function profileComplete($user_id = NULL)
    {
        if ($user_id == NULL) {
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

        $country = get_user_meta($user_id, 'country', true);
        $is_us   = in_array($country, ['United States', 'US', 'USA'], true);

        // Validate phone number. US numbers must be 10 digits; other countries just
        // need something that looks like a phone number.
        $phone = get_user_meta($user_id, 'user_phone', true);
        if ($is_us) {
            if (!preg_match('/^\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}$/', $phone)) {
                return false; // Invalid US phone format
            }
        } elseif (!preg_match('/^\+?[\d\s().-]{7,20}$/', $phone)) {
            return false; // Invalid phone format
        }

        // Validate postal code. US ZIP codes are 5 or 5+4 digits; other countries
        // (e.g. Canada "T3A 6E2", UK "SW1A 1AA") accept letters and spaces.
        $zip = get_user_meta($user_id, 'zip', true);
        if ($is_us) {
            if (!preg_match('/^\d{5}(-\d{4})?$/', $zip)) {
                return false; // Invalid ZIP format
            }
        } elseif (!preg_match('/^[A-Za-z0-9][A-Za-z0-9 -]{1,10}$/', trim($zip))) {
            return false; // Invalid postal code format
        }

        // Members who have attended Burning Man must say which years.
        $has_attended = get_user_meta($user_id, 'has_attended_burning_man', true) === 'Yes';
        $years_attended = json_decode(get_user_meta($user_id, 'years_attended', true), true);

        if ($has_attended) {
            // If user has attended, they must have at least one year selected
            if (empty($years_attended) || !is_array($years_attended)) {
                return false;
            }
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
                    '2026' => __('2026', 'textdomain'),
                    '2025' => __('2025', 'textdomain'),
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

    public function enqueue_admin_phone_mask($hook)
    {
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
    public function get_profile($user_id = NULL)
    {
        if ($user_id == NULL) {
            $user_id = get_current_user_id();
            if (is_wp_error($user_id)) {
                throw new \Exception('Error getting current user ID.');
            }
        }

        if (is_wp_error($user_id) || $user_id == 0) {
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
        <hr />
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
                                        <input type="checkbox" name="<?php echo esc_attr($key); ?>[]"
                                            value="<?php echo esc_attr($option_value); ?>" <?php if (is_array($value) && in_array($option_value, $value))
                                                   echo 'checked="checked"'; ?> />
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
                            <textarea name="<?php echo esc_attr($key); ?>" id="<?php echo esc_attr($key); ?>" rows="5"
                                cols="30"><?php echo esc_textarea($value); ?></textarea>
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
        <hr />
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                // Listen for changes on the "has_attended_burning_man" radio buttons
                $('input[name="has_attended_burning_man"]').on('change', function () {
                    if ($('input[name="has_attended_burning_man"]:checked').val() == 'Yes') {
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

    /**
     * Flat "Roles" taxonomy on users — loosely Holacracy-inspired (a role someone
     * fills in the org, e.g. "Roster Steward"), distinct from WP capability roles.
     * No circles/purpose/accountabilities yet — just named roles.
     */
    public function register_roles_taxonomy()
    {
        register_taxonomy('mycodelic_role', ['user'], [
            'labels' => [
                'name'          => __('Roles', 'textdomain'),
                'singular_name' => __('Role', 'textdomain'),
                'menu_name'     => __('Roles', 'textdomain'),
                'all_items'     => __('All Roles', 'textdomain'),
                'edit_item'     => __('Edit Role', 'textdomain'),
                'add_new_item'  => __('Add New Role', 'textdomain'),
                'search_items'  => __('Search Roles', 'textdomain'),
            ],
            'public'            => false,
            'hierarchical'      => false,
            'show_ui'           => true,
            'show_in_menu'      => 'users.php',
            'show_admin_column' => false,
            'show_in_rest'      => false,
        ]);
    }

    /**
     * Admin-side: checklist of existing role terms on the user-edit screen.
     */
    public function show_roles_field($user)
    {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }

        $all_roles = get_terms(['taxonomy' => 'mycodelic_role', 'hide_empty' => false]);
        $assigned = wp_get_object_terms($user->ID, 'mycodelic_role', ['fields' => 'ids']);
        $manage_url = admin_url('edit-tags.php?taxonomy=mycodelic_role');
        ?>
        <h3><?php esc_html_e('Roles', 'textdomain'); ?></h3>
        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Roles', 'textdomain'); ?></th>
                <td>
                    <?php if (empty($all_roles) || is_wp_error($all_roles)) : ?>
                        <p>
                            <?php esc_html_e('No roles defined yet.', 'textdomain'); ?>
                            <a href="<?php echo esc_url($manage_url); ?>"><?php esc_html_e('Add one', 'textdomain'); ?></a>
                        </p>
                    <?php else : ?>
                        <?php foreach ($all_roles as $role) : ?>
                            <label style="display:block;">
                                <input type="checkbox" name="mycodelic_roles[]" value="<?php echo esc_attr($role->term_id); ?>"
                                    <?php checked(is_array($assigned) && in_array($role->term_id, $assigned, true)); ?> />
                                <?php echo esc_html($role->name); ?>
                            </label>
                        <?php endforeach; ?>
                        <p><a href="<?php echo esc_url($manage_url); ?>"><?php esc_html_e('Manage roles', 'textdomain'); ?></a></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }

    public function save_roles_field($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            return;
        }

        $term_ids = isset($_POST['mycodelic_roles']) ? array_map('intval', (array) $_POST['mycodelic_roles']) : [];
        wp_set_object_terms($user_id, $term_ids, 'mycodelic_role', false);
    }

    public function mycodelic_add_rewrite_rules()
    {
        // /profile/ (bare) is a real WP Page (slug "profile") — WordPress's own
        // default page rewrite rules already route it correctly with no custom
        // rule needed. (A previous custom rule here mapped it to an unregistered
        // "profile_page" query var, which WordPress silently drops, resulting in
        // an effectively empty query that redirected to the homepage — removed.)

        // /profile/{user_nicename}/ — the SAME query WordPress uses natively for
        // /author/{nicename}/, so is_author()/get_queried_object() and the
        // author.html template (child theme) all just work, unmodified. Mirrors
        // WP core's own author-archive rewrite rules, including pagination and
        // feeds, since the Recent Posts query loop paginates past 10 posts and
        // without these WP still generates /profile/{nicename}/page/2/-style
        // links that would otherwise 404.
        add_rewrite_rule('^profile/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?author_name=$matches[1]&feed=$matches[2]', 'top');
        add_rewrite_rule('^profile/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?author_name=$matches[1]&feed=$matches[2]', 'top');
        add_rewrite_rule('^profile/([^/]+)/page/?([0-9]{1,})/?$', 'index.php?author_name=$matches[1]&paged=$matches[2]', 'top');
        add_rewrite_rule('^profile/([^/]+)/?$', 'index.php?author_name=$matches[1]', 'top');
    }

    /**
     * Members-only: anyone not logged in is sent to log in first, then back here.
     */
    public function require_login_for_author_archive()
    {
        if (!is_author() || is_user_logged_in()) {
            return;
        }
        wp_safe_redirect(wp_login_url(home_url($_SERVER['REQUEST_URI'] ?? '/profile/')));
        exit;
    }

    /**
     * WordPress's default author archive (/author/{nicename}/, ?author={id}) is
     * public by default. Send it to the members-only /profile/ URL instead —
     * but /profile/{nicename}/ resolves to that SAME is_author() query, so only
     * redirect requests that didn't already come in through /profile/ (otherwise
     * this loops).
     */
    public function redirect_author_archive_to_profile()
    {
        if (!is_author()) {
            return;
        }

        $path = (string) wp_parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
        if (strpos($path, '/profile/') === 0) {
            return;
        }

        $user = get_queried_object();
        if ($user instanceof WP_User) {
            wp_safe_redirect(home_url('/profile/' . $user->user_nicename . '/'), 301);
            exit;
        }
    }

    /**
     * Registers the profile fields as first-class WP user meta (REST-visible),
     * separate from whether they're also exposed to Block Bindings below.
     */
    public function register_profile_meta()
    {
        foreach (['playa_name', 'user_about_me', 'city', 'state', 'country', 'profile_photo_url', 'header_photo_url'] as $key) {
            register_meta('user', $key, [
                'type'         => 'string',
                'single'       => true,
                'show_in_rest' => true,
            ]);
        }
    }

    /**
     * core/cover's "url" attribute only re-renders dynamically when
     * useFeaturedImage is true (WP core's render_block_core_cover() returns
     * the static content unchanged otherwise); for a plain custom image URL
     * like ours, its block.json also declares no "source" for "url", so the
     * static-HTML replacement path can't reach it either. Block Bindings
     * genuinely cannot drive Cover's background image for this case — same
     * situation as the edit-profile-button visibility, so it gets the same
     * fix: a small, targeted render_block filter.
     */
    public function set_profile_hero_background($block_content, $block)
    {
        if (empty($block['attrs']['className']) || strpos($block['attrs']['className'], 'mf-profile-hero') === false) {
            return $block_content;
        }

        if (!is_author()) {
            return $block_content;
        }

        $user = get_queried_object();
        if (!($user instanceof WP_User)) {
            return $block_content;
        }

        $url = get_user_meta($user->ID, 'header_photo_url', true);
        if (!$url) {
            return $block_content;
        }

        $tags = new WP_HTML_Tag_Processor($block_content);
        if (!$tags->next_tag(['class_name' => 'wp-block-cover__background'])) {
            return $block_content;
        }

        $existing_style = (string) $tags->get_attribute('style');
        $tags->set_attribute('style', $existing_style . 'background-image:url(' . esc_url($url) . ');background-size:cover;background-position:center;');

        return $tags->get_updated_html();
    }

    /**
     * A custom Block Bindings source ("Member Profile") for the author.html
     * template — exposes the profile fields that have no native WP block
     * equivalent (playa name, about me, years, location, roles, roster
     * history, avatar, edit-profile link), so ordinary Heading/Paragraph/Image
     * blocks in the Site Editor can bind straight to them. No shortcodes, no
     * PHP inside the template itself.
     */
    public function register_profile_binding_source()
    {
        if (!function_exists('register_block_bindings_source')) {
            return; // WP < 6.5.
        }

        register_block_bindings_source('mycodelic/profile-field', [
            'label'               => __('Member Profile', 'textdomain'),
            'get_value_callback'  => [$this, 'resolve_profile_binding'],
        ]);
    }

    /**
     * Resolves a mycodelic/profile-field binding against whichever member's
     * /profile/{nicename}/ (author archive) is currently being viewed. Only
     * fields a member would reasonably share with other members are exposed
     * here — never address/phone/email.
     */
    public function resolve_profile_binding($source_args)
    {
        $user = get_queried_object();
        if (!($user instanceof WP_User)) {
            return '';
        }

        switch ($source_args['key'] ?? '') {
            case 'display_name':
                return $user->display_name;

            case 'playa_name':
                return get_user_meta($user->ID, 'playa_name', true);

            case 'avatar_url':
                $photo = get_user_meta($user->ID, 'profile_photo_url', true);
                return $photo ?: plugins_url('assets/images/default-avatar.png', MYCO_CORE_PLUGIN_FILE);

            case 'edit_profile_url':
                return home_url('/profile/');

            case 'location':
                $city = get_user_meta($user->ID, 'city', true);
                $state = get_user_meta($user->ID, 'state', true);
                $country = get_user_meta($user->ID, 'country', true);
                if (!$city && !$state) {
                    return '';
                }
                $is_us = in_array($country, ['United States', 'US', 'USA'], true);
                return implode(', ', array_filter([$city, $is_us ? $state : $country]));

            case 'about_me':
                $about_me = get_user_meta($user->ID, 'user_about_me', true);
                if ($about_me) {
                    return $about_me;
                }
                return (get_current_user_id() === $user->ID)
                    ? __('Add a little about yourself from your profile settings.', 'textdomain')
                    : __('No bio yet.', 'textdomain');

            case 'years_summary':
                $years = $this->getProfileYears($user->ID);
                if (empty($years)) {
                    return '';
                }
                $count = count($years);
                /* translators: %d: number of burns attended */
                return sprintf(_n('%d burn', '%d burns', $count, 'textdomain'), $count)
                    . ' — ' . sprintf(__('first burn %s', 'textdomain'), $years[0]);

            case 'years_list':
                $years = array_reverse($this->getProfileYears($user->ID));
                if (!$years) {
                    return __('No years recorded yet.', 'textdomain');
                }
                $pills = '';
                foreach ($years as $y) {
                    $url = home_url('/history/' . $y . '/');
                    $pills .= '<a href="' . esc_url($url) . '" style="' . $this->pillStyle() . 'text-decoration:none;">' . esc_html($y) . '</a>';
                }
                return $pills;

            case 'roles_list':
                $roles = wp_get_object_terms($user->ID, 'mycodelic_role');
                if (empty($roles) || is_wp_error($roles)) {
                    return __('No roles yet.', 'textdomain');
                }
                return implode(', ', wp_list_pluck($roles, 'name'));

            case 'roster_list':
                $contact_id = $this->civicrm->getContactIdForUser($user->ID);
                $rosters = $contact_id ? $this->civicrm->getContactRosterGroups($contact_id) : [];
                if (!$rosters) {
                    return __('No roster history yet.', 'textdomain');
                }
                $pills = '';
                foreach ($rosters as $title) {
                    $year = preg_match('/^\d{4}/', $title, $m) ? $m[0] : null;
                    $url = $year ? home_url('/roster/?roster_year=' . $year) : home_url('/roster/');
                    $pills .= '<a href="' . esc_url($url) . '" style="' . $this->pillStyle() . 'text-decoration:none;">' . esc_html($title) . '</a>';
                }
                return $pills;

            case 'posts_count':
                $count = (int) count_user_posts($user->ID, 'post');
                /* translators: %d: number of posts */
                return sprintf(_n('%d post', '%d posts', $count, 'textdomain'), $count);
        }

        return '';
    }

    /**
     * This user's years_attended as a sorted (ascending) array of strings.
     */
    protected function getProfileYears($user_id)
    {
        $years = json_decode(get_user_meta($user_id, 'years_attended', true), true);
        if (empty($years) || !is_array($years)) {
            return [];
        }
        $years = array_map('strval', $years);
        sort($years);
        return $years;
    }

    /**
     * Inline style for a small pill/badge (years attended, roster history) —
     * no native "badge" block exists, so this stays inline rather than a class.
     */
    protected function pillStyle()
    {
        return 'display:inline-block;background:var(--wp--preset--color--tertiary);color:var(--wp--preset--color--primary);'
            . 'border-radius:6px;padding:4px 10px;margin:0 6px 6px 0;font-weight:600;';
    }

    /**
     * The "Edit profile" button (className mf-edit-profile-button, set on the
     * block in the Site Editor) only makes sense on your OWN profile — hide it
     * everywhere else. There's no native block-level "only show to X"
     * condition, so this is the one place server logic still reaches into the
     * template's rendered output.
     */
    public function maybe_hide_edit_profile_button($block_content, $block)
    {
        if (empty($block['attrs']['className']) || strpos($block['attrs']['className'], 'mf-edit-profile-button') === false) {
            return $block_content;
        }

        if (!is_author()) {
            return $block_content;
        }

        $user = get_queried_object();
        if (!($user instanceof WP_User) || get_current_user_id() !== $user->ID) {
            return '';
        }

        return $block_content;
    }


    // Populate first name
    public function populate_first_name()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'first_name', true) : '';
    }

    // Populate last name
    public function populate_last_name()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'last_name', true) : '';
    }

    // Populate email
    public function populate_user_email()
    {
        $user = wp_get_current_user();
        return $user->user_email;
    }

    // Populate phone
    public function populate_user_phone()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'user_phone', true) : '';
    }

    // Populate street address
    public function populate_address()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'address_1', true) : '';
    }

    // Populate city
    public function populate_city()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'city', true) : '';
    }

    // Populate state
    public function populate_state()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'state', true) : '';
    }

    // Populate zip code
    public function populate_zip()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'zip', true) : '';
    }

    public function populate_country()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'country', true) : '';
    }
    public function populate_playa_name()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'playa_name', true) : '';
    }

    // Populate "About Me" field
    public function populate_user_about_me()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'user_about_me', true) : '';
    }

    public function populate_attended_burning_man()
    {
        $user_id = get_current_user_id();
        return $user_id ? get_user_meta($user_id, 'has_attended_burning_man', true) : '';
    }

    public function populate_years_attended()
    {
        $user_id = get_current_user_id();
        $years_attended = $user_id ? get_user_meta($user_id, 'years_attended', true) : '';
        if (!empty($years_attended)) {
            return json_decode($years_attended);
            // return maybe_unserialize($years_attended); // Use this if saving as serialized array
        }
        return '';
    }

    /**
     * Gravity Forms 3 submits the address field's country as an ISO 3166-1 alpha-2 code
     * ("US"); older versions, this plugin's admin profile fields and all existing user
     * meta use the country name ("United States"). Store the name consistently.
     */
    public function normalize_country($country)
    {
        $country = trim((string) $country);
        if (strlen($country) === 2 && class_exists('GF_Field_Address')) {
            $name = (new \GF_Field_Address())->get_country_name(strtoupper($country));
            if (is_string($name) && $name !== '' && strtoupper($name) !== strtoupper($country)) {
                return $name;
            }
        }
        return $country;
    }

    public function update_user_profile_from_gravity($entry, $form)
    {
        // Get current user ID
        $user_id = get_current_user_id();

        // Ensure user is logged in
        if (!$user_id) {
            return;
        }

        // Map Gravity Forms fields to user meta fields.
        // (years_attended is a multi-checkbox and is handled separately below.)
        $fields = [
            'first_name' => rgar($entry, '16.3'),
            'last_name' => rgar($entry, '16.6'),
            'playa_name' => rgar($entry, '6'),
            'user_phone' => rgar($entry, '5'),
            'address_1' => rgar($entry, '9.1'),
            'address_2' => rgar($entry, '9.2'),
            'city' => rgar($entry, '9.3'),
            'state' => rgar($entry, '9.4'),
            'zip' => rgar($entry, '9.5'),
            'country' => $this->normalize_country(rgar($entry, '9.6')),
            'user_about_me' => rgar($entry, '13'),
            'has_attended_burning_man' => rgar($entry, '19'),
        ];
        $new_email = sanitize_email(rgar($entry, '18'));

        // Check if the user has attended Burning Man
        $attended_burning_man = rgar($entry, '19'); // Radio field: "Yes" or "No"

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

        // Update user meta for other fields. Empty values are saved too, so a member
        // can clear an optional field such as Address Line 2 or Playa Name.
        foreach ($fields as $key => $value) {
            $clean = ($key === 'user_about_me')
                ? sanitize_textarea_field($value)
                : sanitize_text_field($value);
            update_user_meta($user_id, $key, $clean);
        }

        // Keep the registration-time phone key in sync for the duplicate check.
        if (!empty($fields['user_phone'])) {
            update_user_meta($user_id, 'phone', sanitize_text_field($fields['user_phone']));
        }

        // File uploads (fields 22, 23): unlike the fields above, an empty
        // submission here just means "didn't pick a new file this time", not
        // "clear it" — only overwrite when something was actually uploaded.
        $profile_photo = esc_url_raw(rgar($entry, '22'));
        if (!empty($profile_photo)) {
            update_user_meta($user_id, 'profile_photo_url', $profile_photo);
        }
        $header_photo = esc_url_raw(rgar($entry, '23'));
        if (!empty($header_photo)) {
            update_user_meta($user_id, 'header_photo_url', $header_photo);
        }

        update_user_meta($user_id, 'profile_updated', current_time('mysql'));

        // Update the account email if it changed and is not in use by someone else.
        $user = get_userdata($user_id);
        if ($user && $new_email && is_email($new_email) && $new_email !== $user->user_email) {
            $existing = email_exists($new_email);
            if (!$existing || (int) $existing === (int) $user_id) {
                wp_update_user([
                    'ID' => $user_id,
                    'user_email' => $new_email,
                ]);
            } else {
                $this->messages->set_message('Your profile was saved, but that email address is already used by another account, so your email was not changed.', 'warning');
                return;
            }
        }

        $this->messages->set_message('Profile updated successfully!', 'success');
    }

}