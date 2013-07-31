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
		
		// 
		if ( isset( $_GET['post'] ) )
			$post_ID = (int) $_GET['post'];
		else
			$post_ID = '';
		
		$group_id = $this->get_group_id( $post_ID, $blog_id );
		$result = $this->get_translations( $group_id );
		echo '<textarea>';
		print_r( $result );
		echo '</textarea>';
			/*
		*/
		
		$sites = get_site_option( 'ultraglot' );
		foreach( $sites as $site_id => $language ) {
			
			/******** SHOULD BE PULLING FROM LANGUAGE MAPPING TABLE INSTEAD ************/
			$current_post_ID = get_post_meta( $post_ID, 'ultraglot_language_' . $language, true );
			
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
			isset( $_POST['post_ID'] ) &&
			isset( $_POST['ultraglot_submit'] )
		) {
			$post_ID = (int) $_POST['post_ID'];
			
			foreach( $_POST['ultraglot_language'] as $site_id => $lang_post_id ) {
				$site_id = (int) $site_id;
				$lang_post_id = (int) $lang_post_id;
//				$ultraglot_language[$language]
				update_post_meta( $post_ID, 'ultraglot_language[' . $site_id . ']', $lang_post_id ); // Store the data	
			}
		}
		/*
		return;
		echo '<textarea style="width:700px;height:400px;">';
		print_r( $_POST );
		echo '</textarea>';
		echo $blog_id . '<br />';
		$post_id = (int) $_POST['post_ID'];
		$result = $this->get_group_id( $post_id, $blog_id );
		echo '<textarea style="width:700px;height:400px;">';
		print_r( $result );
		echo '</textarea>';
		//$this->update_row( $group_id, $post_id, $blog_id ) {
		*/
	}

}
