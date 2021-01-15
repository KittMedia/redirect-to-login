<?php
/*
Plugin Name: Redirect To Login
Description: Redirect guests to the login page.
Version: 1.0.2
Author: KittMedia
Author URI: https://kittmedia.com
License: GPL2
*/
defined( 'ABSPATH' ) || exit;

/**
 * Redirect to the login page.
 */
function redirect_to_login() {
	if ( wp_doing_cron() || wp_doing_ajax() || defined( 'WP_CLI' ) && WP_CLI ) {
		return;
	}
	
	if ( php_sapi_name() === 'cli' ) {
		return;
	}
	
	global $wp;
	
	if (
		! is_user_logged_in()
		&& ! in_array( $GLOBALS['pagenow'], [ 'lb-check.php', 'wp-login.php' ], true )
		&& (
			! empty( $_SERVER['REQUEST_URI'] )
			&& strpos( $_SERVER['REQUEST_URI'], 'rh-carver' ) === false
		)
	) {
		wp_safe_redirect( wp_login_url( site_url( $wp->request ) ) );
		exit;
	}
}

add_action( 'init', 'redirect_to_login' );
