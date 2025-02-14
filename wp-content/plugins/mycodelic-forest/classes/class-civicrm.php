<?php

class MycodelicForestCiviCRM 
{
    public function __construct()
    {
       
    }

    public function init()
    {
      
    }

    /**
     * Retrieves the name of a CiviCRM group based on the provided group ID.
     *
     * @param int $group_id The ID of the CiviCRM group.
     * @return string The name of the CiviCRM group.
     */
    public function getGroupName($group_id)
    {
        /*
        $group_name = civicrm_api3('Group', 'getvalue', array(
            'id' => $group_id,
            'return' => 'title',
        ));
        */

        $group_name = "Mycodelic Forest Camp Roster 2024";

        return $group_name;
    }

    /**
     * Retrieves all contacts from a specified CiviCRM group.
     *
     * @param int $group_id The ID of the group to fetch contacts from.
     * @return array|string An array of contacts if successful, or an error message if an exception occurs or no contacts are found.
     */
    public function getGroupContacts($group_id)
    {

        $returnColumns = ['contact_id', 'display_name', 'email'];
        $contacts = [];
        /*
        try {
            $contacts = civicrm_api3( 'Contact', 'get', [
                'sequential' => 1,
                'group'      => $group_id, // filter by group
                'return'     => $returnColumns,
                'options'    => ['limit' => 0], // no limit
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error fetching contacts: ' . $e->getMessage() );
        }

        // 4. Check if contacts were returned
        if ( empty( $result['count'] ) ) {
            return '<p>No contacts found in group ' . esc_html( $group_id ) . '.</p>';
        }
        */

        $mockedData = [
            [
                'contact_id'   => 1,
                'display_name' => 'John Doe',
                'email'        => 'john.doe@mailinator.com',
            ]
        ];

        return $contacts;

    }

}