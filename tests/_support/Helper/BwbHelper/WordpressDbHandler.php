<?php
namespace Helper\BwbHelper;

use \Faker;
use \Exception;

/**
 * Class WordpressDbHandler
 *
 * Helper class for managing sale configurations.
 */
class WordpressDbHandler extends \Codeception\Module {
    
    public function haveInDatabase($table, array $criteria):int {
        global $wpdb;
        $columns = array_keys($criteria);
        $values = array_values($criteria);
        $columnString = implode(", ", $columns);
        $valueString = implode(", ", array_map([$wpdb, 'prepare'], array_fill(0, count($values), '%s'), $values));
        $query = "INSERT INTO test_$table ($columnString) VALUES ($valueString)";
        $wpdb->query($query);

        codecept_debug($query);
        // need to return the index
        return $wpdb->insert_id;
    }

    public function havePostmetaInDatabase($post_id, $meta_key, $meta_value){
        update_metadata('post', $post_id, $meta_key, $meta_value);
       
    }

    public function dontHaveInDatabase($table, array $criteria) {
        codecept_debug("Dont have in database");
        global $wpdb;
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $safe_value = esc_sql($value);
            $conditions[] = "`$key` = '$safe_value'";
        }
        $where = implode(' AND ', $conditions);
        $query = "DELETE FROM test_$table WHERE $where";
        
        codecept_debug($query);
        $wpdb->query("DELETE FROM test_$table WHERE $where");
    }

    public function haveTermInDatabase($term, $taxonomy, $args = []):array {
        // First check that the term doesn't exist
        $existing_term = get_term_by('name', $term, $taxonomy);
        if ($existing_term) {
            $return = [];
            $return[] = $existing_term->term_id;
            $return[] = $existing_term->term_taxonomy_id;
            return $return;
        }
        $term = wp_insert_term($term, $taxonomy, $args);
        codecept_debug($term);

        $return = [];
        $return[] = $term['term_id'];
        $return[] = $term['term_taxonomy_id'];

        return $return;
    }

    public function havePostInDatabase($args):int
    {
        $postId = wp_insert_post($args, true);

        if (is_wp_error($postId)) {
            // Optionally log the error or handle it as required
            error_log('Error inserting post: ' . $postId->get_error_message());
            return $postId;
        }

        return $postId;
    }

    public function grabFromDatabase($table, $column, array $criteria) {
        global $wpdb;
        $query = "SELECT `$column` FROM `test_$table` WHERE ";
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $safe_value = esc_sql($value);
            $conditions[] = "`$key` = '$safe_value'";
        }
        $query .= implode(' AND ', $conditions);
        codecept_debug($query);
        return $wpdb->get_var($query);
    }

    public function grabColumnFromDatabase($table, $column, array $criteria) {
        global $wpdb;
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $safe_value = esc_sql($value);
            $conditions[] = "`$key` = '$safe_value'";
        }
        $where = implode(' AND ', $conditions);
        return $wpdb->get_col("SELECT `$column` FROM test_$table WHERE $where");
    }

    public function updateInDatabase($table, array $data, array $criteria) {
        global $wpdb;
        $wpdb->update("test_$table", $data, $criteria);
    }

    public function haveTermRelationshipInDatabase($object_id, $term_taxonomy_id, $term_order = 0) {
        // First, retrieve the term by taxonomy ID to ensure it exists
        $term = get_term_by('term_taxonomy_id', $term_taxonomy_id, 'project_status');
    
        if ($term && !is_wp_error($term)) {
            // Set the term for the object
            $result = wp_set_object_terms($object_id, $term->term_id, 'project_status', false);  // The false parameter avoids appending the term.
    
            // Check if there was an error
            if (is_wp_error($result)) {
                error_log('Error setting term relationship: ' . $result->get_error_message());
            }
        } else {
            error_log('Term not found with taxonomy ID: ' . $term_taxonomy_id);
        }
    }

    public function seeInDatabase($table, array $criteria) {
        global $wpdb;
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $safe_value = esc_sql($value);
            $conditions[] = "`$key` = '$safe_value'";
        }
        $where = implode(' AND ', $conditions);
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where");
        return $count > 0;
    }

    public function dontSeeInDatabase($table, array $criteria) {
        global $wpdb;
        $conditions = [];
        foreach ($criteria as $key => $value) {
            $safe_value = esc_sql($value);
            $conditions[] = "`$key` = '$safe_value'";
        }
        $where = implode(' AND ', $conditions);
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where");
        return $count == 0;
    }

    public function query($query) {
        global $wpdb;
        return $wpdb->get_results($query);
    }
}
