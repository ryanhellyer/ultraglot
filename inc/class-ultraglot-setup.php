<?php

/**
 * Add Meta Box to "post" post type
 *
 * @copyright Copyright (c), Ryan Hellyer
 * @license http://www.gnu.org/licenses/gpl.html GPL
 * @author Ryan Hellyer <ryanhellyer@gmail.com>
 * @since 1.0
 */
class UltraGlot_Setup extends UltraGlot_DB {
	
	/*
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ) );
		add_action( 'admin_init',     array( $this, 'meta_boxes_save' ) );
	}
	
	/**
	 * Add admin metabox for thumbnail chooser
	 */
	public function add_metabox() {
		add_meta_box(
			'ultraglot', // ID
			'Translations', // Title
			array(
				$this,
				'meta_box', // Callback to method to display HTML
			),
			'post', // Post type
			'side', // Context, choose between 'normal', 'advanced', or 'side'
			'high'  // Position, choose between 'high', 'core', 'default' or 'low'
		);
	}
	
	/**
	 * Output the thumbnail meta box
	 */
	public function meta_box() {
		global $post, $blog_id;
		
		// Set the current pages post ID
		if ( isset( $_GET['post'] ) ) {
			$post_ID = (int) $_GET['post'];
			$group_id = $this->get_group_id( $post_ID, $blog_id );
		}
		
		$sites = get_site_option( 'ultraglot' );
		foreach( $sites as $site_id => $language ) {
			
			if ( isset( $group_id ) ) {
				$current_post_ID = $this->get_post_id( $group_id, $site_id );
			} else {
				$current_post_ID = '';
			}
			if ( wpt_get_current_lang() != $language ) {
				echo '
				<p>
					<label>' . $language . '</label>
					<br />
					<select name="ultraglot_language[' . $site_id . ']">';
					switch_to_blog( $site_id );
					$args = array( 'numberposts' => 10, 'post_type' => 'post' );
					$myposts = get_posts( $args );
					foreach( $myposts as $post ) {
						setup_postdata( $post );
						if ( get_the_ID() == $current_post_ID ) {
							$selected = ' selected="selected"';
						} else {
							$selected = '';
						}
						
						?>
						<option<?php echo $selected; ?> value="<?php the_id(); ?>">
							<?php the_title(); ?>
						</option><?php
					}
					restore_current_blog();
					?></select>
				</p>
				<?php
			}
		}
	}
	
	/**
	 * Save opening times meta box data
	 */
	function meta_boxes_save() {
		global $blog_id;
		
		// Only process if the form has actually been submitted
		if (
			isset( $_POST['_wpnonce'] ) &&
			isset( $_POST['post_ID'] )
		) {
			$post_ID = (int) $_POST['post_ID'];
			$group_id = $this->get_group_id( $post_ID, $blog_id );
			
			// If not group ID present, then we must work out an appropriate one to use
			if ( false == $group_id ) {
				
				// Loop through languages trying to find a group ID to use
				foreach( $_POST['ultraglot_language'] as $site_id => $lang_post_id ) {
					$site_id = (int) $site_id;
					$lang_post_id = (int) $lang_post_id;
					
					$group_id = $this->get_group_id( $lang_post_id, $site_id );
					if ( false != $group_id ) {
						continue;
					}
				}
				
				// If we still don't have a group ID, then all hope is lost and we need to resort to generating a new one
				if ( false == $group_id ) {
					$group_id = $this->get_new_group_id();
				}
				
				// Now that we have a group ID, proceed to update the post ID
				$this->add_group_id( $group_id, $post_ID, $blog_id );
			}
			
			die( 'need to start updating the language posts now');
			
			// Update the posts to be translated ... 
			foreach( $_POST['ultraglot_language'] as $site_id => $lang_post_id ) {
				$site_id = (int) $site_id;
				$lang_post_id = (int) $lang_post_id;
				
				$lang_group_id = $this->get_group_id( $lang_post_id, $site_id );
				if ( $lang_group_id != $group_id ) {
					// TASK ................ Make sure translated post is updated here
					$this->update_group_id( $group_id, $lang_post_id, $site_id );
				}
			}
		}
	}
}
