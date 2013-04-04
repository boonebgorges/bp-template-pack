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
	// bp-default check
	if ( 'bp-default' == get_template() ) {
		add_action( 'admin_notices', create_function( '', "
			echo '<div class=\"error\"><p>' . __( \"Hey! It looks like your theme already supports BuddyPress! This means you don't need BP Template Pack. To get rid of this notice, deactivate the BuddyPress Template Pack plugin.\", 'bp-tpack' ) . '</p></div>';
		" ) );

		return;
	}

	$supports_bp = false;

	// on BP 1.7
	if ( class_exists( 'BP_Theme_Compat' ) ) {
		// Bruteforce check for a BP template
		//
		// If current theme doesn't have this template, we should stop BP TPack and
		// tell the user to disable this plugin to use BP's theme compat instead.
		$template_to_check = '/members/members-loop.php';

		// can't use locate_template() as it's too early
		// use get_stylesheet_directory() and get_template_directory() instead.
		//
		// check stylesheet directory first
		if ( file_exists( get_stylesheet_directory() . $template_to_check ) ) {
			$supports_bp = true;

		// check parent template directory if child theme
		} elseif ( ( get_stylesheet() != get_template() ) && file_exists( get_template_directory() . $template_to_check ) ) {
			$supports_bp = true;
		}

		// current theme doesn't support BP and we're on BP 1.7
		// add notice telling the user to disable TPack as it's not needed
		if ( ! $supports_bp ) {
			add_action( 'admin_notices', create_function( '', "
  				echo '<div class=\"error\"><p>' . __( \"Hey! You're using BuddyPress 1.7.  BuddyPress 1.7 adds universal theme compatibility to any WordPress theme.  This means that you don't need this plugin.  Hooray!  To get rid of this notice, deactivate the BuddyPress Template Pack plugin.\", 'bp-tpack' ) . '</p></div>';
			" ) );

			return;
		}
	}

	// on 1.7 and current theme already has some bp-default templates
	if ( $supports_bp ) {
		// current theme explicitly adds add_theme_supports( 'buddypress' )
		//
		// TPack already adds this in bp_tpack_theme_setup()
		// should we add a notice?
		if ( current_theme_supports( 'buddypress' ) ) {
			add_action( 'admin_notices', create_function( '', "
				echo '<div class=\"error\"><p>' . __( \"Hey! It looks like you added the add_theme_support( 'buddypress' ) line to your theme's functions.php.  If your theme relies on BP Template Pack, we already add this line for you.  To get rid of this notice, remove that line from your theme's functions.php.\", 'bp-tpack' ) . '</p>';
  				echo '<p>' . __( \"If your theme does not rely on BP Template Pack, you don't need this plugin.  To get rid of this notice, deactivate the BuddyPress Template Pack plugin.\", 'bp-tpack' ) . '</p>';
				echo '</div>';
			" ) );
		}

	// older versions of BP and current theme explicitly adds
	// add_theme_supports( 'buddypress' )
	} elseif ( current_theme_supports( 'buddypress' ) ) {
		add_action( 'admin_notices', create_function( '', "
			echo '<div class=\"error\"><p>' . __( \"Hey! It looks like your theme already supports BuddyPress! This means you don't need BP Template Pack. To get rid of this notice, deactivate the BuddyPress Template Pack plugin.\", 'bp-tpack' ) . '</p></div>';
		" ) );

		return;
	}

	/** We can load up BP TPack now **/

	// include admin code
	if ( is_admin() ) {
		include( dirname( __FILE__ ) . '/bpt-admin.php' );
	}

	// include additional functions
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
