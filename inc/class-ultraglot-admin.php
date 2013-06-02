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
		echo 'xxxxxxxxxxxxxxxxxxxxxxxxxxx';
	}
}
