<?php /*
===============================================================
Class CommentPress Version 1.0
===============================================================
AUTHOR			: Christian Wach <needle@haystack.co.uk>
---------------------------------------------------------------
NOTES
=====



---------------------------------------------------------------
*/






/*
===============================================================
Class Name
===============================================================
*/

class CommentPress {






	/*
	===============================================================
	Properties
	===============================================================
	*/
	
	// database object
	var $db;
	
	// display object
	var $display;
	
	// nav object
	var $nav;
	
	// parser object
	var $parser;
	
	// options page
	var $options_page;
	
	// buddypress present
	var $buddypress = false;
	
	// bp-groupblog present
	var $bp_groupblog = false;
	





	/** 
	 * @description: initialises this object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct() {
	
		// init
		$this->_init();

		// --<
		return $this;

	}






	/**
	 * PHP 4 constructor
	 */
	function CommentPress() {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct();
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: if needed, destroys this object
	 * @todo: 
	 *
	 */
	function destroy() {
	
		// nothing

	}







//#################################################################







	/*
	===============================================================
	PUBLIC METHODS
	===============================================================
	*/
	




	/** 
	 * @description: runs when plugin is activated
	 * @param integer $blog_id the ID of the blog - default null
	 * @todo: 
	 *
	 */
	function activate( $blog_id = null ) {
	
		// initialise display - sets the theme
		$this->display->activate( $blog_id );
		
		// initialise database
		$this->db->activate( $blog_id );
		
	}
	
	
	
	
	
	
		
	/** 
	 * @description: runs when plugin is deactivated
	 * @todo: do we want to remove all traces of the plugin?
	 *
	 */
	function deactivate() {
	
		// call database destroy method
		$this->db->deactivate();
		
		// call display destroy method
		$this->display->deactivate();
		
	}
	
	
	
	
	
	
		
	/** 
	 * @description: runs when plugin is uninstalled
	 * @todo: do we want to remove all traces of the plugin?
	 *
	 */
	function uninstall() {
		
		/*
		// call database destroy method
		$this->db->uninstall();
		
		// call display destroy method
		$this->display->uninstall();
		*/
		
	}
	
	
	
	
	
	
		
	/** 
	 * @description: loads translation, if present
	 * @todo: 
	 *
	 */
	function translation() {
		
		// only use, if we have it...
		if( function_exists('load_plugin_textdomain') ) {
	
			// not used, as there are no translations as yet
			load_plugin_textdomain(
			
				// unique name
				'commentpress-plugin', 
				
				// deprecated argument
				false,
				
				// path to directory containing translation files
				plugin_dir_path( CP_PLUGIN_FILE ) . 'languages/'
	
			);
			
		}
		
	}
	
	
	
	
	


	/**
	 * @description: called when BuddyPress is active
	 * @todo: 
	 *
	 */
	function buddypress_init() {
	
		// we've got BuddyPress installed
		$this->buddypress = true;
	
	}
	
	
	
	
	
	
	
	/**
	 * @description: configure when BuddyPress is loaded
	 * @todo: 
	 *
	 */
	function buddypress_globals_loaded() {
	
		// for bp-groupblog integration...
		if ( 
			
			// require multisite
			is_multisite()
			
			// and groups
			AND bp_is_active( 'groups' )
			
			// and bp-groupblog
			AND defined( 'BP_GROUPBLOG_IS_INSTALLED' )
			
		) {
		
			// check if this blog is a group blog...
			$group_id = get_groupblog_group_id( get_current_blog_id() );
			if ( is_numeric( $group_id ) ) {

				// okay, we're properly configured
				$this->bp_groupblog = true;
				
			}
			
		}
	
	}
	
	
	
	
	
	
	
	/**
	 * @description: is BuddyPress active?
	 * @todo: 
	 *
	 */
	function is_buddypress() {
	
		// --<
		return $this->buddypress;
	
	}
	
	
	
	
	
	
	
	/**
	 * @description: is this a BuddyPress Group Blog?
	 * @todo: 
	 *
	 */
	function is_groupblog() {
	
		// --<
		return $this->bp_groupblog;
	
	}
	
	
	
	
	
	
	
	/**
	 * @description: is this a BuddyPress "special page" - a component homepage?
	 * @todo: 
	 *
	 */
	function is_buddypress_special_page() {
		
		// kick out if not BP
		if ( !$this->is_buddypress() ) {
		
			return false;
			
		}
		
		// let's see...
		return !bp_is_blog_page();
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to add link to settings page
	 * @todo: 
	 *
	 */
	function plugin_action_links( $links, $file ) {
	
		if ( $file == CP_PLUGIN_FILE ) {
			$links[] = '<a href="options-general.php?page=cp_admin_page">'.__( 'Settings', 'commentpress-plugin' ).'</a>';
		}
	
		return $links;

	}
	
	
	
	
	
	
	/** 
	 * @description: utility to add a message to admin pages when upgrade required
	 * @todo: 
	 *
	 */
	function admin_upgrade_alert() {

		// sanity check function exists
		if ( function_exists('current_user_can') ) {
	
			// check user permissions
			if ( current_user_can('manage_options') ) {
			
				// show it
				echo '<div id="message" class="error"><p>'.__( 'Commentpress has been updated. Please visit the ' ).'<a href="options-general.php?page=cp_admin_page">'.__( 'Settings Page', 'commentpress-plugin' ).'</a>.</p></div>';
			
			}
			
		}
		
	}
	
	
	
	
	
	
	/** 
	 * @description: appends option to admin menu
	 * @todo: 
	 *
	 */
	function admin_menu() {
		
		// sanity check function exists
		if ( function_exists('current_user_can') ) {
	
			// check user permissions
			if ( current_user_can('manage_options') ) {
		
				// try and update options
				$saved = $this->db->options_update();
				
				// if upgrade required...
				if ( $this->db->check_upgrade() ) {
					
					// access globals
					global $pagenow;
					
					// show on pages other than the CP admin page
					if ( 
					
						$pagenow == 'options-general.php' 
						AND !empty( $_GET['page'] ) 
						AND 'cp_admin_page' == $_GET['page'] 
						
					) {
					
						// we're on our admin page
						
					} else {
					
						// show message
						add_action( 'admin_notices', array( &$this, 'admin_upgrade_alert' ) );
						
					}
					
				}
		
				// insert item in relevant menu
				$this->options_page = add_options_page(
				
					__( 'Commentpress Settings', 'commentpress-plugin' ), 
					__( 'Commentpress', 'commentpress-plugin' ), 
					'manage_options', 
					'cp_admin_page', 
					array( &$this, 'options_page' )
					
				);
				
				//print_r( $this->options_page );die();
				
				// add scripts and styles
				add_action( 'admin_print_scripts-'.$this->options_page, array( &$this, 'admin_js' ) );
				add_action( 'admin_print_styles-'.$this->options_page, array( &$this, 'admin_css' ) );
				add_action( 'admin_head-'.$this->options_page, array( &$this, 'admin_head' ), 50 );
				
			}
			
		}
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: prints plugin options page header
	 * @todo: 
	 *
	 */
	function admin_head() {
		
		// get admin stylesheet
		echo $this->display->get_admin_style();
		
		// get admin javascript
		echo $this->display->get_admin_js();
		
		// there's a new screen object for help in 3.3
		global $wp_version;
		if ( version_compare( $wp_version, '3.2.99999', '>=' ) ) {
		
			$screen = get_current_screen();
			//print_r( $screen ); die();
			
			// use method in this class
			$this->options_help( &$screen );
			
		}
		
		// do we have a custom header bg colour?
		if ( $this->db->option_get_header_bg() != $this->db->header_bg_colour ) {
		
			// echo inline style
			echo '
			
<style type="text/css">
	
	#book_header {
		background: #'.$this->db->option_get_header_bg().';
	}

</style>

';
		
		}
		
	}
	
	
	
	
	
	
	/** 
	 * @description: queue plugin options page css
	 * @todo: 
	 *
	 */
	function admin_css() {
		
		// enqueue farbtastic
		wp_enqueue_style('farbtastic');

	}
	
	
	
	
	
	
	/** 
	 * @description: queue plugin options page javascript
	 * @todo: 
	 *
	 */
	function admin_js() {
		
		// enqueue farbtastic
		wp_enqueue_script('farbtastic');
		
	}
	
	
	
	
	
	
	/** 
	 * @description: prints plugin options page
	 * @todo: 
	 *
	 */
	function options_page() {
	
		// sanity check function exists
		if ( function_exists( 'current_user_can' ) ) {
	
			// check user permissions
			if ( current_user_can( 'manage_options' ) ) {
			
				// get our admin options page
				echo $this->display->get_admin_page();
				
			}
		
		}
		
	}
	
	
	
	
	
	
	/** 
	 * @description: add scripts needed across all WP admin pages
	 * @todo: 
	 *
	 */
	function enqueue_admin_scripts() {
	
		// add quicktag button to page editor
		$this->display->get_custom_quicktags();
		
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds script libraries
	 * @todo: 
	 *
	 */
	function enqueue_scripts() {
		
		// don't include in admin or wp-login.php
		if ( is_admin() OR ( isset( $GLOBALS['pagenow'] ) AND 'wp-login.php' == $GLOBALS['pagenow'] ) ) {
		
			return;
			
		}
		
		// add jQuery libraries
		$this->display->get_jquery();
		
		// if comments are enabled on this post/page
		if ( $this->db->comments_enabled() ) {

			// add tinyMCE scripts
			$this->display->get_tinymce();
			
		}
	
		// add Table of Contents javascript files
		$this->display->get_javascript();
	
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds CSS
	 * @todo: 
	 *
	 */
	function enqueue_styles() {
		
		// add plugin styles
		$this->display->get_frontend_styles();
	
	}
	
	
	
	
		
		
		
	/** 
	 * @description: redirect to child page
	 * @todo: 
	 *
	 */
	function redirect_to_child() {
		
		// do redirect
		$this->nav->redirect_to_child();
		
	}
	
	
	
	
		
		
		
	/** 
	 * @description: inserts plugin-specific header items
	 * @param string $headers
	 * @return string $headers
	 * @todo: 
	 *
	 */
	function head( $headers ) {
		
		// do we have navigation?
		if ( is_single() OR is_page() OR is_attachment() ) {
		
			// initialise nav
			$this->nav->initialise();
			
		}
	
	}
	
	
	
	


	/** 
	 * @description: parses page/post content
	 * @param string $content the content of the page/post
	 * @return string $content
	 * @todo: 
	 *
	 */
	function the_content( $content ) {
	
		// reference our post
		global $post;
		


		// compat with Subscribe to Comments Reloaded
		if( $this->is_subscribe_to_comments_reloaded_page() ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// compat with Theme My Login
		if( $this->is_theme_my_login_page() ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// test for buddypress special page (compat with BP Docs)
		if ( $this->is_buddypress() ) {
			
			// is it a component homepage?
			if ( $this->is_buddypress_special_page() ) {
			
				// --<
				return $content;
				
			}
			
		}


				
		// only parse posts or pages...	
		if( ( is_single() OR is_page() OR is_attachment() ) AND !$this->db->is_special_page() ) {
			
			// delegate to parser
			$content = $this->parser->the_content( $content );
			
		}
		
		

		// --<
		return $content;
	
	}
	
	
	
	
	
	

	/** 
	 * @description: retrieves option for displaying TOC
	 * @return mixed $result
	 * @todo: 
	 *
	 */
	function get_list_option() {
	
		// get list option flag
		$result = $this->db->option_get( 'cp_show_posts_or_pages_in_toc' );
		
		
		
		// --<
		return $result;
	}
	
	
	
	
	
	

	/** 
	 * @description: retrieves minimise all button
	 * @param: string $sidebar type of sidebar (comments, toc, activity)
	 * @return string $result HTML for minimise button
	 * @todo: 
	 *
	 */
	function get_minimise_all_button( $sidebar = 'comments' ) {
	
		// get minimise image
		$result = $this->display->get_minimise_all_button( $sidebar );
	
		// --<
		return $result;
	}
	
	
	
	
	
	

	/** 
	 * @description: retrieves header minimise button
	 * @return string $result HTML for minimise button
	 * @todo: 
	 *
	 */
	function get_header_min_link() {
	
		// get minimise image
		$result = $this->display->get_header_min_link();
	
		// --<
		return $result;
	}
	
	
	
	
	
	

	/** 
	 * @description: retrieves text_signature hidden input
	 * @return string $result HTML input
	 * @todo: 
	 *
	 */
	function get_signature_field() {
	
		// init text signature
		$text_sig = '';
		
		
	
		// get comment ID to reply to from URL query string
		$reply_to_comment_id = isset($_GET['replytocom']) ? (int) $_GET['replytocom'] : 0;
		
		// did we get a comment ID?
		if ( $reply_to_comment_id != 0 ) {
		
			// get paragraph text signature
			$text_sig = $this->db->get_text_signature_by_comment_id( $reply_to_comment_id );
		
		} else {
	
			// do we have a paragraph number in the query string?
			$reply_to_para_id = isset($_GET['replytopara']) ? (int) $_GET['replytopara'] : 0;
			
			// did we get a comment ID?
			if ( $reply_to_para_id != 0 ) {
			
				// get paragraph text signature
				$text_sig = $this->get_text_signature( $reply_to_para_id );
				
			}
		
		}

	
	
		// get list option flag
		$result = $this->display->get_signature_input( $text_sig );
		
		
		
		// --<
		return $result;
	}
	
	
	
	
	
	

	/** 
	 * @description: intercepts media uploads looking for attachments to our title special page
	 * @param integer $attachment_ID the ID of the attachment
	 * @todo: 
	 *
	 */
	function set_book_cover( $attachment_ID ) {
	
		// if the attachment is to our title page, we assume that it
		// is the "book" cover if we're in the CP.org context
	
		/*
		
		// get all image attachments to our title page
		$attachments = get_children(
			
			array(
				
				'post_parent' => $this->db->option_get( 'cp_title_page' ), 
				'post_type' => 'attachment', 
				'post_mime_type' => 'image', 
				'orderby' => 'menu_order'
				
			)
			
		);
		*/

	}
	
	
	
	
	
	

	/** 
	 * @description: add reserved names
	 * @param array $reserved_names the existing list of illegal names
	 * @todo: 
	 *
	 */
	function add_reserved_names( $reserved_names ) {
	
		// get all image attachments to our title page
		$reserved_names = array_merge(
		
			$reserved_names,
			
			array(
				
				'title-page',
				'general-comments',
				'all-comments',
				'comments-by-commenter',
				'table-of-contents',
				'author', // not currently used
				'login', // for Theme My Login
				
			)
			
		);
		
		
		
		// --<
		return $reserved_names;

	}
	
	
	
	
	
	

	/** 
	 * @description: add sidebar to signup form
	 * @todo: 
	 *
	 */
	function after_signup_form() {
		
		// add sidebar
		get_sidebar();

	}
	
	
	
	
	
	

	/** 
	 * @description: adds meta boxes to admin screens
	 * @todo: 
	 *
	 */
	function add_meta_boxes() {
		
		// add our meta boxes to pages
		add_meta_box(
		
			'cp_page_options', 
			__( 'Commentpress Options', 'commentpress-plugin' ), 
			array( &$this, 'custom_box_page' ),
			'page',
			'side'
			
		);

		// add our meta box to posts
		add_meta_box(
		
			'cp_page_options', 
			__( 'Commentpress Options', 'commentpress-plugin' ), 
			array( &$this, 'custom_box_post' ),
			'post',
			'side'
			
		);
		
		// get workflow
		$_workflow = $this->db->option_get( 'cp_blog_workflow' );
		
		// if it's enabled...
		if ( $_workflow == '1' ) {
		
			// init title
			$title = __( 'Workflow', 'commentpress-plugin' );
			
			// allow overrides
			$title = apply_filters( 'cp_workflow_metabox_title', $title );
		
			// add our meta box to posts
			add_meta_box(
			
				'cp_workflow_fields', 
				$title, 
				array( &$this, 'custom_box_workflow' ),
				'post',
				'normal'
				
			);
			
		}
		
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds meta box to page edit screens
	 * @todo: 
	 *
	 */
	function custom_box_page() {
		
		// access post
		global $post;
		


		// Use nonce for verification
		wp_nonce_field( 'cp_page_settings', 'cp_nonce' );
		
		
		
		// --------------------------------------------------------------
		// Show or Hide Page Title
		// --------------------------------------------------------------
		
		// show a title
		echo '<p><strong><label for="cp_title_visibility">' . __( 'Page Title Visibility' , 'commentpress-plugin' ) . '</label></strong></p>';
		
		// set key
		$key = '_cp_title_visibility';
		
		// default to show
		$viz = $this->db->option_get( 'cp_title_visibility' );
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// get it
			$viz = get_post_meta( $post->ID, $key, true );
			
		}
		
		// select
		echo '
<p>
<select id="cp_title_visibility" name="cp_title_visibility">
	<option value="show" '.(($viz == 'show') ? ' selected="selected"' : '').'>'.__('Show page title', 'commentpress-plugin').'</option>
	<option value="hide" '.(($viz == 'hide') ? ' selected="selected"' : '').'>'.__('Hide page title', 'commentpress-plugin').'</option>
</select>
</p>
';

		
		
		// --------------------------------------------------------------
		// Show or Hide Page Meta
		// --------------------------------------------------------------
		
		// show a label
		echo '<p><strong><label for="cp_page_meta_visibility">' . __( 'Page Meta Visibility' , 'commentpress-plugin' ) . '</label></strong></p>';
		
		// set key
		$key = '_cp_page_meta_visibility';
		
		// default to show
		$viz = $this->db->option_get( 'cp_page_meta_visibility' );
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// get it
			$viz = get_post_meta( $post->ID, $key, true );
			
		}
		
		// select
		echo '
<p>
<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
	<option value="show" '.(($viz == 'show') ? ' selected="selected"' : '').'>'.__('Show page meta', 'commentpress-plugin').'</option>
	<option value="hide" '.(($viz == 'hide') ? ' selected="selected"' : '').'>'.__('Hide page meta', 'commentpress-plugin').'</option>
</select>
</p>
';

		
		
		// --------------------------------------------------------------
		// Page Numbering - only shown on first top level page
		// --------------------------------------------------------------
		//print_r( $this->nav->get_first_page() ); die();
		
		// if page has no parent and it's not a special page and it's the first...
		if ( 
		
			$post->post_parent == '0' AND 
			!$this->db->is_special_page() AND 
			$post->ID == $this->nav->get_first_page()
			
		) { // -->
		
			// label
			echo '<p><strong><label for="cp_number_format">' . __('Page Number Format', 'commentpress-plugin' ) . '</label></strong></p>';
			
			// set key
			$key = '_cp_number_format';
			
			// default to arabic
			$format = 'arabic';
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// get it
				$format = get_post_meta( $post->ID, $key, true );
				
			}
			
			//print_r( $format ); die();
			
			// select
			echo '
<p>
<select id="cp_number_format" name="cp_number_format">
	<option value="arabic" '.(($format == 'arabic') ? ' selected="selected"' : '').'>'.__('Arabic numerals', 'commentpress-plugin' ).'</option>
	<option value="roman" '.(($format == 'roman') ? ' selected="selected"' : '').'>'.__('Roman numerals', 'commentpress-plugin' ).'</option>
</select>
</p>
';

		}
		
		
		
		// --------------------------------------------------------------
		// Page Layout for Title Page -> to allow for Book Cover image
		// --------------------------------------------------------------
		
		// is this the title page?
		if ( $post->ID == $this->db->option_get( 'cp_welcome_page' ) ) {
		
			// label
			echo '<p><strong><label for="cp_page_layout">' . __('Page Layout', 'commentpress-plugin' ) . '</label></strong></p>';
			
			// set key
			$key = '_cp_page_layout';
			
			// default to text
			$value = 'text';

			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// get it
				$value = get_post_meta( $post->ID, $key, true );
				
			}
			
			// select
			echo '
<p>
<select id="cp_page_layout" name="cp_page_layout">
	<option value="text" '.(($value == 'text') ? ' selected="selected"' : '').'>'.__('Standard', 'commentpress-plugin' ).'</option>
	<option value="wide" '.(($value == 'wide') ? ' selected="selected"' : '').'>'.__('Wide', 'commentpress-plugin' ).'</option>
</select>
</p>
';

		}
		


		// get post formatter
		$this->_get_post_formatter_metabox( $post );
		


		// get default sidebar
		$this->_get_default_sidebar_metabox( $post );
		


	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds meta box to post edit screens
	 * @todo: 
	 *
	 */
	function custom_box_post() {
		
		// access post
		global $post;
		


		// Use nonce for verification
		wp_nonce_field( 'cp_post_settings', 'cp_nonce' );
		
		
		
		// set key
		$key = '_cp_newer_version';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// get it
			$new_post_id = get_post_meta( $post->ID, $key, true );
			
			// --------------------------------------------------------------
			// Show link to newer post
			// --------------------------------------------------------------
			
			// define label
			$label = __( 'This post already has a new version', 'commentpress-plugin' );
			
			// get the edit post link
			$edit_link = get_edit_post_link( $new_post_id );
			
			// define label
			$link = __( 'Edit new version', 'commentpress-plugin' );
			
			// show link
			echo '
			<p><a href="'.$edit_link.'">'.$link.'</a></p>'."\n";

		} else {
		
			// --------------------------------------------------------------
			// Create new post with content of current post
			// --------------------------------------------------------------
			
			// label
			echo '<p><strong><label for="cp_page_layout">' . __('Versioning', 'commentpress-plugin' ) . '</label></strong></p>';
			
			// define label
			$label = __( 'Create new version', 'commentpress-plugin' );
			
			// show a title
			echo '
			<div class="checkbox">
				<label for="cp_new_post"><input type="checkbox" value="1" id="cp_new_post" name="cp_new_post" /> '.$label.'</label>
			</div>'."\n";
			
		}
		
		
		
		// get post formatter
		$this->_get_post_formatter_metabox( $post );
		


		// get default sidebar
		$this->_get_default_sidebar_metabox( $post );
		


	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds workflow meta box to post edit screens
	 * @todo: 
	 *
	 */
	function custom_box_workflow() {
		
		// we now need to add any workflow that a plugin might want
		do_action( 'cp_workflow_metabox' );
		
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds help copy to admin page in WP <= 3.2
	 * @todo: 
	 *
	 */
	function contextual_help( $text ) {
		
		$text = '';
		$screen = isset( $_GET['page'] ) ? $_GET['page'] : '';
		if ($screen == 'cp_admin_page') {
		
			// get help text
			$text = '<h5>'.__('Commentpress Help', 'commentpress-plugin' ).'</h5>';
			$text .= $this->display->get_help();
			
		}
		
		// --<
		return $text;
	
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds help copy to admin page in WP3.3+
	 * @todo: 
	 *
	 */
	function options_help( $screen ) {
	
		//print_r( $screen ); die();
		
		// is this our screen?
		if ( $screen->id != $this->options_page ) {
		
			// no, kick out
			return;
			
		}
		
		// add a tab
		$screen->add_help_tab( array(
		
			'id'      => 'commentpress-base',
			'title'   => __('Commentpress Help', 'commentpress-plugin'),
			'content' => $this->display->get_help(),
			
		));
		
		// --<
		return $screen;

	}
	
	
	
	
		
		
		
	/** 
	 * @description: stores our additional params
	 * @param integer $post_id the ID of the post (or revision)
	 * @param integer $post the post object
	 * @todo: 
	 *
	 */
	function save_post( $post_id, $post ) {
	
		// we don't use post_id because we're not interested in revisions
		
		// store our meta data
		$result = $this->db->save_meta( $post );
		
	}
	
	
	
	
	
	

	/** 
	 * @description: check for data integrity of other posts when one is deleted
	 * @param integer $post_id the ID of the post (or revision)
	 * @param integer $post the post object
	 * @todo: 
	 *
	 */
	function delete_post( $post_id ) {
	
		// store our meta data
		$result = $this->db->delete_meta( $post_id );
		
	}
	
	
	
	
	
	

	/** 
	 * @description: stores our additional param - the text signature
	 * @param integer $comment_ID the ID of the comment
	 * @param integer $comment_status the status of the comment
	 * @todo: 
	 *
	 */
	function save_comment( $comment_ID, $comment_status ) {
	
		// we don't use comment_status
	
		// store our comment signature
		$result = $this->db->save_comment_signature( $comment_ID );
		
		// in multipage situations, store our comment's page
		$result = $this->db->save_comment_page( $comment_ID );
		
	}
	
	
	
	
	
	

	/** 
	 * @description: get table of contents
	 * @todo: 
	 *
	 */
	function get_toc() {
	
		// switch pages or posts
		if( $this->get_list_option() == 'post' ) {
		
			// list posts
			$this->display->list_posts();
			
		} else {
		
			// list pages
			$this->display->list_pages();
			
		} 
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: get table of contents
	 * @todo: 
	 *
	 */
	function get_toc_list() {
	
		// switch pages or posts
		if( $this->get_list_option() == 'post' ) {
		
			// list posts
			$this->display->list_posts();
			
		} else {
		
			// list pages
			$this->display->list_pages();
			
		} 
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: exclude special pages from page listings
	 * @todo: 
	 *
	 */
	function exclude_special_pages( $excluded_array ) {
	
		//print_r( $excluded_array ); die();
	
		// get special pages array, if it's there
		$special_pages = $this->db->option_get( 'cp_special_pages' );
		
		// do we have an array?
		if ( is_array( $special_pages ) ) {
		
			// merge and make unique
			$excluded_array = array_unique( array_merge( $excluded_array, $special_pages ) );
		
		}
		
		// --<
		return $excluded_array;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: exclude special pages from admin page listings
	 * @todo: 
	 *
	 */
	function exclude_special_pages_from_admin( $query ) {
	
		//print_r( $query ); die();
	
		global $pagenow, $post_type;
		
		// check admin location
		if ( is_admin() AND $pagenow=='edit.php' AND $post_type =='page' ) {
		
			// get special pages array, if it's there
			$special_pages = $this->db->option_get( 'cp_special_pages' );
			
			// do we have an array?
			if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {
			
				// modify query
				$query->query_vars['post__not_in'] = $special_pages;
			
			}

		}
		
	}
	
	
	
	
	
	
	/** 
	 * @description: page counts still need amending
	 * @todo: 
	 *
	 */
	function update_page_counts_in_admin( $vars ) {
	
		//print_r( $vars ); die();
	
		global $pagenow, $post_type;
		
		// check admin location
		if (is_admin() && $pagenow=='edit.php' && $post_type =='page') {
		
			// get special pages array, if it's there
			$special_pages = $this->db->option_get( 'cp_special_pages' );
			
			// do we have an array?
			if ( is_array( $special_pages ) ) {
			
				/*
				Data comes in like this:
				[all] => <a href='edit.php?post_type=page' class="current">All <span class="count">(8)</span></a>
				[publish] => <a href='edit.php?post_status=publish&amp;post_type=page'>Published <span class="count">(8)</span></a>
				*/
				
				// capture existing value enclosed in brackets
				preg_match( '/\((\d+)\)/', $vars['all'], $matches );
				//print_r( $matches ); die();
				
				// did we get a result?
				if ( isset( $matches[1] ) ) {
					
					// subtract special page count
					$new_count = $matches[1] - count( $special_pages );
				
					// rebuild 'all' and 'publish' items
					$vars['all'] = preg_replace( 
					
						'/\(\d+\)/', 
						'('.$new_count.')', 
						$vars['all'] 
						
					);
					
				}
			
				// capture existing value enclosed in brackets
				preg_match( '/\((\d+)\)/', $vars['publish'], $matches );
				//print_r( $matches ); die();
				
				// did we get a result?
				if ( isset( $matches[1] ) ) {
				
					// subtract special page count
					$new_count = $matches[1] - count( $special_pages );
				
					// rebuild 'all' and 'publish' items
					$vars['publish'] = preg_replace( 
					
						'/\(\d+\)/', 
						'('.$new_count.')', 
						$vars['publish'] 
						
					);
					
				}
			
			}
		
		}
		
		return $vars;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: get comments sorted by text signature and paragraph
	 * @param integer $post_ID the ID of the post
	 * @return array $_comments
	 * @todo: 
	 *
	 */
	function get_sorted_comments( $post_ID ) {
	
		// --<
		return $this->parser->get_sorted_comments( $post_ID );
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: get paragraph number for a particular text signature
	 * @param string $text_signature the text signature
	 * @return integer $num position in text signature array
	 * @todo: deal with duplicates
	 *
	 */
	function get_para_num( $text_signature ) {
	
		// get position in array
		$num = array_search( $text_signature, $this->db->get_text_sigs() );
	
		// --<
		return $num + 1;
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: get text signature for a particular paragraph number
	 * @param integer $para_num paragraph number in a post
	 * @return string $text_signature the text signature
	 * @todo: 
	 *
	 */
	function get_text_signature( $para_num ) {
	
		// get text sigs
		$_sigs = $this->db->get_text_sigs();
	
		// get value at that position in array
		$text_sig = ( isset( $_sigs[$para_num-1] ) ) ? $_sigs[$para_num-1] : '';
	
		// --<
		return $text_sig;
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: get a link to a "special" page
	 * @param string $page_type Commentpress name of a special page
	 * @return string $link HTML link to that page
	 * @todo: 
	 *
	 */
	function get_page_link( $page_type = 'cp_all_comments_page' ) {
	
		// init
		$link = '';
		
		

		// get page ID
		$_page_id = $this->db->option_get( $page_type );
		
		// do we have a page?
		if ( $_page_id != '' ) {
		
			// get page
			$_page = get_post( $_page_id );
			
			// get link
			$_url = get_permalink( $_page );
			
			// switch title by type
			switch( $page_type ) {
				
				case 'cp_welcome_page': 
					$_link_title = __( 'Title Page', 'commentpress-plugin' );
					$_button = 'cover'; 
					break;
					
				case 'cp_all_comments_page': 
					$_link_title = __( 'All Comments', 'commentpress-plugin' ); 
					$_button = 'allcomments'; break;
					
				case 'cp_general_comments_page': 
					$_link_title = __( 'General Comments', 'commentpress-plugin' );
					$_button = 'general'; break;
					
				case 'cp_blog_page': 
					$_link_title = __( 'Blog', 'commentpress-plugin' );
					$_button = 'blog'; break;
					
				case 'cp_blog_archive_page': 
					$_link_title = __( 'Blog Archive', 'commentpress-plugin' );
					$_button = 'archive'; break;

				case 'cp_comments_by_page': 
					$_link_title = __( 'Comments by Commenter', 'commentpress-plugin' );
					$_button = 'members'; break;
					
				default: 
					$_link_title = __( 'Members', 'commentpress-plugin' );
					$_button = 'members';
			
			}
			
			// let plugins override titles
			$_title = apply_filters( 'commentpress_page_link_title', $_link_title );
			
			// show link
			$link = '<li><a href="'.$_url.'" id="btn_'.$_button.'" class="css_btn" title="'.$_title.'">'.$_title.'</a></li>'."\n";
		
		}
		
		
		
		// --<
		return $link;
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: get a url for a "special" page
	 * @param string $page_type Commentpress name of a special page
	 * @return string $_url URL of that page
	 * @todo: 
	 *
	 */
	function get_page_url( $page_type = 'cp_all_comments_page' ) {
	
		// init
		$_url = '';
		
		

		// get page ID
		$_page_id = $this->db->option_get( $page_type );
		
		// do we have a page?
		if ( $_page_id != '' ) {
		
			// get page
			$_page = get_post( $_page_id );
			
			// get link
			$_url = get_permalink( $_page );
			
		}
		
		
		
		// --<
		return $_url;
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: get book cover
	 * @todo: 
	 *
	 */
	function get_book_cover() {
		
		// get image SRC
		$src = $this->db->option_get('cp_book_picture');
		
		// get link URL
		$url = $this->db->option_get('cp_book_picture_link');
		


		// --<
		return $this->display->get_linked_image( $src, $url );
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: are paragraph-level comments allowed?
	 * @todo: 
	 *
	 */
	function comments_by_paragraph() {
	
		// --<
		return $this->db->option_get( 'cp_para_comments_enabled' );
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: check if a theme is one of ours or not
	 * @return boolean $is_allowed_theme
	 * @todo: 
	 *
	 */
	function is_allowed_theme() {
	
		// given the variety of names that people give the Commentpress theme directory,
		// I have given up trying to enforce the use of the Commentpress theme or one of
		// it's derivatives. Adding the theme to the allowed_themes array at the top of
		// this file is probably an unnecessary requirement now. People use this at their
		// own risk anyway :-)
		
		// --<
		return true;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: check if we are on the signup page
	 * @return boolean $is_signup
	 * @todo: 
	 *
	 */
	function is_signup_page() {
	
		// init
		$is_signup = false;
		
		
	
		// if multisite
		if ( is_multisite() ) { 
			
			// test script filename
			if ( 'wp-signup.php' == basename($_SERVER['SCRIPT_FILENAME']) ) {
			
				// override
				$is_signup = true;
		
			}
			
		}
	


		// --<
		return $is_signup;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to check for presence of Theme My Login
	 * @return boolean $success
	 * @todo: 
	 *
	 */
	function is_theme_my_login_page() {
		
		// access page
		global $post;
	
		// compat with Theme My Login
		if( 
		
			is_page() AND 
			!$this->db->is_special_page() AND 
			$post->post_name == 'login' AND 
			$post->post_content == '[theme-my-login]'
			
		) {
		
			// --<
			return true;
			
		}
		
		
		
		// --<
		return false;

	}
	
	
	
	
	
	
	

	/** 
	 * @description: utility to check for presence of Subscribe to Comments Reloaded
	 * @return boolean $success
	 * @todo: 
	 *
	 */
	function is_subscribe_to_comments_reloaded_page() {
		
		// access page
		global $post;
	
		// compat with Subscribe to Comments Reloaded
		if( 
		
			is_page() AND 
			!$this->db->is_special_page() AND 
			$post->ID == '9999999' AND 
			$post->guid == get_bloginfo('url').'/?page_id=9999999'
			
		) {
		
			// --<
			return true;
			
		}
		
		
		
		// --<
		return false;

	}
	
	
	
	
	
	
	

	/** 
	 * @description: return the name of the default sidebar
	 * @return array $settings
	 * @todo:
	 */
	function get_default_sidebar() {
	
		// set sensible default
		$return = 'toc';
	


		// is this a commentable page?
		if ( !$this->is_commentable() ) {
		
			// no - we must use either 'activity' or 'toc'
			if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {
				
				// get option (we don't need to look at the page meta in this case)
				$default = $this->db->option_get( 'cp_sidebar_default' );
				
				// use it unless it's 'comments'
				if ( $default != 'comments' ) { $return = $default; }
				
			}
			
			// --<
			return $return;
			
		}
		


		// get CPTs
		//$_types = $this->_get_commentable_cpts();
		
		// testing what we do with CPTs...
		//if ( is_singular() OR is_singular( $_types ) ) {
		
		
		
		// is it a commentable page?
		if ( is_singular() ) {
		
			// some people have reported that db is not an object at this point -
			// though I cannot figure out how this might be occurring - so we
			// avoid the issue by checking if it is
			if ( is_object( $this->db ) ) {
		
				// is it a special page which have comments in page (or are not commentable)?
				if ( !$this->db->is_special_page() ) {
				
					// access page
					global $post;
				
					// is it our title page?
					if ( $post->ID == $this->db->option_get( 'cp_welcome_page' ) ) {
					
						// use 'toc', but should this be a special case?
						return 'toc';
					
					} else {
					
						// either 'comments', 'activity' or 'toc'
						if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {
							
							// get global option
							$return = $this->db->option_get( 'cp_sidebar_default' );
							
							// check if the post/page has a meta value
							$key = '_cp_sidebar_default';
							
							// if the custom field already has a value...
							if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
							
								// get it
								$return = get_post_meta( $post->ID, $key, true );
								
							}
							
							
						}
						
						// --<
						return $return;
					
					}
					
				}
				
			}
		
		}
		

		
		// not singular... must be either activity or toc
		if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {
			
			// override
			$default = $this->db->option_get( 'cp_sidebar_default' );
			
			// use it unless it's 'comments'
			if ( $default != 'comments' ) { $return = $default; }
			
		}
		
		
		
		// --<
		return $return;
		
	}
	
	
	
	
	
	

	/** 
	 * @description: get the order of the sidebars
	 * @return array sidebars in order of display
	 * @todo:
	 */
	function get_sidebar_order() {
		
		// set default but allow overrides
		$order = apply_filters( 
			
			// hook name
			'cp_sidebar_tab_order', 
			
			// default order
			array( 'comments', 'activity', 'contents' ) 
			
		);
		
		// --<
		return $order;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: check if a page/post can be commented on
	 * @return boolean true if commentable, false otherwise
	 * @todo:
	 */
	function is_commentable() {
	
		// declare access to globals
		global $post;
	
		
		
		// not if we're not on a page/post and especially not if there's no post object
		if ( !is_singular() OR !is_object( $post ) ) { return false; }
		
		
		
		// CP Special Pages special pages are not
		if ( $this->db->is_special_page() ) { return false; }

		// BuddyPress special pages are not
		if ( $this->is_buddypress_special_page() ) { return false; }

		// Theme My Login page is not
		if ( $this->is_theme_my_login_page() ) { return false; }

		// Subscribe to Comments Reloaded page is not
		if ( $this->is_subscribe_to_comments_reloaded_page() ) { return false; }


	
		// --<
		return true;
		
	}
	
	
	
	
	
	
	
//#################################################################







	/*
	===============================================================
	PRIVATE METHODS
	===============================================================
	*/
	
	
	



	/*
	---------------------------------------------------------------
	Object Initialisation
	---------------------------------------------------------------
	*/
	
	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
	
		// define filename
		$class_file = 'class_commentpress_db.php';
	
		// get path
		$class_file_path = $this->_file_is_present( $class_file );
		
		// we're fine, include class definition
		require_once( $class_file_path );
	
		// init autoload database object
		$this->db = new CommentPressDatabase( $this );
		


		// define filename
		$class_file = 'class_commentpress_display.php';
		
		// get path
		$class_file_path = $this->_file_is_present( $class_file );
		
		// we're fine, include class definition
		require_once( $class_file_path );
	
		// init display object
		$this->display = new CommentPressDisplay( $this );
		
		
	
		// define filename
		$class_file = 'class_commentpress_nav.php';
	
		// get path
		$class_file_path = $this->_file_is_present( $class_file );
		
		// we're fine, include class definition
		require_once( $class_file_path );
	
		// init display object
		$this->nav = new CommentPressNavigator( $this );



		// define filename
		$class_file = 'class_commentpress_parser.php';
		
		// get path
		$class_file_path = $this->_file_is_present( $class_file );
		
		// we're fine, include class definition
		require_once( $class_file_path );
	
		// init parser object
		$this->parser = new CommentPressParser( $this );
		
		
	
		// register hooks
		$this->_register_hooks();
		
	}







	/** 
	 * @description: register Wordpress hooks
	 * @todo: 
	 *
	 */
	function _register_hooks() {
	
		// access version
		global $wp_version;
	
		// use translation
		add_action( 'init', array( &$this, 'translation' ) );
		
		// modify comment posting
		add_action( 'comment_post', array( &$this, 'save_comment' ), 10, 2 );
		
		// exclude special pages from listings
		add_filter( 'wp_list_pages_excludes', array( &$this, 'exclude_special_pages' ), 10, 1 );
		add_filter( 'parse_query', array( &$this, 'exclude_special_pages_from_admin' ), 10, 1 );
		
		// is this the back end?
		if ( is_admin() ) {
			
			// modify all
			add_filter( 'views_edit-page', array( &$this, 'update_page_counts_in_admin' ), 10, 1 );
			
			// modify admin menu
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			
			// add meta boxes
			add_action( 'add_meta_boxes' , array( &$this, 'add_meta_boxes' ) );
			
			// intercept save
			add_action( 'save_post', array( &$this, 'save_post' ), 10, 2 );
			
			// intercept delete
			add_action( 'before_delete_post', array( &$this, 'delete_post' ), 10, 1 );
			
			// there's a new screen object in 3.3
			if ( version_compare( $wp_version, '3.2.99999', '>=' ) ) {
			
				// use new help functionality
				//add_action('add_screen_help_and_options', array( &$this, 'options_help' ) );

				// NOTE: help is actually called in $this->admin_head() because the 
				// 'add_screen_help_and_options' action does not seem to be working in 3.3-beta1
			
			} else {
			
				// previous help method
				add_action( 'contextual_help', array( &$this, 'contextual_help' ) );
				
			}
			
			// comment block quicktag
			add_action( 'admin_enqueue_scripts', array( &$this, 'enqueue_admin_scripts' ) );
			
			// add a neat link
			add_filter( 'plugin_action_links', array( &$this, 'plugin_action_links' ), 10, 2 );

		} else {
		
			// modify the document head
			add_filter( 'wp_head', array( &$this, 'head' ) );
			
			// add script libraries
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_scripts' ) );
			
			// add CSS files
			add_action( 'wp_enqueue_scripts', array( &$this, 'enqueue_styles' ) );
			
			// add template redirect for TOC behaviour
			add_action( 'template_redirect', array( &$this, 'redirect_to_child' ) );
			
			// modify the content (after all's done)
			add_filter( 'the_content', array( &$this, 'the_content' ), 20 );
			
		}
			
		// if we're in a multisite scenario
		if ( is_multisite() ) {
		
			// add filter for signup page to include sidebar
			add_filter( 'after_signup_form', array( &$this, 'after_signup_form' ) );
			
			// if subdirectory install
			if ( !is_subdomain_install() ) {
			
				// add filter for reserved commentpress special page names
				add_filter( 'subdirectory_reserved_names', array( &$this, 'add_reserved_names' ) );
				
			}
			
		}
		
		// if BP installed, then the following actions will fire...

		// enable BuddyPress functionality
		add_action( 'bp_include', array( &$this, 'buddypress_init' ) );
		
		// add BuddyPress functionality (really late, so group object is set up)
		add_action( 'bp_setup_globals', array( &$this, 'buddypress_globals_loaded' ), 1000 );
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to check for presence of vital files
	 * @param string $filename the name of the Commentpress Plugin file
	 * @return string $filepath absolute path to file
	 * @todo: 
	 *
	 */
	function _file_is_present( $filename ) {
	
		// define path to our requested file
		$filepath = plugin_dir_path( CP_PLUGIN_FILE ) . $filename;
	
		// is our class definition present?
		if ( !is_file( $filepath ) ) {
		
			// oh no!
			die( 'File "'.$filepath.'" is missing from the plugin directory.' );
		
		}
		
		
		
		// --<
		return $filepath;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to check for commentable CPT
	 * @return string $types array of post types
	 * @todo: in development
	 *
	 */
	function _get_commentable_cpts() {
		
		// init
		$_types = false;
		


		// NOTE: exactly how do we support CPTs?
		$args = array(
			//'public'   => true,
			'_builtin' => false
		);
		
		$output = 'names'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'
		
		// get post types
		$post_types = get_post_types( $args, $output, $operator ); 

		// trace
		//print_r( $post_types ); die();
		
		
		
		// did we get any?
		if ( count( $post_types ) > 0 ) {
		
			// init as array
			$_types = false;
			
			// loop
			foreach ($post_types AS $post_type ) {
			
				// add name to array (is_singular expects this)
				$_types[] = $post_type;
				
			}
		
		}

		// trace
		//print_r( $_types ); die();


		// --<
		return $_types;

	}
	
	
	
	
	
	
		
	/** 
	 * @description: adds the formatter to the page/post metabox
	 * @todo:
	 *
	 */
	function _get_post_formatter_metabox( $post ) {
		
		// --------------------------------------------------------------
		// Override post formatter
		// --------------------------------------------------------------
		
		// do we have the option to choose blog type (new in 3.3.1)?
		if ( $this->db->option_exists('cp_blog_type') ) {
		
			// define no types
			$types = array();
			
			// allow overrides
			$types = apply_filters( 'cp_blog_type_options', $types );
			
			// if we get some from a plugin, say...
			if ( !empty( $types ) ) {
			
				// define title
				$type_title = __( 'Text Formatting', 'commentpress-plugin' );
			
				// allow overrides
				$type_title = apply_filters( 'cp_post_type_override_label', $type_title );
			
				// label
				echo '<p><strong><label for="cp_post_type_override">'.$type_title.'</label></strong></p>';
				
				// construct options
				$type_option_list = array();
				$n = 0;
				
				// set key
				$key = '_cp_post_type_override';
				
				// default to current blog type
				$value = $this->db->option_get('cp_blog_type');
				
				// but, if the custom field has a value...
				if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
				
					// get it
					$value = get_post_meta( $post->ID, $key, true );
					
				}
				
				foreach( $types AS $type ) {
					if ( $n == $value ) {
						$type_option_list[] = '<option value="'.$n.'" selected="selected">'.$type.'</option>';
					} else {
						$type_option_list[] = '<option value="'.$n.'">'.$type.'</option>';
					}
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );
				
				
				
				// select
				echo '
				<p>
				<select id="cp_post_type_override" name="cp_post_type_override">
					'.$type_options.'
				</select>
				</p>
				';

			}
			
		}

	}
	
	
	
	
	
	
		
	/** 
	 * @description: adds the default sidebar preference to the page/post metabox
	 * @todo:
	 *
	 */
	function _get_default_sidebar_metabox( $post ) {
		
		// --------------------------------------------------------------
		// Override post formatter
		// --------------------------------------------------------------
		
		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {
		
			// show a title
			echo '<p><strong><label for="cp_sidebar_default">' . __( 'Default Sidebar' , 'commentpress-plugin' ) . '</label></strong></p>';
			
			// set key
			$key = '_cp_sidebar_default';
			
			// default to show
			$_sidebar = $this->db->option_get( 'cp_sidebar_default' );
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// get it
				$_sidebar = get_post_meta( $post->ID, $key, true );
				
			}
			
			// select
			echo '
<p>
<select id="cp_sidebar_default" name="cp_sidebar_default">
	<option value="toc" '.(($_sidebar == 'toc') ? ' selected="selected"' : '').'>'.__('Contents', 'commentpress-plugin').'</option>
	<option value="activity" '.(($_sidebar == 'activity') ? ' selected="selected"' : '').'>'.__('Activity', 'commentpress-plugin').'</option>
	<option value="comments" '.(($_sidebar == 'comments') ? ' selected="selected"' : '').'>'.__('Comments', 'commentpress-plugin').'</option>
</select>
</p>
';
			
		}

	}
	
	
	
	
	
	
		
//#################################################################







} // class ends






?>