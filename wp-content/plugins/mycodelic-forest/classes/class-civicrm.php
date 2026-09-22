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
     * Retrieves the yearly roster groups ("2024 Camp Roster", "2022 Roster", ...).
     *
     * @return array Group titles keyed by group ID, ordered newest year first.
     * @throws Exception on API error.
     */
    public function getRosterGroups()
    {
        try {
            $result = civicrm_api3('Group', 'get', [
                'title'     => ['LIKE' => '%Roster%'],
                'is_active' => 1,
                'return'    => ['id', 'title'],
                'options'   => ['limit' => 0],
            ]);
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error fetching roster groups: ' . $e->getMessage() );
        }

        // Only titles that start with a year are yearly rosters.
        $groups = [];
        foreach ($result['values'] as $group_id => $group) {
            if (preg_match('/^\d{4}\b/', $group['title'])) {
                $groups[(int) $group_id] = $group['title'];
            }
        }

        // Newest year first: titles start with the year, so a reverse string sort does it.
        arsort($groups, SORT_STRING);

        return $groups;
    }

    /**
     * Retrieves the name of a CiviCRM group based on the provided group ID.
     *
     * @param int $group_id The ID of the CiviCRM group.
     * @return string The name of the CiviCRM group.
     */
    public function getGroupName($group_id)
    {
        try {
            return (string) civicrm_api3('Group', 'getvalue', array(
                'id'     => $group_id,
                'return' => 'title',
            ));
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error fetching group: ' . $e->getMessage() );
        }
    }

    /**
     * Retrieves all contacts from a specified CiviCRM group.
     *
     * @param int $group_id The ID of the group to fetch contacts from.
     * @return array An array of contacts (empty if the group has none).
     * @throws Exception on API error.
     */
    public function getGroupContacts($group_id)
    {
        $returnColumns = ['contact_id', 'display_name', 'email'];

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

        if ( empty( $result['count'] ) || empty( $result['values'] ) ) {
            return [];
        }

        return $result['values'];
    }

    /**
     * @return array The contact record, or an empty array if not found.
     */
    public function getContact($contact_id)
    {
        try {
            $result = civicrm_api3( 'Contact', 'get', [
                'sequential' => 1,
                'id'         => $contact_id,
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error fetching contact: ' . $e->getMessage() );
        }

        return $result['values'][0] ?? [];
    }

    /**
     * @param int   $contact_id
     * @param array $data  Contact fields to update, e.g. ['first_name' => 'Ann'].
     */
    public function updateContact($contact_id, array $data)
    {
        try {
            civicrm_api3( 'Contact', 'create', array_merge( $data, [ 'id' => $contact_id ] ) );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error updating contact: ' . $e->getMessage() );
        }

        return true;
    }

    /**
     * @return int|null The phone record ID, or null if the contact has none.
     */
    public function getContactPhoneId($contact_id)
    {
        try {
            $result = civicrm_api3( 'Phone', 'get', [
                'sequential'       => 1,
                'contact_id'       => $contact_id,
                'location_type_id' => 1,
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error fetching contact phone ID: ' . $e->getMessage() );
        }

        return ! empty( $result['values'][0]['id'] ) ? (int) $result['values'][0]['id'] : null;
    }

    public function updateContactPhone($contact_id, $phone)
    {
        try {
            $params = [
                'contact_id'       => $contact_id,
                'phone'            => $phone,
                'location_type_id' => 1,
            ];
            // Update the existing phone record instead of adding a duplicate.
            $phone_id = $this->getContactPhoneId( $contact_id );
            if ( $phone_id ) {
                $params['id'] = $phone_id;
            }
            civicrm_api3( 'Phone', 'create', $params );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error updating contact phone: ' . $e->getMessage() );
        }

        return true;
    }

    public function updateContactPrimaryAddress($contact_id, $address)
    {
        try {
            civicrm_api3( 'Address', 'create', [
                'contact_id'        => $contact_id,
                'location_type_id'  => 1,
                'street_address'    => $address['street_address'] ?? '',
                'city'              => $address['city'] ?? '',
                'state_province_id' => $address['state_province_id'] ?? null,
                'postal_code'       => $address['postal_code'] ?? '',
                'country_id'        => $address['country_id'] ?? null,
            ] );
        } catch ( \CiviCRM_API3_Exception $e ) {
            throw new Exception( 'Error updating contact address: ' . $e->getMessage() );
        }

        return true;
    }

}
