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
        
        $group_name = civicrm_api3('Group', 'getvalue', array(
            'id' => $group_id,
            'return' => 'title',
        ));
        

        // $group_name = "Mycodelic Forest Camp Roster 2024";

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
        
        try {
            $result = civicrm_api3( 'Contact', 'get', [
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

        $contacts = $result['values'];

        return $contacts;

    }

    public function getContact($contact_id)
    {
        $contact = [];
        
        try {
            $contact = civicrm_api3( 'Contact', 'get', [
                'sequential' => 1,
                'id'         => $contact_id,
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error fetching contact: ' . $e->getMessage() );
        }
        

        $contact = [
            'contact_id'   => 1,
            'display_name' => 'John Doe',
            'email'        => 'john.doe@mailinator.com',
        ];

        return $contact;
    }


    public function updateContact($contact_id)
    {
        
        try {
            $result = civicrm_api3( 'Contact', 'create', [
                'id' => $contact_id,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error updating contact: ' . $e->getMessage() );
        }
        

        return true;
    }

    public function getContactPhoneId($contact_id)
    {
        $phone_id = 1;
        
        try {
            $result = civicrm_api3( 'Phone', 'get', [
                'sequential' => 1,
                'contact_id' => $contact_id,
                'location_type_id' => 1,
            ] );
            if ( ! empty( $result['values'][0]['id'] ) ) {
                $phone_id = $result['values'][0]['id'];
            }
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error fetching contact phone ID: ' . $e->getMessage() );
        }
        

        return $phone_id;
    }

    public function updateContactPhone($contact_id, $phone)
    {
        
        try {
            $result = civicrm_api3( 'Phone', 'create', [
                'contact_id' => $contact_id,
                'phone' => $phone,
                'location_type_id' => 1,
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error updating contact phone: ' . $e->getMessage() );
        }
        

        return true;
    }

    public function updateContactPrimaryAddress($contact_id, $address)
    {
        
        try {
            $result = civicrm_api3( 'Address', 'create', [
                'contact_id' => $contact_id,
                'location_type_id' => 1,
                'street_address' => $address['street_address'],
                'city' => $address['city'],
                'state_province_id' => $address['state_province_id'],
                'postal_code' => $address['postal_code'],
                'country_id' => $address['country_id'],
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error updating contact address: ' . $e->getMessage() );
        }
        

        return true;
    }

}