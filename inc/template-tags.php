<?php
wpt_get_langs();
/*
 * Get array of possible languages
 *
 * @return array
 */
function wpt_get_langs() {
	$sites = get_site_option( 'ultraglot' );
	$languages = array();
	foreach( $sites as $language ) {
		$languages[] = $language;
	}
	return $languages;
}

/*
 * Get the current pages language
 *
 * @return string
 */
function wpt_get_current_lang() {
	return 'en_US';
}

/*
 * Get HTML select box of languages
 * Automatically redirects to selected page
 *
 * @return array
 */
function wpt_get_select_box() {
	$box = '<select name="lang-selector" id="wpt-lang-selector">';
	foreach( wpt_get_langs() as $value ) {
		if ( $value == wpt_get_current_lang() ) {
			$selected = ' selected="selected"';
		} else {
			$selected = '';
		}
		$box .= '<option' . $selected . ' value="' . $value . '">XXX</option>';
	}
	$box .= '</select>';
	return $box;
}

/*
 * Echo HTML select box of languages
 * Automatically redirects to selected page
 */
function wpt_select_box() {
	echo wpt_get_select_box();
}

/*
 * Get HTML list of languages
 * Automatically redirects to selected page
 *
 * @return array
 */
function wpt_get_list() {
	$box = '';
	foreach( wpt_get_langs() as $value ) {
		$box .= '<li id="language-' . $value . '">XXX</li>';
	}
	return $box;
}

/*
 * Echo HTML list of languages
 * Automatically redirects to selected page
 */
function wpt_list() {
	echo wpt_get_list();
}

