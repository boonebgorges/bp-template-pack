<?php
/**
 * BP Template Pack Admin
 *
 * Adds admin page to copy over BP templates and deactivation hooks.
 *
 * @package BP_TPack
 * @subpackage Admin
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * When BPT is deactivated, remove a few options from the DB
 */
function bp_tpack_deactivate() {
	/* Cleanup */
	delete_option( 'bp_tpack_disable_js' );
	delete_option( 'bp_tpack_disable_css' );
	delete_option( 'bp_tpack_configured' );
}
register_deactivation_hook( __FILE__, 'bp_tpack_deactivate' );

/**
 * Adds the BPT admin page under the "Themes" menu.
 */
function bp_tpack_add_theme_menu() {
	add_theme_page( __( 'BP Compatibility', 'bp-template-pack' ), __( 'BP Compatibility', 'bp-template-pack' ), 'switch_themes', 'bp-tpack-options', 'bp_tpack_theme_menu' );
}
add_action( 'admin_menu', 'bp_tpack_add_theme_menu' );

/**
 * Adds an admin notice if BPT hasn't been setup yet.
 */
function bp_tpack_admin_notices() {
	if ( isset( $_GET['page'] ) && 'bp-tpack-options' == $_GET['page'] )
		return;

	if ( !(int)get_option( 'bp_tpack_configured' ) ) {
		?>

		<div id="message" class="updated fade">
			<p>You have activated the BuddyPress Template Pack, but you haven't completed the setup process. Visit the <a href="<?php echo add_query_arg( 'page', 'bp-tpack-options', admin_url( 'themes.php' ) ) ?>">BP Compatibility</a> page to wrap up.</p>
		</div>

		<?php
	}
}
add_action( 'admin_notices', 'bp_tpack_admin_notices' );

/**
 * Output the BPT admin page
 */
