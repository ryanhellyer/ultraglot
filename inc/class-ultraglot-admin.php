<?php

class UltraGlot_Admin {

	public function __construct() {
		if ( is_network_admin() ) {
			add_action( 'network_admin_menu', array( $this, 'add_menu_item' ) );
		}
	}

	public function add_menu_item() {
		if ( is_super_admin() ) {
			add_menu_page(
				__( 'UltraGlot', 'ultra-glot' ),
				__( 'UltraGlot', 'ultra-glot' ),
				'manage_network',
				'ultra-glot',
				array( $this, 'admin_page' ),
				'',
				5
			);
		}
	}

	public function admin_page() {
		
		// Save data
		if ( isset( $_POST['ultraglot'] ) && isset( $_POST['_wpnonce'] ) ) {

			// Do nonce security check
			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'glotpress_nonce' ) )
				wp_die( '<p><strong>Internal UltraGlot error: Failed nonce check</strong></p>' );

			// Cleaning up names and disposing of unneeded post data
			$site_ids = $_POST['site_id'];
			$languages = $_POST['language'];
			$store = array();
			foreach( $site_ids as $key => $site_id ) {
				$site_id = (int) $site_id; // Sanitise site ID
				$language = esc_html( $languages[$key] ); // Sanitise language
				if ( 0 != $site_id ) {
					$store[$site_id] = $language;
				}
			}
			update_site_option( 'ultraglot', $store );

		}
		
		?>
		<style>
			.small-column {
				width: 4em;
			}
		</style>
		<form name="glotpress action="" method="POST">
			<div class="wrap">
				<div id="icon-users" class="icon32">
					<br />
				</div>
				<h2>
					<?php _e( 'UltraGlot sites', 'ultraglot' ); ?>
					<a title="<?php _e( 'Add a new network of sites to the network', 'ultraglot' ); ?>" href="<?php echo admin_url() . 'network/admin.php?page=ultra-glot&add_new=true'; ?>" class="add-new-h2">Add New</a>
				</h2>
				
				<?php if ( isset( $_GET['add_new' ] ) ) {
					echo "<div class='error'><p>The Add new button does not work as expected; it will eventually allow for the addition of other sites within the site (need better terminology to describe this too). I'm currently thinking that the default UI should only allow for one website on the network, but an extension plugin could be added to allow for more. This allows for monetisation (by us) and also simplifies the UI to save confusing newcomers to the plugin. It also allows us to use a simple get_site_option() call rather than using a new database table, or alternatively, warning people that their site will expldoe if they add more than a few hundred 'sites' to it.</p></div>";
				} else {
					?>
				<p>
					Add the site ID and language slug below. This is not the finalised UI, just a test UI to get something operational.
				</p><?php
				}
				?>

				<table class="widefat" cellspacing="0">
					<thead>
						<tr>
							<th scope='col'>
								<span>Site ID</span>
							</th>
							<th scope='col'>
								<span>Language</span>
							</th>
						</tr>
					</thead>
				
					<tfoot>
						<tr>
							<th scope='col'>
								<span>Site ID</span>
							</th>
							<th scope='col'>
								<span>Language</span>
							</th>
						</tr>
					</tfoot>
	
					<tbody><?php

						$sites = get_site_option( 'ultraglot' );
						foreach( $sites as $site_id => $language ) {
							echo $this->network_admin_row( $site_id, $language );
						}
						echo $this->network_admin_row( '', '' );
						?>
					</tbody>
				</table>

				<br />				

				<p class="submit">
					<input name="ultraglot" type="submit" class="button button-primary" />
				</p>
				<?php wp_nonce_field( 'glotpress_nonce' ); ?>
			</div>
		</form><?php
	}

	public function network_admin_row( $site_id, $language ) {
		return '
		<tr>
			<td>
				<input type="text" name="site_id[]" value="' . $site_id . '" />
			</td>
			<td>
				<input type="text" name="language[]" value="' . $language . '" />
			</td>
		</tr>';
	}
}
