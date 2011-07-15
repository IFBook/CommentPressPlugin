<?php /*
----------------------------------------------------------------
Plugin Name: Commentpress
Plugin URI: http://www.futureofthebook.org/commentpress/
Description:  Commentpress allows readers to comment paragraph by paragraph in the margins of a text. You can use it to annotate, gloss, workshop, debate and more!
Author: Institute for the Future of the Book
Version: 3.2
Author URI: http://www.futureofthebook.org
----------------------------------------------------------------
Special thanks to:
Eddie Tejeda @ www.visudo.com for Commentpress 2.0
Matteo Bicocchi @ www.open-lab.com for mbContainer.js
The developers of jQuery www.jquery.com
Mark James, for the icon http://www.famfamfam.com/lab/icons/silk/
----------------------------------------------------------------
*/

// set flag to activate Commentpress theme in multisite-sitewide context
define( 'CP_ACTIVATE_THEME', true );





// ----------------------------------------------------------------
// No need to edit below this line
// ----------------------------------------------------------------

// set version
define( 'CP_VERSION', '3.2' );

// set testing flag
define( 'CP_PLUGIN_TESTING', true );

// store this file
define( 'CP_PLUGIN_FILE', __FILE__ );

// get directory name of WP_CONTENT_DIR

// make a temp array by splitting the path on a known directory name
$tmp_array = explode( trailingslashit( ABSPATH ), WP_CONTENT_DIR );

// retain only what was appended to ABSPATH
$wp_content_dirname = trailingslashit( $tmp_array[1] );






// ----------------------------------------------------------------
// Begin by establishing Plugin Context
// ----------------------------------------------------------------

// is our class file in the same directory as this file?
if( is_file( dirname(__FILE__) . '/class_commentpress.php' ) ) {

	// set current directory as plugin home directory
	define( 'CP_PLUGIN_ABS_PATH', str_replace( '\\', '/', trailingslashit( dirname(__FILE__) ) ) );
	
	// make a temp array by splitting the path on a known directory name
	$tmp_array = explode( $wp_content_dirname, CP_PLUGIN_ABS_PATH );
	
	// retain only the path following the split to create a relative path
	define( 'CP_PLUGIN_REL_PATH', $wp_content_dirname.$tmp_array[1] );

// is our class file in the plugin subdirectory?
} elseif( is_file( dirname(__FILE__) . '/commentpress/class_commentpress.php' ) ) {

	// set the subdirectory as home
	define( 'CP_PLUGIN_ABS_PATH', str_replace( '\\', '/', dirname(__FILE__) . '/commentpress/' ) );
	
	// make a temp array by splitting the path on a known directory name
	$tmp_array = explode( $wp_content_dirname, CP_PLUGIN_ABS_PATH );
	
	// retain only the path following the split to create a relative path
	define( 'CP_PLUGIN_REL_PATH', $wp_content_dirname.$tmp_array[1] );

} else {

	// alert and start again
	die( 'Commentpress cannot find the necessary files to start. Please check your installation.' );

}



// test for multisite location
if ( basename( dirname(__FILE__) ) == 'mu-plugins' ) { 

	// directory-based forced activation
	define( 'CP_PLUGIN_CONTEXT', 'mu_forced' );

// test for multisite
} elseif ( is_multisite() ) {

	// check if our plugin is one of those activated sitewide
	// NB: there MUST be a better way to do this!

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

	// is the plugin active sitewide?
	if ( $flag ) {
	
		// selected sitewide forced activation
		define( 'CP_PLUGIN_CONTEXT', 'mu_sitewide' );
	
	} else {

		// optional/sitewide activation in multisite
		define( 'CP_PLUGIN_CONTEXT', 'mu_optional' );
	
	}

} else {

	// single user install
	define( 'CP_PLUGIN_CONTEXT', 'standard' );
	
}





// ----------------------------------------------------------------
// Init plugin
// ----------------------------------------------------------------

// do we have our class?
if ( !class_exists( 'CommentPress' ) ) {



	// Sanity check

	// define filename
	$cp_class_file = 'class_commentpress.php';

	// define path to our class file
	$cp_class_file_path = CP_PLUGIN_ABS_PATH . $cp_class_file;

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
	

	
}



/** 
 * @description: utility to add link to settings page
 * @todo: 
 *
 */
function cp_plugin_action_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/commentpress.php' ) ) {
		$links[] = '<a href="options-general.php?page=cp_admin_page">'.__('Settings').'</a>';
	}

	return $links;
}

add_filter( 'plugin_action_links', 'cp_plugin_action_links', 10, 2 );




?>