function bp_tpack_theme_menu() {
	$theme_dir = WP_CONTENT_DIR . '/themes/' . get_option('stylesheet') . '/';

	if ( !empty( $_GET['finish'] ) )
		update_option( 'bp_tpack_configured', 1 );

	if ( !empty( $_GET['reset'] ) )
		delete_option( 'bp_tpack_configured' );

	if ( !file_exists( $theme_dir . 'activity' ) && !file_exists( $theme_dir . 'blogs' ) && !file_exists( $theme_dir . 'forums' ) && !file_exists( $theme_dir . 'groups' ) && !file_exists( $theme_dir . 'members' ) && !file_exists( $theme_dir . 'registration' ) ) {
		$step = 1;

		if ( !empty( $_GET['move'] ) ) {
			$step = 2;
			$error = false;

			/* Attempt to move the directories */
			if ( !bp_tpack_move_templates() )
				$error = true;
		}

		/* Make sure we reset if template files have been deleted. */
		delete_option( 'bp_tpack_configured' );
	} else
		$step = 3;

	if ( !empty( $_POST['bp_tpack_save'] ) ) {
		/* Save options */
		if ( !empty( $_POST['bp_tpack_disable_css'] ) )
			update_option( 'bp_tpack_disable_css', 1 );
		else
			delete_option( 'bp_tpack_disable_css' );

		if ( !empty( $_POST['bp_tpack_disable_js'] ) )
			update_option( 'bp_tpack_disable_js', 1 );
		else
			delete_option( 'bp_tpack_disable_js' );
	}

	if ( !(int)get_option( 'bp_tpack_configured' ) ) {
?>
	<div class="wrap">
		<h2>Making Your Theme BuddyPress Compatible</h2>

		<p>Adding support for BuddyPress to your existing WordPress theme is a straightforward process. Follow the setup instructions on this page.</p>

		<?php switch( $step ) {
			case 1: ?>

				<h2>Step One: Moving template files automatically</h2>

				<p>BuddyPress needs some extra template files in order to display its pages correctly. This plugin will attempt to automatically move the necessary files into your current theme.</p>

				<p>Click the button below to start the process.</p>

				<p><a class="button" href="?page=bp-tpack-options&move=1">Move Template Files</a></p>

			<?php break; ?>

		<?php case 2: ?>

				<?php if ( $error ) : ?>

					<h2>Step Two: Moving templates manually</h2>

					<p><strong>Moving templates failed.</strong> There was an error when trying to move the templates automatically. This probably means that we don't have the
					correct permissions. That's all right - it just means you'll have to move the template files manually.</p>

					<p>You will need to connect to your WordPress files using FTP. When you are connected browse to the following directory:<p>

					<p><code><?php echo BP_PLUGIN_DIR . '/bp-themes/bp-default/' ?></code></p>

					<p>In this directory you will find six folders (/activity/, /blogs/, /forums/, /groups/, /members/, /registration/). If you want to use all of the features of BuddyPress then you must move these six directories to the following folder:</p>

					<p><code><?php echo $theme_dir ?></code></p>

					<p>If you decide that you don't want to use a feature of BuddyPress, then you can actually ignore the template folders for these features. For example, if you don't want to use the groups and forums features, you can simply avoid copying the /groups/ and /forums/ template folders to your active theme. (If you're not sure what to do, just copy all six folders over to your theme directory.)</p>

					<p>Once you have correctly copied the folders into your active theme, please use the button below to move onto step three.</p>

					<p><a href="?page=bp-tpack-options" class="button">I've finished moving template folders</a></p>

				<?php else : ?>

					<h2>Templates moved successfully!</h2>

					<p>Great news! BuddyPress templates are now in the correct position in your theme, which means that we can skip Step Two: Moving Templates Manually, and move directly to Step Three. Cool!</p>

					<p><a class="button" href="?page=bp-tpack-options">Continue to Step Three</a></p>

				<?php endif; ?>

		<?php break; ?>
		<?php case 3: ?>
			<h2>Step Three: Tweaking your layout</h2>

			<p>Now that the template files are in the correct location, <a href="<?php echo get_bloginfo( 'url' ) ?>" target="_blank">check out your site</a>. (You can come back to the current page at any time, by visiting Dashboard > Appearance > BP Compatibility.) You should see a BuddyPress admin bar at the top of the page. Try visiting some of the links in the "My Account" menu. If everything has gone right up to this point, you should be able to see your BuddyPress content.</p>

			<p>If you find that the pages are not quite aligned correctly, or the content is overlapping the sidebar, you may need to tweak the template HTML. Please follow the "fixing alignment" instructions below. If the content in your pages is aligned to your satisfaction, then you can skip to the "Finishing Up" section at the bottom of this page.</p>

			<h3>Fixing Alignment</h3>

			<p>By default BuddyPress templates use this HTML structure:</p>

<p><pre><code style="display: block; width: 40%; padding-left: 15px;">
[HEADER]

&lt;div id="container"&gt;
	&lt;div id="content"&gt;
		[PAGE CONTENT]
	&lt;/div&gt;

	&lt;div id="sidebar"&gt;
		[SIDEBAR CONTENT]
	&lt;/div&gt;
&lt;/div&gt;

[FOOTER]

</code></pre></p>

			<p>If BuddyPress pages are not aligned correctly, then you may need to modify some of the templates to match your theme's HTML structure. The best way to do this is to access your theme's files, via FTP, at:</p>

			<p><code><?php echo $theme_dir ?></code></p>

			<p>Open up the <code>page.php</code> file (if this does not exist, use <code>index.php</code>). Make note of the HTML template structure of the file, specifically the <code>&lt;div&gt;</code> tags that surround the content and sidebar.</p>

			<p>You will need to change the HTML structure in the BuddyPress templates that you copied into your theme to match the structure in your <code>page.php</code> or <code>index.php</code> file.</p>

			<?php if ( version_compare( BP_VERSION, '1.3' ) > 0 ) : ?>
				<p>In BuddyPress 1.5, the easiest way to do this is to make copies of your theme's <code>header.php</code>, <code>sidebar.php</code> and <code>footer.php</code> and rename them to <code>header-buddypress.php</code>, <code>sidebar-buddypress.php</code>, and <code>footer-buddypress.php</code>.</p>

				<p>Then you can alter the structure of these new template files (<code>header-buddypress.php</code>, <code>sidebar-buddypress.php</code>, and <code>footer-buddypress.php</code>) to resemble your theme's page.php (or index.php).</p>

				<p>The older method consisted of manually modifying the following files:</p>

			<?php else : ?>
				<p>The files that you need to edit are as follows (leave out any folders you have not copied over in step two):</p>
			<?php endif; ?>

			<ul style="list-style: disc; margin-left: 40px;">
				<li><?php echo '/activity/index.php' ?></li>
				<li><?php echo '/blogs/index.php' ?></li>
				<li><?php echo '/forums/index.php' ?></li>
				<li><?php echo '/groups/index.php' ?></li>
				<li><?php echo '/groups/create.php' ?></li>
				<li><?php echo '/groups/single/home.php' ?></li>
				<li><?php echo '/groups/single/plugins.php' ?></li>
				<li><?php echo '/members/index.php' ?></li>
				<li><?php echo '/members/single/home.php' ?></li>
				<li><?php echo '/members/single/plugins.php' ?></li>
				<li><?php echo '/registration/register.php' ?></li>

				<?php if ( is_multisite() ) : ?>
					<li><?php echo '/blogs/create.php' ?></li>
					<li><?php echo '/registration/activate.php' ?></li>
				<?php endif; ?>
			</ul>

			<?php if ( version_compare( BP_VERSION, '1.3' ) > 0 ) : ?>
				<p>So as you can see, modifying these three files (<code>header-buddypress.php</code>, <code>sidebar-buddypress.php</code>, and <code>footer-buddypress.php</code>) instead of 10+ files makes things a lot more easier!</p>
			<?php endif; ?>

			<p>Once you are done matching up the HTML structure of your theme in these template files, please take another look through your site. You should find that BuddyPress pages now fit inside the content structure of your theme.</p>

			<h3>Finishing Up</h3>

			<p>You're now all done with the conversion process. Your WordPress theme will now happily provide BuddyPress compatibility support. Once you hit the finish button you will be presented with a new permanent theme options page, which will allow you to tweak some settings.</p>

			<p><a href="?page=bp-tpack-options&finish=1" class="button-primary">Finish</a></p>
			<p>&nbsp;</p>

		<?php break;?>

		<?php } ?>
	</div>

<?php } else { // The theme steps have been completed, just show the permanent page ?>

	<div class="wrap">

		<h2>BuddyPress Theme Compatibility</h2>

		<?php if ( !empty( $_GET['finish'] ) ) : ?>
			<div id="message">
				<p><strong>Congratulations, you have completed the BuddyPress theme compatibility setup procedure!</strong></p>
			</div>
		<?php endif; ?>

		<form action="" name="bp-tpack-settings" method="post" style="width: 60%; float: left; margin-right: 3%;">

			<p><strong><input type="checkbox" name="bp_tpack_disable_css" value="1"<?php if ( (int)get_option( 'bp_tpack_disable_css' ) ) : ?> checked="checked"<?php endif; ?> /> Disable BP Template Pack CSS</strong></p>
			<p>
				<small style="display: block; margin-left:18px; font-size: 11px">The BuddyPress template pack comes with basic wireframe CSS styles that will format the layout of BuddyPress pages. You can
					extend upon these styles in your theme's CSS file, or simply turn them off and build your own styles.</small>
			</p>

			<p style="margin-top: 20px;"><strong><input type="checkbox" name="bp_tpack_disable_js" value="1"<?php if ( (int)get_option( 'bp_tpack_disable_js' ) ) : ?> checked="checked"<?php endif; ?> /> Disable BP Template Pack JS / AJAX</strong></p>
				<small style="display: block; margin-left:18px; font-size: 11px">The BuddyPress template pack will automatically integrate the BuddyPress default theme javascript and AJAX functionality into your
					theme. You can switch this off, however the experience will be somewhat degraded.</small>

			<p class="submit">
				<input type="submit" name="bp_tpack_save" value="Save Settings" class="button" />
			</p>
		</form>

		<div style="float: left; width: 37%;">

			<?php /* In BP 1.5+, we remove the "BuddyPress is ready" message dynamically */ ?>
			<?php if ( version_compare( BP_VERSION, '1.3' ) <= 0 ) : ?>
				<p style="line-height: 180%; border: 1px solid #eee; background: #fff; padding: 5px 10px;"><strong>NOTE:</strong> To remove the "BuddyPress is ready" message you will need to add a "buddypress" tag to your theme. You can do this by editing the <code>style.css</code> file of your active theme and adding the tag to the "Tags:" line in the comment header.</p>
			<?php endif ?>

			<h4>Navigation Links</h4>

			<p>You may want to add new navigation tabs or links to your theme to link to BuddyPress directory pages. The default set of links are:</p>
				<ul>
					<?php if ( bp_is_active( 'activity' ) ) : ?>
						<li>Activity: <a href="<?php echo get_option('home') . '/' . bp_get_root_slug( BP_ACTIVITY_SLUG ) . '/'; ?>"><?php echo get_option('home') . '/' . BP_ACTIVITY_SLUG . '/'; ?></a></li>
					<?php endif ?>

					<li>Members: <a href="<?php echo get_option('home') . '/' . bp_get_root_slug( BP_MEMBERS_SLUG ) . '/'; ?>"><?php echo get_option('home') . '/' . BP_MEMBERS_SLUG . '/'; ?></a></li>

					<?php if ( bp_is_active( 'groups' ) ) : ?>
						<li>Groups: <a href="<?php echo get_option('home') . '/' . bp_get_root_slug( BP_GROUPS_SLUG ) . '/'; ?>"><?php echo get_option('home') . '/' . BP_GROUPS_SLUG . '/'; ?></a></li>
					<?php endif ?>

					<?php if ( bp_is_active( 'forums' ) ) : ?>
						<li>Forums: <a href="<?php echo get_option('home') . '/' . bp_get_root_slug( BP_FORUMS_SLUG ) . '/'; ?>"><?php echo get_option('home') . '/' . BP_FORUMS_SLUG . '/'; ?></a></li>
					<?php endif ?>

					<li>Register: <a href="<?php echo get_option('home') . '/' . bp_get_root_slug( BP_REGISTER_SLUG ) . '/'; ?>"><?php echo get_option('home') . '/' . BP_REGISTER_SLUG . '/'; ?></a> (registration must be enabled)</li>

					<?php if ( is_multisite() && bp_is_active( 'blogs' ) ) : ?>
						<li>Blogs: <a href="<?php echo get_option('home') . '/' . bp_get_root_slug( BP_BLOGS_SLUG ) . '/'; ?>"><?php echo get_option('home') . '/' . BP_BLOGS_SLUG . '/'; ?></a></li>
					<?php endif; ?>
				</ul>

			<h4>Reset Setup</h4>
			<p>If you would like to run through the setup process again please use the reset button (you will start at step three if you haven't removed the template files):</p>
			<p><a class="button" href="?page=bp-tpack-options&reset=1">Reset</a></p>
		</div>

<?php
	}
}

/**
 * Function to copy over bp-default's main templates to the current WP theme
 *
 * @uses bp_tpack_recurse_copy()
 */
function bp_tpack_move_templates() {
	$destination_dir = WP_CONTENT_DIR . '/themes/' . get_option('stylesheet') . '/';
	$source_dir = BP_PLUGIN_DIR . '/bp-themes/bp-default/';

	$dirs = array( 'activity', 'blogs', 'forums', 'groups', 'members', 'registration' );

	foreach ( (array)$dirs as $dir ) {
		if ( !bp_tpack_recurse_copy( $source_dir . $dir, $destination_dir . $dir ) )
			return false;
	}

	return true;
}

/**
 * Removes the "you'll need to activate a BuddyPress-compatible theme" message from the admin when
 * the plugin is up and running successfully
 *
 * @since 1.3
 */
function bp_tpack_remove_compatibility_message() {
	global $bp;

	// Only works with BP 1.5 or greater
	if ( !empty( $bp->admin->notices ) ) {
		// Check to see whether we've completed the setup
		if ( get_option( 'bp_tpack_configured' ) ) {
			// Remove the message. They're not semantically keyed, so this is a hack
			// Search for the themes.php link, which will work under translations
			foreach( $bp->admin->notices as $key => $notice ) {
				if ( false !== strpos( $notice, 'themes.php' ) ) {
					unset( $bp->admin->notices[$key] );
				}
			}

			// Reset the indexes
			$bp->admin->notices = array_values( $bp->admin->notices );
		}
	}
}
add_action( 'admin_notices', 'bp_tpack_remove_compatibility_message', 2 );

/**
 * Helper function to copy files from one folder over to another
 *
 * @param string $src Location of source directory to copy
 * @param string $dst Location of destination directory where the copied files should reside
 * @see bp_tpack_move_templates()
 */
function bp_tpack_recurse_copy( $src, $dst ) {
	$dir = @opendir( $src );

	if ( !@mkdir( $dst ) )
		return false;

	while ( false !== ( $file = readdir( $dir ) ) ) {
		if ( ( $file != '.' ) && ( $file != '..' ) ) {
			if ( is_dir( $src . '/' . $file ) )
				bp_tpack_recurse_copy( $src . '/' . $file, $dst . '/' . $file );
			else {
				if ( !@copy( $src . '/' . $file, $dst . '/' . $file ) )
					return false;
			}
		}
	}

	@closedir( $dir );

	return true;
}

if ( !function_exists( 'bp_get_root_slug' ) ) :
/**
 * BP 1.2-compatible version of bp_get_root_slug()
 */
function bp_get_root_slug( $slug ) {
	if ( empty ( $slug ) )
		return false;

	return $slug;
}
endif;

?>