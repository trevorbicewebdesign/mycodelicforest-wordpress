<?php

class MycodelicForestShortcodes
{
    private $MycodelicForestCiviCRM;
    public function __construct(MycodelicForestCiviCRM $MycodelicForestCiviCRM)
    {
        $this->MycodelicForestCiviCRM = $MycodelicForestCiviCRM;
    }

    public function init()
    {
        add_shortcode('civi_group_contacts', array($this, 'my_civicrm_group_shortcode'));
    }

    /**
     * Shortcode: [civi_group_contacts group_id="123"]
     *
     * Displays all contacts from the specified group (by group_id).
     */
    public function my_civicrm_group_shortcode($atts)
    {

        // 1. Parse shortcode attributes
        $atts = shortcode_atts(array(
            'group_id' => '', // Example usage: [civi_group_contacts group_id="123"]
        ), $atts, 'civi_group_contacts');

        // 2. Basic validation
        $group_id = trim($atts['group_id']);
        if (empty($group_id)) {
            return '<p>No group_id specified in shortcode.</p>';
        }

        try {
            $contacts = $this->MycodelicForestCiviCRM->getGroupContacts($group_id);
        } catch (Exception $e) {
            return '<p>Error fetching contacts: ' . $e->getMessage() . '</p>';
        }

        // 5. Construct output HTML
        $html = '<div class="civi-group-contacts">';
        $group_name = $this->MycodelicForestCiviCRM->getGroupName($group_id);
        $html .= '<h3>' . esc_html($group_name) . '</h3>';

        $html .= '<ul>';
        foreach ($contacts['values'] as $contact) {
            $display_name = isset($contact['display_name']) ? $contact['display_name'] : '(No Name)';
            $email = isset($contact['email']) ? $contact['email'] : '(No Email)';

            $html .= '<li>';
            $html .= '<strong>' . esc_html($display_name) . '</strong>';
            $html .= ' &ndash; ' . esc_html($email);
            $html .= '</li>';
        }
        $html .= '</ul>';

        $html .= '</div>'; // .civi-group-contacts

        return $html;
    }
}
