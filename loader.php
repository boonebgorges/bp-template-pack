<?php
/*
Plugin Name: BuddyPress Template Pack
Plugin URI: http://wordpress.org/extend/plugins/bp-template-pack/
Description: Add support for BuddyPress to your existing WordPress theme. This plugin will guide you through the process step by step.
Author: apeatling, boonebgorges, r-a-y
Version: 1.2.1
Author URI: http://buddypress.org
*/

/**
 * BP Template Pack
 *
 * @package BP_TPack
 * @subpackage Loader
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Initialize the plugin once BuddyPress has initialized.
 *
 * @since 1.1
 */
function bp_tpack_loader() {
	// Check if the current theme supports BuddyPress or if the current theme is using bp-default
	// If so, stop now!
	if ( current_theme_supports( 'buddypress' ) || 'bp-default' == get_option( 'template' ) ) {
		add_action( 'admin_notices', function() {
			echo '<div class="error"><p>' . __( "Hey! It looks like your theme already supports BuddyPress! This means you don't need BP Template Pack. To get rid of this notice, deactivate the BuddyPress Template Pack plugin.", 'bp-tpack' ) . '</p></div>';
		} );
		return;
	}

	if ( is_admin() )
		include( dirname( __FILE__ ) . '/bpt-admin.php' );

	include( dirname( __FILE__ ) . '/bpt-functions.php' );
}
add_action( 'bp_include', 'bp_tpack_loader' );

/**
 * Localize the plugin.
 *
 * @since 1.2
 */
function bp_tpack_load_language() {
	load_plugin_textdomain( 'bp-tpack', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
}
add_action( 'plugins_loaded', 'bp_tpack_load_language' );

?>
