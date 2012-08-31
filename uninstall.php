<?php /*
===============================================================
CommentPress Uninstaller Version 1.0
===============================================================
AUTHOR			: Christian Wach <needle@haystack.co.uk>
LAST MODIFIED	: 18/07/2012
---------------------------------------------------------------
NOTES
=====
----------------------------------------------------------------
*/



// kick out if uninstall not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit(); }



/** 
 * @description: restore Wordpress database schema
 * @return boolean $result
 * @todo: 
 *
 */
function commentpress_schema_restore() {
	
	// database object
	global $wpdb;
	
	// include Wordpress install helper script
	require_once( ABSPATH . 'wp-admin/install-helper.php' );
	
	// drop the column, if already there
	$result = maybe_drop_column(
	
		$wpdb->comments, 
		'comment_text_signature', 
		"ALTER TABLE `$wpdb->comments` DROP `comment_text_signature`;"
		
	);
	
	// --<
	return $result;
}



// delete options
delete_option( 'cp_version' );
delete_option( 'cp_options' );

// restore database schema
$success = commentpress_schema_restore();

// do we care about the result?



?>