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
//echo '<textarea style="width:400px;height:400px;font-size:10px;">';
//print_r( $_POST );
//echo '</textarea>';
//echo '|'.$group_id.', '.$post_ID.', '.$blog_id . '|';
//echo $group_id;die;
			foreach( $_POST['ultraglot_language'] as $site_id => $lang_post_id ) {
				$site_id = (int) $site_id;
				$lang_post_id = (int) $lang_post_id;
				
				// If group ID already set, then charge ahead
				if ( false != $group_id ) {
					$the_group_id = $group_id;
				}
				// If no group ID set, then need to work out what the group ID is before continuing
				else {
					$group_id = $this->get_group_id( $lang_post_id, $blog_id );
					if ( false != $group_id ) {
						$the_group_id = $group_id;
					} else {
						// Add new group ID to this post
						add_group_id( $post_id, $blog_id )
					}
					echo $the_group_id . 'x';die;
				}
				$this->update_post_id( $the_group_id, $lang_post_id, $site_id );
//echo '|'.$group_id.', '.$lang_post_id.', '.$site_id . '|';
//die;
			}
		}
	}

}
