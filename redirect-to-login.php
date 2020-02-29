<?php
/*
Plugin Name: Redirect To Login
Description: Redirect guests to the login page.
Version: 1.0.0
Author: KittMedia
Author URI: https://kittmedia.com
License: GPL2
*/
defined( 'ABSPATH' ) || exit;

/**
 * Redirect to the login page.
 */
function redirect_to_login() {
	if ( wp_doing_cron() || wp_doing_ajax() ) {
		return;
	}
	
	global $wp;
	
	if ( ! is_user_logged_in() && ! in_array( $GLOBALS['pagenow'], [ 'wp-login.php' ], true ) ) {
		wp_safe_redirect( wp_login_url( site_url( $wp->request ) ) );
		exit;
	}
}

add_action( 'init', 'redirect_to_login' );
