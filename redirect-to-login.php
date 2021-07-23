<?php
/*
Plugin Name: Redirect To Login
Description: Redirect guests to the login page.
Version: 1.1.1
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
	
	if (
		! is_user_logged_in()
		&& ! in_array( $GLOBALS['pagenow'], [ 'lb-check.php', 'wp-login.php' ], true )
		&& (
			! empty( $_SERVER['REQUEST_URI'] )
			&& strpos( $_SERVER['REQUEST_URI'], 'rh-carver' ) === false
			&& strpos( $_SERVER['REQUEST_URI'], 'api' ) === false
		)
		&& ( ! redirect_to_login_is_rest() || ! defined( 'NO_REDIRECT_REST_API' ) || ( defined( 'NO_REDIRECT_REST_API' ) && ! NO_REDIRECT_REST_API ) )
	) {
		wp_safe_redirect( wp_login_url( site_url( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}
}

add_action( 'init', 'redirect_to_login' );

/**
 * Check if the current request is a REST request.
 * 
 * @see		https://wordpress.stackexchange.com/a/317041/137048
 * 
 * @return	bool True if current request is a REST request, false otherwise
 */
function redirect_to_login_is_rest() {
	$prefix = rest_get_url_prefix();
	
	if (
		defined( 'REST_REQUEST' ) && REST_REQUEST
		|| isset( $_GET['rest_route'] )
		&& strpos( trim( $_GET['rest_route'], '\\/' ), $prefix ) === 0
	) {
		return true;
	}
	
	global $wp_rewrite;
	
	if ( $wp_rewrite === null ) {
		$wp_rewrite = new WP_Rewrite();
	}
	
	$rest_url = wp_parse_url( trailingslashit( rest_url() ) );
	$current_url = wp_parse_url( add_query_arg( [] ) );
	
	return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
}
