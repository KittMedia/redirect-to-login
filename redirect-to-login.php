<?php
/*
Plugin Name: Redirect to Login
Description: Redirect guests to the login page.
Version: 1.2.0
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
	
	/**
	 * Filter whether the current request is ignored.
	 * 
	 * @param	bool	$ignore_request Whether the current request is ignored
	 */
	$ignore_request = (bool) \apply_filters( 'redirect_to_login_ignore_request', false );
	
	if ( $ignore_request ) {
		return;
	}
	
	$ignored_pages = [
		'wp-login.php',
	];
	$ignored_uris = [
		'api',
	];
	
	/**
	 * Filter ignored pages.
	 * 
	 * @param	string[]	$ignored_pages Current list of ignored pages
	 */
	$ignored_pages = (array) \apply_filters( 'redirect_to_login_ignored_pages', $ignored_pages );
	
	/**
	 * Filter ignored URIs.
	 * 
	 * @param	string[]	$ignored_uris Current list of ignored URIs
	 */
	$ignored_uris = (array) \apply_filters( 'redirect_to_login_ignored_uris', $ignored_uris );
	
	if (
		! is_user_logged_in()
		&& ! in_array( $GLOBALS['pagenow'], $ignored_pages, true )
		&& ! redirect_to_login_is_ignored_uri( $ignored_uris )
		&& ( ! redirect_to_login_is_rest() || ! defined( 'NO_REDIRECT_REST_API' ) || ( defined( 'NO_REDIRECT_REST_API' ) && ! NO_REDIRECT_REST_API ) )
	) {
		wp_safe_redirect( wp_login_url( site_url( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}
}

add_action( 'init', 'redirect_to_login' );

/**
 * Check, whether the current request URI contains an ignored URI.
 * 
 * @param	string[]	$ignored_uris List of ignored URIs
 * @return	bool Wether the current request URI contains an ignored URI
 */
function redirect_to_login_is_ignored_uri( array $ignored_uris ): bool {
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return false;
	}
	
	foreach ( $ignored_uris as $uri ) {
		if ( \strpos( $_SERVER['REQUEST_URI'], $uri ) !== false ) {
			return true;
		}
	}
	
	return false;
}

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
	
	$defaults = [
		'host' => '',
		'path' => '',
		'scheme' => '',
	];
	$rest_url = wp_parse_args( wp_parse_url( trailingslashit( rest_url() ) ), $defaults );
	$current_url = wp_parse_args( wp_parse_url( add_query_arg( [] ) ), $defaults );
	
	return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
}
