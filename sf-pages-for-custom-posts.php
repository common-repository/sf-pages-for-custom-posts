<?php
/*
Plugin Name: SF Pages For Custom Posts
Plugin URI: http://scri.in/pfcp
Description: Allows you to easily assign to a page your custom post types with a settings panel, like you can do with normal posts.
Version: 0.5
Author: Grégory Viguier
Author URI: http://www.screenfeed.fr/
License: GPLv3
License URI: http://www.screenfeed.fr/gpl-v3.txt
*/

DEFINE( 'SF_PFCP_PLUGIN_NAME', 'SF Pages For Custom Posts' );
DEFINE( 'SF_PFCP_DOMAIN', 'sf-pfcp' );

/* Just adds a "Settings" link in the plugins list */
function sf_pfcp_settings_action_links( $links, $file ) {
	if ( strstr( __FILE__, $file ) != '' ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=sf_pfcp_config' ) . '">' . __("Settings") . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}

/* Hook for get_header, we set is_page to true */
function sf_pfcp_get_header_hook() {
	global $wp_query;
	$wp_query->is_page = 1;
}

/* Set the request at the very beginning */
function sf_pfcp_parse_request($req) {
	$pfcp = get_option( 'sf_pfcp_options' );

	if ( empty($pfcp) )
		return;

	global $wp_query;
	$page_id = 0;
	$original_queried_object = '';

	if ( '' != $req->query_vars['pagename'] ) {									// Grab page ID with rewrite
		$original_queried_object = get_page_by_path($req->query_vars['pagename']);
		if ( !empty($original_queried_object) )
			$page_id = $original_queried_object->ID;
	}
	elseif ( '' != $req->query_vars['page_id'] ) {								// Grab page ID without rewrite
		$page_id = $req->query_vars['page_id'];
	}
	elseif ( 'page' == get_option('show_on_front') && isset($pfcp[get_option('page_on_front')])) {		// We want a custom post type on the front page but we don't know if we actually are on it
		$page_id = sf_check_if_front_page($req);
	}

	if ( $page_id && isset($pfcp[$page_id]) && isset($pfcp[$page_id]['post_type']) && $pfcp[$page_id]['post_type'] ) {		// If we have a winner!
		// On 'pre_get_posts' we won't have the page ID or its name, so we need to transmit the entire queried object into a custom field
		if ( !empty($original_queried_object) )
			$req->query_vars['original_queried_object'] = $original_queried_object;
		else
			$req->query_vars['original_queried_object'] = get_page($page_id);

		// We hook in get_header if we didn't choose "Page" template and we're not in the front-page : set "is_page" to 1, so we can have the good page title and good current item in menus (we display the template we've chosen, but is_page is true)
		if ( isset($pfcp[$page_id]['template']) && $pfcp[$page_id]['template'] != 'is_page' ) {
			add_action( 'get_header', 'sf_pfcp_get_header_hook' );
		}

		$req->query_vars['page_id']			= 0;								// Used when rewrite is not active
		$req->query_vars['pagename']		= '';								// Used when rewrite is active

		// New query vars
		unset($pfcp[$page_id]['template']);
		foreach($pfcp[$page_id] as $k => $v) {									// post type and posts per page (and other if hooked)
			$req->query_vars[$k]			= $v;
		}

		add_action('pre_get_posts',	'sf_pfcp_set_query');
	}
}

/* Set the template and other stuff */
function sf_pfcp_set_query($q) {
	$pfcp = get_option( 'sf_pfcp_options' );

	$q->queried_object = $q->query_vars['original_queried_object'];
	unset($q->query_vars['original_queried_object']);
	unset($q->query['original_queried_object']);
	$q->queried_object_id = (int) $q->queried_object->ID;						// Useful for current menu item
	$page_id = $q->queried_object_id;

	$q->is_home								= '';

	// New template if needed
	if (isset($pfcp[$page_id]['template']) && $pfcp[$page_id]['template']) {
		$q->$pfcp[$page_id]['template']		= 1;
		if ( 'page' == get_option('show_on_front') && $page_id == get_option('page_for_posts') ) {
			$q->is_posts_page				= 1;
		}
	}
}

/* Check if we're in the front page and return the page ID if so */
function sf_check_if_front_page($req) {
	global $wp_query;
	$query = $wp_query;
	$qv = $req->query_vars;
	$front_page = 0;

	if (empty($qv))																// query_vars is empty : we're in the front page
		$front_page = 1;
	else {
		$query->parse_tax_query( $qv );

		foreach ( $query->tax_query->queries as $tax_query ) {
			if ( 'NOT IN' != $tax_query['operator'] ) {
				$qv['is_tax'] = true;
			}
		}
		unset( $tax_query );

		if ( !empty( $qv['post_type'] ) && ! is_array( $qv['post_type'] ) ) {
			$post_type_obj = get_post_type_object( $qv['post_type'] );
			if ( ! empty( $post_type_obj->has_archive ) )
				$qv['is_post_type_archive'] = true;
		}

		if ( !( $qv['post_type'] || $qv['p'] || $qv['name'] || $qv['static'] || $qv['pagename'] || $qv['page_id'] || $qv['subpost'] || $qv['attachment'] || $qv['subpost_id'] ||
			$qv['attachment_id'] || $qv['is_post_type_archive'] || $qv['second'] || $qv['minute'] || $qv['hour'] || $qv['day'] || $qv['monthnum'] || $qv['year'] || $qv['m'] || $qv['w'] ||
			!(empty($qv['author']) || ($qv['author'] == '0')) ||$qv['author_name'] || $qv['is_tax'] || $qv['s'] || $qv['feed'] || $qv['tb'] ||
			( '404' == $qv['error'] ) || is_admin() || $qv['comments_popup'] || $qv['robots'] ) ) {						// None of these are true, it's front page
			$front_page = 1;
		}
	}
	unset($query);

	if ($front_page)
		return get_option('page_on_front');										// It's front_page, so we return the 'page_on_front' ID
	else return 0;
}

/* Init */
if (is_admin()) {
	require('sf-pfcp-admin.inc.php');											// Admin
	add_action( 'init', 'sf_pfcp_lang_init' );									// Localize
	add_action( 'admin_init', 'sf_pfcp_register_settings' );					// Settings for admin
	add_action( 'admin_menu', 'sf_pfcp_menu' );									// Menu item
	register_uninstall_hook( __FILE__, 'sf_pfcp_uninstaller' );					// Uninstall
	add_filter( 'plugin_action_links', 'sf_pfcp_settings_action_links', 10, 2 );// "Settings" link in plugins list
	if (isset($_GET['page']) && $_GET['page'] == 'sf_pfcp_config') {
		add_action('contextual_help', 'sf_pfcp_contextual_help',10,3);			// Trinity! Help me!
	}
}
else {
	add_action('parse_request',	'sf_pfcp_parse_request');
}

?>