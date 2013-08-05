<?php
/*

Plugin Name: UltraGlot
Plugin URI: http://ultraglot.com/
Description: For translating stuff. Needs to be network activated!
Author: Ryan Hellyer and that Remkus dude
Version: 1.0
Author URI: http://ultraglot.com/

Copyright (c) 2013 UltraGlot


This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
license.txt file included with this plugin for more information.

*/

define( 'UG_TABLE_NAME', 'a_language_mapping' );

require( 'inc/template-tags.php' );
require( 'inc/class-ultraglot-db.php' );
require( 'inc/class-ultraglot-setup.php' );
require( 'inc/class-ultraglot-admin.php' );

new UltraGlot_Setup;
new UltraGlot_Admin;
new UltraGlot_DB;
