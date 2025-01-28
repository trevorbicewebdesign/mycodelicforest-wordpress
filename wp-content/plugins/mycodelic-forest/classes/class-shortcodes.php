<?php

class MycodelicForestShortcodes
{
    public function __construct()
    {

    }

    public function init()
    {
        add_shortcode( 'civi_group_contacts', array( $this, 'my_civicrm_group_shortcode' ) );
    }

    /**
     * Shortcode: [civi_group_contacts group_id="123"]
     *
     * Displays all contacts from the specified group (by group_id).
     */
    public function my_civicrm_group_shortcode( $atts ) {

        // 1. Parse shortcode attributes
        $atts = shortcode_atts( array(
            'group_id' => '', // Example usage: [civi_group_contacts group_id="123"]
        ), $atts, 'civi_group_contacts' );

        // 2. Basic validation
        $group_id = trim( $atts['group_id'] );
        if ( empty( $group_id ) ) {
            return '<p>No group_id specified in shortcode.</p>';
        }

        // 3. Fetch contacts via the CiviCRM API
        //    Example: retrieve contact_id, display_name, primary email, etc.
        try {
            $result = civicrm_api3( 'Contact', 'get', array(
                'sequential' => 1,
                'group'      => $group_id, // filter by group
                'return'     => ['contact_id', 'display_name', 'email'],
            ) );
        } catch ( \CiviCRM_API3_Exception $e ) {
            return '<p>Error fetching contacts: ' . $e->getMessage() . '</p>';
        }

        // 4. Check if contacts were returned
        if ( empty( $result['count'] ) ) {
            return '<p>No contacts found in group ' . esc_html( $group_id ) . '.</p>';
        }

        // 5. Construct output HTML
        $html  = '<div class="civi-group-contacts">';
        $html .= '<h3>Contacts in group ' . esc_html( $group_id ) . '</h3>';

        $html .= '<ul>';
        foreach ( $result['values'] as $contact ) {
            $display_name = isset( $contact['display_name'] ) ? $contact['display_name'] : '(No Name)';
            $email        = isset( $contact['email'] ) ? $contact['email'] : '(No Email)';

            $html .= '<li>';
            $html .= '<strong>' . esc_html( $display_name ) . '</strong>';
            $html .= ' &ndash; ' . esc_html( $email );
            $html .= '</li>';
        }
        $html .= '</ul>';

        $html .= '</div>'; // .civi-group-contacts

        return $html;
    }
}
