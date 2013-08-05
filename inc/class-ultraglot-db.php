<?php

/**
 * Database setup
 * 
 * @copyright Copyright (c), Ryan Hellyer
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 * @since 1.0
 */
class UltraGlot_DB {
	
	/**
	 * Class constructor
	 */
	public function __construct(){
		$this->create_table();
	}
	
	/*
	 * If we have post ID and blog ID, then work out group ID
	 *
	 * @param int $post_id The post ID
	 * @param int $blog_id The blog ID
	 * @global object $wpdb The WordPress database object
	 */
	public function get_group_id( $post_id, $blog_id ) {
		global $wpdb;
		
		// Sanitise data
		$post_id   = (int) $post_id;
		$blog_id   = (int) $blog_id;
		
		// Process query
		$tablename = UG_TABLE_NAME;
		$query = "SELECT * FROM {$tablename} WHERE post_id = %d AND blog_id = %s";
		$query = $wpdb->prepare( $query, $post_id, $blog_id );
		$result = $wpdb->get_results( $query, OBJECT );
		
		if ( isset( $result[0] ) ) {
			$result = $result[0];
		}
		
		if ( isset( $result->group_id ) ) {
			return $result->group_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Get the post ID for a given group ID and blog ID
	 *
	 * @param int $group_id The group id
	 * @param int $blog_id The blog id
	 * @global object $wpdb The WordPress database object
	 * @return int post ID or bool fail
	 **/
	public function get_post_id( $group_id, $blog_id ) {
		global $wpdb;
		
		// Sanitise data
		$group_id = (int) $group_id;
		$blog_id  = (int) $blog_id;
		
		// Process query
		$tablename = UG_TABLE_NAME;
		$query = "SELECT * FROM {$tablename} WHERE group_id = %d AND blog_id = %s";
		$query = $wpdb->prepare( $query, $group_id, $blog_id );
		$result = $wpdb->get_results( $query, OBJECT );
		if ( isset( $result[0] ) ) {
			$result = $result[0];
		}
		
		// Return the ID if it exists, or reports false
		if ( isset( $result->post_id ) ) {
			return $result->post_id;
		} else {
			return false;
		}
	}
	
	/**
	 * Update the post ID
	 *
	 * @param int $group_id The group id
	 * @param int $post_id The post id
	 * @param int $blog_id The blog id
	 * @global object $wpdb The WordPress database object
	 * @return bool True if the post was updated
	 **/
	public function update_post_id( $group_id, $post_id, $blog_id ) {
		global $wpdb;
		
		// Sanitise data
		$group_id    = (int) $group_id;
		$post_id     = (int) $post_id;
		$blog_id     = (int) $blog_id;
		$old_post_id = (int) $this->get_post_id( $group_id, $blog_id );
		
		// Perform the DB update
		$result = $wpdb->update(
			UG_TABLE_NAME,
			array(
				'post_id'  => $post_id,
			),
			array(
				'group_id' => $group_id,
				'post_id'  => $old_post_id, // Need old post ID to ensure that we edit the original row!
				'blog_id'  => $blog_id,
			)
		);
		
		//Check result
		if ( ! $result )
			return false;
		
		return true;
	}
	
	/**
	 * Creates the table for the plugin logs
	 *
	 * @global array $wpdb The WordPress database global object
	 **/
	public function create_table() {
		global $wpdb;
		
		// Get collation - From /wp-admin/includes/schema.php
		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";
		
		// Create table
		$tablename = UG_TABLE_NAME;
		$sql = "CREATE TABLE {$tablename} (
			translation_id BIGINT(20) NOT NULL AUTO_INCREMENT,
			group_id BIGINT(20) NOT NULL,
			post_id BIGINT(20) NOT NULL,
			blog_id BOOLEAN NOT NULL,

			PRIMARY KEY (translation_id), 
			KEY post_id (post_id),
			KEY group_id (group_id)
		) {$charset_collate};";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}
	
	/**
	 * Update the group ID
	 *
	 * @param int $group_id The group id
	 * @param int $post_id The post id
	 * @param int $blog_id The blog id
	 * @global object $wpdb The WordPress database object
	 * @return bool True if the post was updated
	 **/
	public function update_group_id( $group_id, $post_id, $blog_id ) {
		global $wpdb;
		
		// Sanitise data
		$group_id    = (int) $group_id;
		$post_id     = (int) $post_id;
		$blog_id     = (int) $blog_id;
		
		// Perform the DB update
		$result = $wpdb->update(
			UG_TABLE_NAME,
			array(
				'group_id' => $group_id,
				'post_id'  => $post_id,
				'blog_id'  => $blog_id,
			),
			array(
				'group_id' => $group_id,
				'post_id'  => $post_id,
				'blog_id'  => $blog_id,
			)
		);
		
		//Check result
		if ( ! $result )
			return false;
		
		return true;
	}
	
	/**
	 * Retrieves row info
	 *
	 * @param int $user_id The user id
	 * @param int $post_id The post id
	 * @global array $wpdb The WordPress database global object
	 * @return array of objects
	 **/
	public function get_row_info( $post_id, $blog_id ) {
		global $wpdb;
		
		// Sanitise data
		$post_id   = (int) $post_id;
		$blog_id   = (int) $blog_id;
		
		// Process query
		$tablename = UG_TABLE_NAME;
		$query = "SELECT * FROM {$tablename} WHERE post_id = %d AND blog_id = %s";
		$query = $wpdb->prepare( $query, $post_id, $blog_id );
		$result = $wpdb->get_results( $query, OBJECT );
		
		// Taking only the most recent result (should probably be done via the query if possible)
		$number = count( $result );
		if ( $number > 0 )
			$result = $result[$number-1];
		else
			$result = false;
		
		return $result;
	}
}
