<?php

/**
 * Database setup
 * 



From Cristi
wp_language_mapping:
    language_group_id int(20) … arbitrary number that groups posts that have the same content, in different languages (like a pointer to a term id, without the term in the db). Not unique.
    language_post_id int(20) … points to a blog post in a certain language
    language_blog_id int(20) … points to the blog where the post is




 * @copyright Copyright (c), Ryan Hellyer
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 * @since 1.0
 */
class UltraGlot_DB {
	
	/**
	 * Class constructor
	 */
	public function __construct(){
//		$this->datetime = current_time( 'mysql' );

		$this->create_table();
//		$this->update_row( 1, 6, 45 );
	}
	
	public function get_translations( $group_id ) {
		global $wpdb;
		
		// Sanitise data
		$group_id   = (int) $group_id;

		// Process query
		$tablename = UG_TABLE_NAME;
		$query = "SELECT * FROM {$tablename} WHERE group_id = %s";
		$query = $wpdb->prepare( $query, $group_id );
		$result = $wpdb->get_results( $query, OBJECT );
		
		return $result;
	}
	
	/*
	 * If we have post ID and blog ID, then work out group ID
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
/*		
		// Taking only the most recent result (should probably be done via the query if possible)
		$number = count( $result );
		if ( $number > 0 )
			$result = $result[$number-1];
		else
			$result = false;
*/
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
	 * @return int post ID
	 **/
	public function get_post_id( $group_id, $blog_id ) {
		/*
		$translations = $this->get_translations( $group_id );
		foreach( $translations as $key => $value ) {
			if ( $blog_id == $value->blog_id ) {
				$post_ID = $value->post_id;
				return $post_ID;
			}
		}
*/


		global $wpdb;
		
		// Sanitise data
		$group_id   = (int) $group_id;
		$blog_id   = (int) $blog_id;
		
		// Process query
		$tablename = UG_TABLE_NAME;
		$query = "SELECT * FROM {$tablename} WHERE group_id = %d AND blog_id = %s";
		$query = $wpdb->prepare( $query, $group_id, $blog_id );
		$result = $wpdb->get_results( $query, OBJECT );
		if ( isset( $result[0] ) ) {
			$result = $result[0];
		}
		
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
	 * Updates a row in the database
	 *
	 * @param int $user_id The user id
	 * @param int $post_id The post id
	 * @param bool $answer True or false (if answer is correct or not)
	 * @return bool false on failure, true if success.
	 **/
	public function update_row( $group_id, $post_id, $blog_id ) {
		global $wpdb;
		
		// Sanitise data
		$group_id  = (int) $group_id;
		$post_id   = (int) $post_id;
		$blog_id   = (int) $blog_id;
		
		// If row doesn't exist, then add it
		$result = $this->get_row_info( $post_id, $blog_id );
		if ( $result == false ) {
			$this->add_row( $group_id, $post_id, $blog_id );
			return;
		}
		
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
//		$tablename = $wpdb->prefix . UG_TABLE_NAME;
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
	
	/**
	 * Inserts a log item into the database
	 *
	 * @param int $user_id The user id
	 * @param int $post_id The post id
	 * @param bool $answer The answer
	 * @global array $wpdb The WordPress database global object
	 * @return bool false on failure, true if success.
	 **/
	private function add_row( $group_id, $post_id, $blog_id ) {
		global $wpdb;
		
		// Sanitise data
		$group_id  = (int) $group_id;
		$post_id   = (int) $post_id;
		$blog_id   = (int) $blog_id;
		
		// Perform the DB insert
		$tablename = UG_TABLE_NAME;
		$result = $wpdb->insert(
			$tablename,
			array(
				'group_id' => $group_id,
				'post_id'  => $post_id,
				'blog_id'  => $blog_id,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s'
			)
		);
		
		//Check result
		if ( ! $result )
			return false;
		$log_id = (int) $wpdb->insert_id;
		
		return true;
	}

}
