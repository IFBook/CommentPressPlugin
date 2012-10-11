<?php /*
===============================================================
Class CommentPress Version 1.0
===============================================================
AUTHOR			: Christian Wach <needle@haystack.co.uk>
LAST MODIFIED	: 04/05/2009
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
	
	// display object
	var $display;
	
	// init text_signatures
	var $text_signatures = array();
	
	// database object
	var $db;
	
	// options page
	var $options_page;
	
	// buddypress present
	var $buddypress = false;
	
	// bp-groupblog present
	var $bp_groupblog = false;
	
	// all comments
	var $comments_all = array();
	
	// approved comments
	var $comments_approved = array();
	
	// sorted comments
	var $comments_sorted = array();
	





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
			
			
			
			// retrieve all comments and store...
			// we need this data multiple times and only need to get it once
			$this->comments_all = $this->db->get_all_comments( $post->ID );
			
			// retrieve approved comments
			$this->comments_approved = $this->db->get_approved_comments( $post->ID );
			
			
			
			// strip out <!--shortcode--> tags
			$content = $this->_strip_shortcodes( $content );
			
			
			
			// check for our quicktag
			$has_quicktag = $this->_has_comment_block_quicktag( $content );
	
			// if it hasn't...
			if ( !$has_quicktag ) {
			
				// if, BP, what type of blog is this?
				if ( $this->is_groupblog() ) {
				
					// auto-format content accordingly
					
					// get action to take
					$action = apply_filters(
						
						// hook
						'cp_select_content_formatter',
						
						// default
						'tag'
						
					);
					
					// act on it
					switch( $action ) {
						
						// for poetry, for example, line by line commenting formatter is better
						case 'line' :

							// set constant - okay, since we never return here
							if ( !defined( 'CP_BLOCK' ) ) 
								define( 'CP_BLOCK', 'line' );
						
							// generate text signatures array
							$this->text_signatures = $this->_generate_line_signatures( $content );
							//print_r( $this->text_signatures ); die();
							
							// only continue parsing if we have an array of sigs
							if ( !empty( $this->text_signatures ) ) {
							
								// filter content by <br> and <br /> tags
								$content = $this->_parse_lines( $content );
								//$content = $this->_filter_content_by_line( $content );
								
							}
							
							break;
						
						// for general prose, existing formatter is fine
						case 'tag' :

							// set constant
							if ( !defined( 'CP_BLOCK' ) ) 
								define( 'CP_BLOCK', 'tag' );
								
							// generate text signatures array
							$this->text_signatures = $this->_generate_text_signatures( $content, 'p|ul|ol' );
							//print_r( $this->text_signatures ); die();
							
							// only continue parsing if we have an array of sigs
							if ( !empty( $this->text_signatures ) ) {
							
								// filter content by <p>, <ul> and <ol> tags
								$content = $this->_parse_content( $content, 'p|ul|ol' );
								
							}
							
							break;
					
					}
					
				} else {
				
					// as normal...
					
					// TO DO: check internal options, set on page/post edit screen
			
					// set constant
					if ( !defined( 'CP_BLOCK' ) ) 
						define( 'CP_BLOCK', 'tag' );
				
					// generate text signatures array
					$this->text_signatures = $this->_generate_text_signatures( $content, 'p|ul|ol' );
				
					// only parse content if we have an array of sigs
					if ( !empty( $this->text_signatures ) ) {
					
						// filter content by <p>, <ul> and <ol> tags
						$content = $this->_parse_content( $content, 'p|ul|ol' );
						
					}
					
				}
				
			} else {
			
				// set constant
				if ( !defined( 'CP_BLOCK' ) ) 
					define( 'CP_BLOCK', 'block' );
			
				// generate text signatures array
				$this->text_signatures = $this->_generate_block_signatures( $content );
				//print_r( $this->text_signatures ); die();
				
				// only parse content if we have an array of sigs
				if ( !empty( $this->text_signatures ) ) {
				
					// filter content by <!--commentblock--> quicktags
					$content = $this->_parse_blocks( $content );
					
				}
				
			}



			// store text sigs
			$this->db->set_text_sigs( $this->text_signatures );
			
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
	
		// have we already sorted the comments?
		if ( !empty( $this->comments_sorted ) ) {
			
			// --<
			return $this->comments_sorted;
		
		}
	
		// --<
		return $this->_get_sorted_comments( $post_ID );
		
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
	 * @description: parses the content by tag
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return string $content the parsed content
	 * @todo: 
	 *
	 */
	function _parse_content( $content, $tag = 'p|ul|ol' ) {
	


		// get our paragraphs
		$matches = $this->_get_text_matches( $content, $tag );
		
		// kick out if we don't have any
		if( !count( $matches ) ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// reference our post
		global $post;



		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );
		//print_r( $this->comments_sorted ); die();
	


		// we already have our text signatures, so set flag
		$sig_key = 0;
		
		// run through 'em...
		foreach( $matches AS $paragraph ) {
	  
			// get a signature for the paragraph
			$text_signature = $this->text_signatures[ $sig_key ];
			
			// increment
			$sig_key++;
			
			// get comment count
			// NB: the sorted array contains whole page as key 0, so we use the incremented value
			$comment_count = count( $this->comments_sorted[ $sig_key ] );
			
			// get comment icon
			$commenticon = $this->display->get_icon( $comment_count, $text_signature, 'auto', $sig_key );
			
			// set pattern by first tag
			switch ( substr( $paragraph, 0 , 2 ) ) {
			
				case '<p': $tag = 'p'; break;
				case '<o': $tag = 'ol'; break;
				case '<u': $tag = 'ul'; break;
			
			}

			/*
			-------------------------------------------------------------
			NOTES
			-------------------------------------------------------------
			
			There's a temporary fix to exclude <param> and <pre> tags by excluding subsequent 'a' and 
			'r' chars - this regex needs more attention so that only <p> and <p ...> are captured.
			In HTML5 there is also the <progress> tag, but this is excluded along with <pre>
			Also, the WordPress visual editor inserts styles into <p> tags for text justification,
			so we need to feed this regex with enhanced tags to capture the following:
			
			<p style="text-align:left;"> 
			<p style="text-align:right;"> 
			<p style="text-align:center;">
			<p style="text-align:justify;">
			
			-------------------------------------------------------------
			*/
			
			// further checks when there's a <p> tag
			if ( $tag == 'p' ) {
				
				// set pattern by TinyMCE tag attribute
				switch ( substr( $paragraph, 0 , 22 ) ) {
				
					case '<p style="text-align:l': $tag = 'p style="text-align:left;"'; break;
					case '<p style="text-align:r': $tag = 'p style="text-align:right;"'; break;
					case '<p style="text-align:c': $tag = 'p style="text-align:center;"'; break;
					case '<p style="text-align:j': $tag = 'p style="text-align:justify;"'; break;
					
					// if we fall through to here, treat it like it's just a <p> tag above.
					// This will fail if there are custom attributes set in the HTML editor,
					// but I'm not sure how to handle that without migrating to an XML parser
				
				}
	
				// test for Simple Footnotes para "heading"
				switch ( substr( $paragraph, 0 , 16 ) ) {
					case '<p class="notes"': $tag = 'p class="notes"'; break;
				}
	
			}

			/*
			-------------------------------------------------------------
			NOTES
			-------------------------------------------------------------
			
			There are also flaws with parsing nested lists, both ordered and unordered. The WordPress
			Unit Tests XML file reveals these, though the docs are hopefully clear enough that people
			won't use nested lists. However, the severity is such that I'm contemplating migrating to
			a DOM parser such as:
			
			phpQuery <https://github.com/TobiaszCudnik/phpquery>
			Simple HTML DOM <http://sourceforge.net/projects/simplehtmldom/>
			Others <http://stackoverflow.com/questions/3577641/how-to-parse-and-process-html-with-php>
			
			There are so many examples of people saying "don't use regex with HTML" that this probably
			ought to be done when time allows.

			-------------------------------------------------------------
			*/
			
			// further checks when there's a <ol> tag
			if ( $tag == 'ol' ) {
				
				// set pattern by TinyMCE tag attribute
				switch ( substr( $paragraph, 0 , 21 ) ) {
					
					// compat with WP Footnotes
					case '<ol class="footnotes"': $tag = 'ol class="footnotes"'; break;
					
					// see notes for p tag above
				
				}
	
			}

			// assign icons to paras
			$pattern = array('#<('.$tag.'[^a^r>]*)>#');
			$replace = array( $this->display->get_para_tag( $text_signature, $commenticon, $tag ) );
			$block = preg_replace( $pattern, $replace, $paragraph );
			
			// NB: because str_replace() has no limit to the replacements, I am switching to
			// preg_replace() because that does have a limit
			//$content = str_replace( $paragraph, $block, $content );
			
			// prepare paragraph for preg_replace
			$prepared_para = preg_quote( $paragraph );
			
			// because we use / as the delimiter, we need to escape all /s
			$prepared_para = str_replace( '/', '\/', $prepared_para );
			
			// only once please
			$limit = 1;

			// replace the paragraph in the original context, preserving all other content
			$content = preg_replace( 
			
				//array($paragraph), 
				'/'.$prepared_para.'/', 
				$block,
				$content,
				$limit				
				
			);
			
			/*
			print_r( array( 
			
				//'p' => $paragraph,
				'p' => $prepared_para,
				'b' => $block,
				'c' => $content
			
			) ); //die();
			*/
			
		}
		


		/*
		print_r( array( 
		
			'd' => $duplicates,
			't' => $this->text_signatures,
			'c' => $content 
		
		) ); 
		
		die();
		*/
		


		// --<
		return $content;

	}
	






	/** 
	 * @description: splits the content into an array by tag
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $matches the ordered array of matched items
	 * @todo: 
	 *
	 */
	function _get_text_matches( $content, $tag = 'p|ul|ol' ) {
	
		// filter out embedded tweets
		$content = $this->_filter_twitter_embeds( $content );
				
		// get our paragraphs (needed to split regex into two strings as some IDEs 
		// don't like PHP closing tags, even they are part of a regex and not actually
		// closing tags at all) 
		//preg_match_all( '/<('.$tag.')[^>]*>(.*?)(<\/('.$tag.')>)/', $content, $matches );
		preg_match_all( '#<('.$tag.')[^>]*?'.'>(.*?)</('.$tag.')>#si', $content, $matches );
		//print_r( $matches[0] ); print_r( $matches[1] ); exit();
		
		// kick out if we don't have any
		if( !empty($matches[0]) ) {
		
			// --<
			return $matches[0];
			
		} else {
		
			// --<
			return array();
		
		}
		
	}
	
	
	
		
		
		
	/** 
	 * @description: parses the content by tag and builds text signatures array
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $text_signatures the ordered array of text signatures
	 * @todo: 
	 *
	 */
	function _generate_text_signatures( $content, $tag = 'p|ul|ol' ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// store text sigs array in global
			$this->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
	
		// get our paragraphs
		$matches = $this->_get_text_matches( $content, $tag );
		
		// kick out if we don't have any
		if( !count( $matches ) ) {
		
			// store text sigs array in global
			$this->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em...
		foreach( $matches AS $paragraph ) {
	  
			// get a signature for the paragraph
			$text_signature = $this->_generate_text_signature( $paragraph );
			
			// do we have one already?
			if ( in_array( $text_signature, $this->text_signatures ) ) {
			
				// is it in the duplicates array?
				if ( array_key_exists( $text_signature, $duplicates ) ) {
				
					// add one
					$duplicates[ $text_signature ]++;
				
				} else {
				
					// add it
					$duplicates[ $text_signature ] = 1;
				
				}
				
				// add number to end of text sig
				$text_signature .= '_'.$duplicates[ $text_signature ];
				
			}
			
			// add to signatures array
			$this->text_signatures[] = $text_signature;
			
		}
		
		
		
		// store text sigs array in global
		$this->db->set_text_sigs( $this->text_signatures );



		// --<
		return $this->text_signatures;

	}
	






	/** 
	 * @description: parse the content by line (<br />)
	 * @param string $content the post content
	 * @return string $content the parsed content
	 * @todo: 
	 *
	 */
	function _parse_lines( $content ) {
	
		// get our lines
		$matches = $this->_get_line_matches( $content );
		//print_r( $matches ); die();
		
		// kick out if we don't have any
		if( !count( $matches ) ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// reference our post
		global $post;



		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );
		//print_r( $this->comments_sorted ); die();
	


		// we already have our text signatures, so set flag
		$sig_key = 0;
		
		// init our content array
		$content_array = array();
	


		// run through 'em...
		foreach( $matches AS $paragraph ) {

			// is there any content?
			if ( $paragraph != '' ) {
				
				// check for paras
				if ( $paragraph == '<p>' OR $paragraph == '</p>' ) {
				
					// do we want to allow commenting on verses?
				
					// add to content array
					$content_array[] = $paragraph;
	
				} else {
				
					// line commenting
				
					// get a signature for the paragraph
					$text_signature = $this->text_signatures[ $sig_key ];
					
					// increment
					$sig_key++;
					
					// get comment count
					// NB: the sorted array contains whole page as key 0, so we use the incremented value
					$comment_count = count( $this->comments_sorted[ $sig_key ] );
					
					// get comment icon
					$commenticon = $this->display->get_icon( $comment_count, $text_signature, 'line', $sig_key );
					
					// get comment icon markup
					$icon_html = $this->display->get_para_tag( $text_signature, $commenticon, 'span' );
					
					// assign icons to blocks
					$paragraph = $icon_html.$paragraph;
					
					// add to content array
					$content_array[] = $paragraph;
	
				}
				
			}
			
		}

		//print_r( $this->text_signatures ); //die();
		//print_r( $duplicates ); die();
		//die();
	

		
		// rejoin and exclude quicktag
		$content = implode( '', $content_array );
	


		// --<
		return $content;

	}
	






	/** 
	 * @description: splits the content into an array by line
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $matches the ordered array of matched items
	 * @todo: 
	 *
	 */
	function _get_line_matches( $content ) {
		
		// filter out embedded tweets
		$content = $this->_filter_twitter_embeds( $content );
				
		// wrap all lines with spans
		
		// get all instances
		$pattern = array(
		
			'/<br>/', 
			'/<br\/>/', 
			'/<br \/>/', 
			'/<br>\n/', 
			'/<br\/>\n/', 
			'/<br \/>\n/', 
			'/<p>/', 
			'/<\/p>/'
			
		);
		
		// define replacements
		$replace = array( 
		
			'</span><br>', 
			'</span><br/>', 
			'</span><br />', 
			'<br>'."\n".'<span class="cp-line">', 
			'<br/>'."\n".'<span class="cp-line">', 
			'<br />'."\n".'<span class="cp-line">', 
			'<p><span class="cp-line">', 
			'</span></p>' 
			
		);
		
		// do replacement
		$content = preg_replace( $pattern, $replace, $content );
		
		/*
		print_r( array(
		
			'content' => $content,
		
		) ); die();
		*/
		


		// explode by <span>
		$output_array = explode( '<span class="cp-line">', $content );
		//print_r( $output_array ); die();
		
		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
		
			// --<
			return array();
		
		}
		
		
		
		// --<
		return $output_array;
		
	}
	
	
	
		
		
	/** 
	 * @description: parses the content by line (<br />) and builds text signatures array
	 * @param string $content the post content
	 * @return array $text_signatures the ordered array of text signatures
	 * @todo: 
	 *
	 */
	function _generate_line_signatures( $content ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// store text sigs array in global
			$this->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// wrap all lines with spans
		


		// explode by <span>
		$output_array = $this->_get_line_matches( $content );
		//print_r( $output_array ); die();
		
		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
		
			// store text sigs array in global
			$this->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// reference our post
		global $post;

		// init our content array
		$content_array = array();

		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();



		// run through 'em...
		foreach( $output_array AS $paragraph ) {
		
			// is there any content?
			if ( $paragraph != '' ) {
				
				// check for paras
				if ( $paragraph == '<p>' OR $paragraph == '</p>' ) {
				
					// do we want to allow commenting on verses?
				
				} else {
				
					// line commenting
				
					// get a signature for the paragraph
					$text_signature = $this->_generate_text_signature( $paragraph );
					
					// do we have one already?
					if ( in_array( $text_signature, $this->text_signatures ) ) {
					
						// is it in the duplicates array?
						if ( array_key_exists( $text_signature, $duplicates ) ) {
						
							// add one
							$duplicates[ $text_signature ]++;
						
						} else {
						
							// add it
							$duplicates[ $text_signature ] = 1;
						
						}
						
						// add number to end of text sig
						$text_signature .= '_'.$duplicates[ $text_signature ];
						
					}
					
					// add to signatures array
					$this->text_signatures[] = $text_signature;
					
				}
				
			}
			
		}

		//print_r( $this->text_signatures ); //die();
		//print_r( $duplicates ); die();
		//die();
	


		// store text sigs array in global
		$this->db->set_text_sigs( $this->text_signatures );



		// --<
		return $this->text_signatures;

	}
	






	/** 
	 * @description: parses the content by comment block
	 * @param string $content the post content
	 * @return string $content the parsed content
	 * @todo: this is probably mighty slow - review preg_replace patterns
	 *
	 */
	function _parse_blocks( $content ) {
	
		// get our lines
		$matches = $this->_get_block_matches( $content );
		//print_r( $matches ); die();
		
		// kick out if we don't have any
		if( !count( $matches ) ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// reference our post
		global $post;

		

		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );
		//print_r( $this->comments_sorted ); die();
	


		// we already have our text signatures, so set flag
		$sig_key = 0;
		
		// init content array
		$content_array = array();
		
		
		
		// run through 'em...
		foreach( $matches AS $paragraph ) {
		
			// is there any content?
			if ( $paragraph != '' ) {
	  
				// get a signature for the paragraph
				$text_signature = $this->text_signatures[ $sig_key ];
				
				// increment
				$sig_key++;
				
				// get comment count
				// NB: the sorted array contains whole page as key 0, so we use the incremented value
				$comment_count = count( $this->comments_sorted[ $sig_key ] );
				
				// get comment icon
				$commenticon = $this->display->get_icon( $comment_count, $text_signature, 'block', $sig_key );
				
				// get comment icon markup
				$icon_html = $this->display->get_para_tag( $text_signature, $commenticon, 'div' );
				
				// assign icons to blocks
				$paragraph = $icon_html.$paragraph.'</div>'."\n\n\n\n";
				
				// add to content array
				$content_array[] = $paragraph;
				
			}
			
		}

		//print_r( $this->text_signatures ); //die();
		//print_r( $duplicates ); die();
	

		
		// rejoin and exclude quicktag
		$content = implode( '', $content_array );
	


		// --<
		return $content;

	}
	






	/** 
	 * @description: splits the content into an array by block
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $matches the ordered array of matched items
	 * @todo: 
	 *
	 */
	function _get_block_matches( $content ) {
		
		// wp_texturize() does an okay job with creating paragraphs, but comments tend
		// to screw things up. let's try and fix...

		//print_r( array( 'before' => $content ) );

		// first, replace all instances of '   <!--commentblock-->   ' with
		// '<p><!--commentblock--></p>\n'
		$content = preg_replace( 
		
			'/\s+<!--commentblock-->\s+/', 
			'<p><!--commentblock--></p>'."\n", 
			$content 
			
		);

		// next, replace all instances of '<p><!--commentblock-->fengfnefe' with
		// '<p><!--commentblock--></p>\n<p>fengfnefe'
		$content = preg_replace( 
		
			'/<p><!--commentblock-->/', 
			'<p><!--commentblock--></p>'."\n".'<p>', 
			$content 
			
		);

		// next, replace all instances of 'fengfnefe<!--commentblock--></p>' with
		// 'fengfnefe</p>\n<p><!--commentblock--></p>'
		$content = preg_replace( 
		
			'/<!--commentblock--><\/p>/', 
			'</p>'."\n".'<p><!--commentblock--></p>'."\n", 
			$content 
			
		);

		// replace all instances of '<br />\n<!--commentblock--><br />\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace( 
		
			'/<br \/>\s+<!--commentblock--><br \/>\s+/', 
			'</p>'."\n".'<p><!--commentblock--></p>'."\n".'<p>', 
			$content 
			
		);

		// next, replace all instances of '<br />\n<!--commentblock--></p>\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace( 
		
			'/<br \/>\s+<!--commentblock--><\/p>\s+/', 
			'</p>'."\n".'<p><!--commentblock--></p>'."\n", 
			$content 
			
		);

		// next, replace all instances of '<p><!--commentblock--><br />\n' with
		// '<p><!--commentblock--></p>\n<p>'
		$content = preg_replace( 
		
			'/<p><!--commentblock--><br \/>\s+/', 
			'<p><!--commentblock--></p>'."\n".'<p>', 
			$content 
			
		);
		
		// repair some oddities: empty newlines with whitespace after:
		$content = preg_replace( 
		
			'/<p><br \/>\s+/', 
			'<p>', 
			$content 
			
		);
		
		// repair some oddities: empty newlines without whitespace after:
		$content = preg_replace( 
		
			'/<p><br \/>/', 
			'<p>', 
			$content 
			
		);
		
		// repair some oddities: empty paragraphs with whitespace inside:
		$content = preg_replace( 
		
			'/<p>\s+<\/p>\s+/', 
			'', 
			$content 
			
		);
		
		// repair some oddities: empty paragraphs without whitespace inside:
		$content = preg_replace( 
		
			'/<p><\/p>\s+/', 
			'', 
			$content 
			
		);

		// repair some oddities: any remaining empty paragraphs:
		$content = preg_replace( 
		
			'/<p><\/p>/', 
			'', 
			$content 
			
		);

		//print_r( array( 'after' => $content ) ); die();
		
		
		
		// explode by <p> version to temp array
		$output_array = explode( '<p><'.'!--commentblock--></p>', $content );
		
		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
		
			// --<
			return array();
		
		}
		
		
		
		// --<
		return $output_array;
		
	}
	
	
	
		
		
	/** 
	 * @description: parses the content by comment block and generates text signature array
	 * @param string $content the post content
	 * @return array $text_signatures the ordered array of text signatures
	 * @todo: this is probably mighty slow - review preg_replace patterns
	 *
	 */
	function _generate_block_signatures( $content ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// store text sigs array in global
			$this->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// get blocks array
		$matches = $this->_get_block_matches( $content );
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em...
		foreach( $matches AS $paragraph ) {
		
			// is there any content?
			if ( $paragraph != '' ) {
	  
				// get a signature for the paragraph
				$text_signature = $this->_generate_text_signature( $paragraph );
				
				// do we have one already?
				if ( in_array( $text_signature, $this->text_signatures ) ) {
				
					// is it in the duplicates array?
					if ( array_key_exists( $text_signature, $duplicates ) ) {
					
						// add one
						$duplicates[ $text_signature ]++;
					
					} else {
					
						// add it
						$duplicates[ $text_signature ] = 1;
					
					}
					
					// add number to end of text sig
					$text_signature .= '_'.$duplicates[ $text_signature ];
					
				}
				
				// add to signatures array
				$this->text_signatures[] = $text_signature;
				
			}
			
		}

		//print_r( $this->text_signatures ); die();
		//print_r( $duplicates ); die();
	


		// store text sigs array in global
		$this->db->set_text_sigs( $this->text_signatures );



		// --<
		return $this->text_signatures;

	}
	






	/** 
	 * @description: utility to check if the content has our custom quicktag
	 * @param string $content the post content
	 * @return string $content modified post content
	 * @todo: 
	 *
	 */
	function _has_comment_block_quicktag( $content ) {
	
		// init
		$return = false;
		
		
	
		// look for < !--commentblock--> comment
		if ( strstr( $content, '<!--commentblock-->' ) ) {
		
			// yep
			$return = true;
		
		}
		
		
		
		// --<
		return $return;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to remove our custom quicktag
	 * @param string $content the post content
	 * @return string $content modified post content
	 * @todo: 
	 *
	 */
	function _strip_comment_block_quicktag( $content ) {
	
		// look for < !--commentblock--> comment
		if ( preg_match('/<'.'!--commentblock--><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// look for < !--commentblock--> comment
		if ( preg_match('/<p><'.'!--commentblock--><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// --<
		return $content;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to strip out shortcodes from content otherwise they get formatting
	 * @param string $content the post content
	 * @return string $content modified post content
	 * @todo: 
	 *
	 */
	function _strip_shortcodes( $content ) {
	
		/*
		========================
		Notes added: 08 Mar 2012
		========================
		
		Here's how these quicktags work...
		http://codex.wordpress.org/Customizing_the_Read_More
		
		
		-------------
		More Quicktag
		-------------
		
		However, we cannot be sure of how the quicktags has been inserted. For example (1):
		
		<p>Here&#8217;s the teaser<br />
		<span id="more-689"></span><br />
		Here&#8217;s the rest of the post</p>
		
		Is the intention here that the teaser is a paragraph? I'd say so.
		
		What about (2):
		
		<p>Here&#8217;s the teaser</p>
		<p><span id="more-689"></span></p>
		<p>Here&#8217;s the rest of the post</p>
		
		I'd say the same as above.
		
		And then these two possibilities (3) & (4):
		
		<p>Here&#8217;s the teaser<span id="more-689"></span><br />
		Here&#8217;s the rest of the post</p>
		
		<p>Here&#8217;s the teaser<br />
		<span id="more-689"></span>Here&#8217;s the rest of the post</p>
		
		Now, for our purposes, since we currently use the excerpt in the blog archives, only
		(1) and (2) are truly problematic - because they cause visible formatting. (3) & (4)
		do not currently get filtered out because the spans are inline - but they do imply
		that the content before and after should be self-contained. As a result, I think it
		is probably better to add a statement about correct usage in to the help text so that
		we can reliably parse the content.
		
		
		-----------------
		NoTeaser Quicktag
		-----------------
		
		The Codex says "Include <!--noteaser--> in the post text, immediately after the <!--more-->"
		which really means *on the same line*. When this is done, our content looks like this (1):
		
		<p><span id="more-691"></span><!--noteaser--></p>
		<p>And this is the rest of the post blah</p>
		
		Or (2):
		
		<p><span id="more-691"></span><!--noteaser--><br />
		And this is the rest of the post blah</p>
		
		*/
	
		//print_r( $content ); die();
		
		// look for inline <!--more--> span
		if ( preg_match('/<span id="more-(.*?)?'.'><\/span><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		// look for separated <!--more--> span
		if ( preg_match('/<p><span id="more-(.*?)?'.'><\/span><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
				
		// look for inline <!--more--> span correctly followed by <!--noteaser-->
		if ( preg_match('/<span id="more-(.*?)?'.'><\/span><!--noteaser--><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
				
		// look for separated <!--more--> span correctly followed by <!--noteaser-->
		if ( preg_match('/<p><span id="more-(.*?)?'.'><\/span><!--noteaser--><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
				
		// look for incorrectly placed inline <!--noteaser--> comment
		if ( preg_match('/<'.'!--noteaser--><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// look for incorrectly placed separated <!--noteaser--> comment
		if ( preg_match('/<p><'.'!--noteaser--><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// this gets the additional text... (not used)
		if ( !empty($matches[1]) ) {
			//$more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
		}
		
		//print_r( $content ); die();


		// --<
		return $content;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: generates a text signature based on the content of a paragraph
	 * @param string $text the text of a paragraph
	 * @param integer $position paragraph position in a post
	 * @return string $sig the generated text signature
	 * @todo: implement some kind of paragraph identifier to distiguish identical paragraphs?
	 *
	 */
	function _generate_text_signature( $text, $position = null ) {
	
		// get an array of words from the text
		$words = explode( ' ', ereg_replace( '[^A-Za-z]', ' ', html_entity_decode($text) ) );
		
		// store unique words
		// NB: this may be a mistake for poetry, which can use any words in any order
		$unique_words = array_unique( $words );
		
		
		
		// init sig
		$text_signature = null;
	
		// run through our unique words
		foreach( $unique_words AS $key => $word ) {
			
			// add first letter
			$text_signature .= substr( $word, 0, 1 );
			
			// limit to 250 chars
			// NB: this is because we have changed the format of text signatures by adding numerals
			// when there are duplicates. Duplicates add at least 2 characters, so there is the 
			// (admittedly remote) possibility of exceeding the varchar(255) character limit.
			if( $key > 250 ) { break; }
			
		}
		
		
		
		// get sig - think this through (not used, as position always null
		$sig = ($position) ? 
				$position . ':' . $text_signature : 
				$text_signature;
		
		
		
		// --<
		return $sig;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: removes embedded tweets (new in WP 3.4)
	 * @param string $content the post content
	 * @return string $content the filtered post content
	 * @todo: make these commentable
	 *
	 */
	function _filter_twitter_embeds( $content ) {
	
		// test for a WP 3.4 function
		if ( function_exists( 'wp_get_themes' ) ) {
	
			// look for Embedded Tweet <blockquote>
			if ( preg_match('#<(blockquote class="twitter-tweet)[^>]*?'.'>(.*?)</(blockquote)>#si', $content, $matches) ) {
			
				// derive list
				$content = explode( $matches[0], $content, 2 );
				
				// rejoin to exclude from content to be parsed
				$content = implode( '', $content );
				
				// also remove twitter script
				$content = str_replace(
				
					'<p><script src="//platform.twitter.com/widgets.js" charset="utf-8"></script></p>', 
					'', 
					$content 
					
				);
				
			}
			
		}
		
		
		
		// --<
		return $content;
				
	}
	
	
	
		
		
		
	/** 
	 * @description: filter comments to find comments for the current page of a multipage post
	 * @param array $comments array of comment objects
	 * @return array $filtered array of comments for the current page
	 * @todo: 
	 *
	 */
	function _multipage_comment_filter( $comments ) {
	  
		// access globals
		global $post, $page, $multipage;
		//print_r( $comments ); die();
		


	  	// init return
		$filtered = array();

		// kick out if no comments
		if( !is_array( $comments ) OR empty( $comments ) ) {
		
			// --<
			return $filtered;
		}
		
		
		
		// kick out if not multipage
		if( !isset( $multipage ) OR !$multipage ) {
		
			// --<
			return $comments;
			
		}
		
		
		
		// now add only comments that are on this page or are page-level
		foreach ( $comments AS $comment ) {
		
			// if it has a text sig
			if ( !is_null( $comment->comment_text_signature ) AND $comment->comment_text_signature != '' ) {
			
				// set key
				$key = '_cp_comment_page';
				
				// does it have a comment meta value?
				if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {
				
					// get the page number
					$page_num = get_comment_meta( $comment->comment_ID, $key, true );
					
					// is it the current one?
					if ( $page_num == $page ) {
					
						// add it
						$filtered[] = $comment;
						
					}
					
				}
				
			} else {
			
				// page-level comment: add it
				$filtered[] = $comment;
				
			}
		
		}
		
		
		
		// --<
		return $filtered;
		
	}
	






	/** 
	 * @description: get comments sorted by text signature and paragraph
	 * @param integer $post_ID the ID of the post
	 * @return array $_comments
	 * @todo: 
	 *
	 */
	function _get_sorted_comments( $post_ID ) {
	
		// init return
		$_comments = array();
		
		
	
		// get all comments
		$comments = $this->comments_all;
		
		
		
		// filter out any multipage comments not on this page
		$comments = $this->_multipage_comment_filter( $comments );
		//print_r( $comments ); die();
		
		
		
		// get our signatures
		$_sigs = $this->db->get_text_sigs();
		//print_r( $_sigs ); die();
		
		// assign comments to text signatures
		$_assigned = $this->_assign_comments( $comments, $_sigs );
		
		// NB: $_assigned is an array with sigs as keys and array of comments as value
		// it may be empty...
		
		// we must have text signatures...
		if ( !empty( $_sigs ) ) {
		


			// if we have any comments on the whole page...
			if ( isset( $_assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ] ) ) {
		
				// add them first
				$_comments[] = $_assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ];
				
			} else {
			
				// append empty array
				$_comments[] = array();
			
			}
			
		

			// then add  in the order of our text signatures
			foreach( $_sigs AS $text_signature ) {
			
				// if we have any assigned...
				if ( isset( $_assigned[ $text_signature ] ) ) {
			
					// append assigned comments
					$_comments[] = $_assigned[ $text_signature ];
					
				} else {
				
					// append empty array
					$_comments[] = array();
				
				}
				
			}
			
		}
		
		
		
		//print_r( $_comments ); die();

		// --<
		return $_comments;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: filter comments by text signature
	 * @param array $comments array of comment objects
	 * @param array $text_signatures array of text signatures
	 * @param integer $confidence the confidence level of paragraph identity - default 90%
	 * @return array $assigned array with text signatures as keys and array of comments as values
	 * @todo: 
	 *
	 */
	function _assign_comments( $comments, $text_signatures, $confidence = 90 ) {
	  
	  	// init returned array
	  	// NB: we use a very unlikely key for page-level comments: WHOLE_PAGE_OR_POST_COMMENTS
		$assigned = array();

		// kick out if no comments
		if( !is_array( $comments ) OR empty( $comments ) ) {
		
			// --<
			return $assigned;
		}
		
		
		
		// kick out if no text_signatures
		if( !is_array( $text_signatures ) OR empty( $text_signatures ) ) {
		
			// --<
			return $assigned;
		}
		
		
		
		/*
		print_r( array( 
		
			'comments' => $comments,
			'sigs' => $text_signatures 
		
		) ); die();
		*/
		
		// run through our comments...
		foreach( $comments AS $comment ) {
		
			// test for empty comment text signature
			if ( !is_null( $comment->comment_text_signature ) AND $comment->comment_text_signature != '' ) {
			
				// do we have an exact match in the text sigs array?
				// NB: this will work, because we're already ensuring identical sigs are made unique
				if ( in_array( $comment->comment_text_signature, $text_signatures ) ) {
					
					// yes, assign to that key
					$assigned[ $comment->comment_text_signature ][] = $comment;
				
				} else {
				
					// init possibles array
					$possibles = array();
				
					// find the nearest matching text signature
					foreach( $text_signatures AS $text_signature ) {
					
						// compare strings...
						similar_text( $comment->comment_text_signature, $text_signature, $score );
						
						//print_r( $score.'<br>' ); 
						
						// add to possibles array if it passes
						if( $score >= $confidence ) { $possibles[ $text_signature ] = $score; }
					
					}
					//die();
					
					// did we get any?
					if ( !empty( $possibles ) ) {
						
						// sort them by score
						arsort( $possibles );
						//print_r( array_keys( $possibles ) ); die();
						
						// let's use the sig with the highest score
						$highest = array_pop( array_keys( $possibles ) );
					
						// assign comment to that key
						$assigned[ $highest ][] = $comment;
					
					} else {
					
						// we have an orphaned comment - assign to page
						$assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ][] = $comment;
					
					}
				
				}
				
			} else {
			
				// we have comment with no text sig - assign to page
				$assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ][] = $comment;
			
			}
			
		}
		
		// let's have a look
		//print_r( $assigned ); die();
		
		
		
		// --<
		return $assigned;
		
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