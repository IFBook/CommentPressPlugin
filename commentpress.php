<?php /*
----------------------------------------------------------------
Plugin Name: Commentpress
Plugin URI: http://www.futureofthebook.org/commentpress/
Description: Commentpress allows readers to comment paragraph by paragraph in the margins of a text. You can use it to annotate, gloss, workshop, debate and more! <strong>For Wordpress Multisite:</strong> do not network activate this plugin. For more information see the plugin docs.
Author: Institute for the Future of the Book
Version: 3.3.4
Author URI: http://www.futureofthebook.org
----------------------------------------------------------------
Special thanks to:
Eddie Tejeda @ www.visudo.com for Commentpress 2.0
The developers of jQuery www.jquery.com
Mark James, for the icon http://www.famfamfam.com/lab/icons/silk/
----------------------------------------------------------------
*/





// ----------------------------------------------------------------
// No need to edit below this line
// ----------------------------------------------------------------

// set version
define( 'CP_VERSION', '3.3.3' );

// store reference to this file
if ( !defined( 'CP_PLUGIN_FILE' ) ) {
	define( 'CP_PLUGIN_FILE', __FILE__ );
}






/*
----------------------------------------------------------------
Begin by establishing Plugin Context
----------------------------------------------------------------
NOTE: force-activated and network-activated contexts are now deprecated
----------------------------------------------------------------
*/

// test for multisite location
if ( basename( dirname(__FILE__) ) == 'mu-plugins' ) { 

	// directory-based forced activation
	if ( !defined( 'CP_PLUGIN_CONTEXT' ) ) {
		define( 'CP_PLUGIN_CONTEXT', 'mu_forced' );
	}
	
// test for multisite
} elseif ( is_multisite() ) {

	// check if our plugin is one of those activated sitewide
	// NOTE: there IS be a better way to do this! See Commentpress for Multisite...

	// make a temp array by splitting path on a known directory name
	$tmp_array = explode( trailingslashit( WP_PLUGIN_DIR ), CP_PLUGIN_FILE );
	
	// get our plugin path relative to WP_PLUGIN_DIR
	$this_plugin = $tmp_array[1];
	
	// init flag
	$flag = false;

	// get sitewide plugins
	$active = (array)get_site_option( 'active_sitewide_plugins' );
	$active = array_keys( $active );

	// loop through them
	foreach( $active AS $plugin_file ) {
	
		// is ours there?
		if ( $plugin_file == $this_plugin ) { $flag = true; }
	
	}

	// is the plugin network activated?
	if ( $flag ) {
	
		// yes, network activated
		if ( !defined( 'CP_PLUGIN_CONTEXT' ) ) {
			define( 'CP_PLUGIN_CONTEXT', 'mu_sitewide' );
		}
		
	} else {

		// optional activation per blog in multisite
		if ( !defined( 'CP_PLUGIN_CONTEXT' ) ) {
			define( 'CP_PLUGIN_CONTEXT', 'mu_optional' );
		}
		
	}

} else {

	// single user install
	if ( !defined( 'CP_PLUGIN_CONTEXT' ) ) {
		define( 'CP_PLUGIN_CONTEXT', 'standard' );
	}
	
}

//print_r( CP_PLUGIN_CONTEXT ); //die();





/*
----------------------------------------------------------------
Init plugin
----------------------------------------------------------------
*/

// do we have our class?
if ( !class_exists( 'CommentPress' ) ) {



	// Sanity check

	// define filename
	$cp_class_file = 'class_commentpress.php';

	// define path to our class file
	$cp_class_file_path = plugin_dir_path( CP_PLUGIN_FILE ) . $cp_class_file;

	// is our class definition present?
	if ( !is_file( $cp_class_file_path ) ) {
	
		// oh no!
		die( 'Class file "'.$cp_class_file.'" is missing from the plugin directory.' );
	
	}
	
	
	
	// Include and init

	// we're fine, include class definition
	require_once( $cp_class_file_path );
	
	// instantiate it
	$commentpress_obj = new CommentPress;
	

	
	/*
	----------------------------------------------------------------
	Critical Plugin Hooks
	----------------------------------------------------------------
	moved these three hooks to the main plugin file for sanity's sake and so we can
	begin to rationalise the activate, deactivate and uninstall processes
	----------------------------------------------------------------
	*/
	
	// activation
	register_activation_hook( CP_PLUGIN_FILE, array( &$commentpress_obj, 'activate' ) );
	
	// deactivation
	register_deactivation_hook( CP_PLUGIN_FILE, array( &$commentpress_obj, 'deactivate' ) );
	
	// uninstall uses the 'uninstall.php' method
	// see: http://codex.wordpress.org/Function_Reference/register_uninstall_hook
	
}





?>