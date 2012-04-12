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
	





	/** 
	 * @description: initialises this object
	 * @return object
	 * @todo: 
	 *
	 */
	function CommentPress() {
	
		// init
		$this->_init();

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
		
		// if we're in multisite
		if ( CP_PLUGIN_CONTEXT != 'standard' ) {
			
			// activate our newly created blog
			switch_to_blog( $blog_id );

		}
	
		// initialise display - sets the theme
		$this->display->initialise( $blog_id );
		
		// initialise database
		$this->db->initialise( $blog_id );
		
	}
	
	
	
	
	
	
		
	/** 
	 * @description: runs when plugin is deactivated
	 * @todo: do we want to remove all traces of the plugin?
	 *
	 */
	function deactivate() {
	
		// call database destroy method
		$this->db->destroy();
		
		// call display destroy method
		$this->display->destroy();
		
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
	 * @description: configure when BuddyPress is active
	 * @todo: 
	 *
	 */
	function buddypress_init() {
	
		// for BuddyPress integration...
		if ( defined( 'BP_VERSION' ) ) {
		
			// we've got BuddyPress installed
			$this->buddypress = true;
			
		}
	
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
		
			// init comment counts array
			$this->comment_counts = array();
			
			// add whole page count to comment counts
			$this->comment_counts[] = $this->_text_signature_count( $post->ID, '' );
			
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
						
							// filter content by <br> and <br /> tags
							$content = $this->_filter_content_by_line( $content );
							
							break;
						
						// for general prose, existing formatter is fine
						case 'tag' :

							// set constant
							if ( !defined( 'CP_BLOCK' ) ) 
								define( 'CP_BLOCK', 'tag' );
						
							// filter content by <p>, <ul> and <ol> tags
							$content = $this->_filter_content( $content, 'p|ul|ol' );
							
							break;
					
					}
					
				} else {
				
					// as normal...
					
					// TO DO: check internal options, set on page/post edit screen
			
					// set constant
					if ( !defined( 'CP_BLOCK' ) ) 
						define( 'CP_BLOCK', 'tag' );
				
					// filter content by <p>, <ul> and <ol> tags
					$content = $this->_filter_content( $content, 'p|ul|ol' );
					
				}
				
			} else {
			
				// set constant
				if ( !defined( 'CP_BLOCK' ) ) 
					define( 'CP_BLOCK', 'block' );
			
				// filter content by <!--commentblock--> quicktags
				$content = $this->_filter_comment_blocks( $content );
				
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
	 * @description: retrieves minimise button
	 * @param: string $sidebar type of sidebar (comments, toc, activity)
	 * @return string $result HTML for minimise button
	 * @todo: 
	 *
	 */
	function get_minimise_button( $sidebar = 'comments' ) {
	
		// init
		$result = '';

		// if minimised is checked
		if ( $this->db->option_get( 'cp_minimise_sidebar' ) == 1 ) {
		
			// get minimise image
			$result = $this->display->get_minimise_button( $sidebar );
		
		}
		
		
		
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
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
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
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			
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
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			
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
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
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
				if ( get_post_meta( $post->ID, $key, true ) != '' ) {
				
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
		
		// get workflow
		$_workflow = $this->db->option_get( 'cp_blog_workflow' );
		
		// if it's enabled...
		if ( $_workflow == '1' ) {
		
			// notify plugins that workflow stuff needs saving
			do_action( 'cp_workflow_save_post', $post );
		
		}
		
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
	 * @description: get comments sorted by text signature and paragraph
	 * @param integer $post_ID the ID of the post
	 * @return array $_comments
	 * @todo: 
	 *
	 */
	function get_sorted_comments( $post_ID ) {
	
		// init return
		$_comments = array();
		
		
	
		// get all approved comments
		//$comments = $this->db->get_approved_comments( $post_ID );
		
		// get all comments
		$comments = $this->db->get_all_comments( $post_ID );
		
		
		
		// filter out any multipage comments not on this page
		$comments = $this->_multipage_comment_filter( $comments );
		//print_r( $comments ); die();
		
		
		
		// add all comments on the whole page
		$_comments[] = array_merge(
		
			// array of comments with no text signature (whole page)
			$this->_text_signature_filter( $comments, '' ),

			// array of orphaned comments (now assigned to the whole page)
			$this->_orphaned_comment_filter( $comments )

		);
		
		

		/* 
		we have an array of text_signatures built when the_content() was called
		this will be in the order of the paragraphs that they apply to. 
		
		HOWEVER:
		any comments whose paragraphs have signficantly changed will not be picked
		up by this process - they should have been assigned to the whole page by the
		_orphaned_comment_filter() method called above
		*/
		
		// get our signatures
		$_sigs = $this->db->get_text_sigs();

		// do we have any text signatures?
		if ( count( $_sigs ) > 0 ) {
		
			// loop through our signatures
			foreach( $_sigs AS $text_signature ) {
			
				// append comments filtered by that signature
				$_comments[] = $this->_text_signature_filter( $comments, $text_signature );
				
			}
			
		}
		
		//var_dump( $_comments );

		// --<
		return $_comments;
		
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
	 * @description: return the name of the default sidebar
	 * @return array $settings
	 * @todo:
	 */
	function get_default_sidebar() {
	
		// test for buddypress special page
		if ( $this->is_buddypress() ) {
			
			// is it a component homepage?
			if ( $this->is_buddypress_special_page() ) {
			
				return 'toc';
			
			}
			
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
		
				// is it a special page which have comments in page?
				if ( !$this->db->is_special_page() ) {
				
					// compat with Theme My Login
					if( !$this->is_theme_my_login_page() ) {
					
						// set default sidebar
						return 'comments';
						
					}
					
				}
				
			}
		
		}
		

		
		// --<
		return 'toc';
		
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
		
		// is this the back end?
		if ( is_admin() ) {
	
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
			
		// if we're in a standalone, multisite-optional scenario
		if ( CP_PLUGIN_CONTEXT == 'standard' OR CP_PLUGIN_CONTEXT == 'mu_optional' ) {
		
			// activation
			register_activation_hook( CP_PLUGIN_FILE, array( &$this, 'activate' ) );
			
			// deactivation
			register_deactivation_hook( CP_PLUGIN_FILE, array( &$this, 'deactivate' ) );
			
		} else {
		
			// multisite-forced or multisite-sitewide activation
			
			// NOTE: if a user registers a blog during the signup process, and the Commentpress
			// plugin is 'network activated' or force-activated, the plugin is activated, 
			// but register_activation_hook is not fired.
		
			// if we're in multisite-sitewide scenario
			if ( CP_PLUGIN_CONTEXT == 'mu_sitewide' ) {
			
				// sitewide -> we hook into the blog activation process? nope...
				//add_action( 'wpmu_activate_blog', array( &$this, 'activate' ) );
				
			} else {
			
				// forced -> we hook into the blog creation process. works!
				add_action( 'wpmu_new_blog', array( &$this, 'activate' ) );
				
			}
			
		}
		
		// if we're in a multisite scenario
		if ( CP_PLUGIN_CONTEXT != 'standard' ) {
		
			// add filter for signup page
			add_filter( 'after_signup_form', array( &$this, 'after_signup_form' ) );
			
			// is this multisite?
			if ( is_multisite() ) {
			
				// if subdirectory install
				if ( !is_subdomain_install() ) {
				
					// add filter for reserved commentpress special page names
					add_filter( 'subdirectory_reserved_names', array( &$this, 'add_reserved_names' ) );
					
				}
				
			}
		
		}
		
		// if BP installed...
		if ( defined( 'BP_VERSION' ) ) {
		
			// enable BuddyPress functionality
			add_action( 'bp_include', array( &$this, 'buddypress_init' ) );
			
			// add BuddyPress functionality
			add_action( 'bp_setup_globals', array( &$this, 'buddypress_globals_loaded' ) );
			
		}
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: filters the content by tag
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return string $content the parsed content
	 * @todo: 
	 *
	 */
	function _filter_content( $content, $tag = 'p|ul|ol' ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// --<
			return $content;
			
		}
		
		
		
	
		// reference our post
		global $post;



		// get our paragraphs (needed to split regex into two strings as some IDEs 
		// don't like PHP closing tags, even they are part of a regex and not actually
		// closing tags at all) 
		//preg_match_all( '/<('.$tag.')[^>]*>(.*?)(<\/('.$tag.')>)/', $content, $matches );
		preg_match_all( '#<('.$tag.')[^>]*?'.'>(.*?)</('.$tag.')>#si', $content, $matches );
		//print_r( $matches[0] ); print_r( $matches[1] ); exit();
		
		// kick out if we don't have any
		if( !count($matches[0]) ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em...
		foreach( $matches[0] AS $paragraph ) {
	  
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
			
			// get comment count
			$comment_count = $this->_text_signature_count( $post->ID, $text_signature );
			
			// add to count comment counter array
			$this->comment_counts[] = $comment_count;
			
			// get comment icon
			$commenticon = $this->display->get_icon( $comment_count, $text_signature );
			
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
	
			}

			// assign icons to paras
			$pattern = array('#<('.$tag.'[^a^r>]*)>#');
			$replace = array( $this->display->get_para_tag( $text_signature, $commenticon, $tag ) );
			$block = preg_replace( $pattern, $replace, $paragraph );
			
			// NOTE: because str_replace() has no limit to the replacements, I am switching to
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
	 * @description: filters the content by comment block
	 * @param string $content the post content
	 * @return string $content the parsed content
	 * @todo: this is probably mighty slow - review preg_replace patterns
	 *
	 */
	function _filter_comment_blocks( $content ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// reference our post
		global $post;

		
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
		
		
		
		// init our content array
		$content_array = array();
	


		// explode by <p> version to temp array
		$output_array = explode( '<p><'.'!--commentblock--></p>', $content );
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em...
		foreach( $output_array AS $paragraph ) {
		
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
				
				// get comment count
				$comment_count = $this->_text_signature_count( $post->ID, $text_signature );
				
				// add to comment counter array
				$this->comment_counts[] = $comment_count;
				
				// get comment icon
				$commenticon = $this->display->get_icon( $comment_count, $text_signature );
				
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
	 * @description: filters the content by line (<br />)
	 * @param string $content the post content
	 * @return string $content the parsed content
	 * @todo: 
	 *
	 */
	function _filter_content_by_line( $content ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// reference our post
		global $post;

		
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
		


		// init our content array
		$content_array = array();
	


		// explode by <span>
		$output_array = explode( '<span class="cp-line">', $content );
		//print_r( $output_array ); die();
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em...
		foreach( $output_array AS $paragraph ) {
		
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
					
					// get comment count
					$comment_count = $this->_text_signature_count( $post->ID, $text_signature );
					
					// add to comment counter array
					$this->comment_counts[] = $comment_count;
					
					// get comment icon
					$commenticon = $this->display->get_icon( $comment_count, $text_signature );
					
					// get comment icon markup
					$icon_html = $this->display->get_para_tag( $text_signature, $commenticon, 'span' );
					
					// assign icons to blocks
					$paragraph = $icon_html.$paragraph; //.'</div>'."\n\n\n\n";
					
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
		if ( preg_match('/<'.'!--commentblock--><br \/>/', $content, $matches) ) {
		
			// yep
			$return = true;
		
		}
		
		
		
		// look for < !--commentblock--> comment
		if ( preg_match('/<p><'.'!--commentblock--><\/p>/', $content, $matches) ) {
		
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
		$unique_words = array_unique( $words );
		
		
		
		// init sig
		$text_signature = null;
	
		// run through our unique words
		foreach( $unique_words AS $key => $word ) {
			
			// add first letter
			$text_signature .= substr( $word, 0, 1 );
			
			// limit to 254 chars
			if( $key > 254 ) { break; }
			
		}
		
		
		
		// get sig - think this through
		$sig = ($position) ? 
				$position . ':' . $text_signature : 
				$text_signature;
		
		
		
		// --<
		return $sig;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: count approved comments with a particular text signature
	 * @param integer $post_ID the ID of the post
	 * @param string $text_signature the text signature
	 * @return integer $comment_count the number of comments with a text signature in a post
	 * @todo: 
	 *
	 */
	function _text_signature_count( $post_ID, $text_signature ) {
	
		// get comments
		$comments = $this->db->get_approved_comments( $post_ID );
		
		// filter out multipage comments
		$filtered_by_page = $this->_multipage_comment_filter( $comments );
		
		// filter all but those with the text sig we want
		$filtered = $this->_text_signature_filter( $filtered_by_page, $text_signature );
		
		// count them
		$comment_count = count( $filtered );
		
		// --<
		return ( $comment_count > 0) ? $comment_count : 0;	
	
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
		if( !is_array( $comments ) ) {
		
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
	 * @description: filter comments by text signature
	 * @param array $comments array of comment objects
	 * @param string $text_signature the text signature
	 * @param integer $confidence the confidence level of paragraph identity - default 90%
	 * @return array $filtered array of comments with a text signature
	 * @todo: 
	 *
	 */
	function _text_signature_filter( $comments, $text_signature, $confidence = 90 ) {
	  
	  	// init return
		$filtered = array();

		// kick out if no comments
		if( !is_array( $comments ) ) {
		
			// --<
			return $filtered;
		}
		
		
		
		// run through our comments...
		foreach( $comments AS $comment ) {
		
			/* 
			NOTE: 
			If both strings are empty, similar_text returns a score of 0. Therefore, 
			we cannot use similar_text this to filter comments for the whole page, 
			whose signature is empty. So...
			*/
			
			// test for empty strings
			if ( $text_signature == '' ) {
			
				// test for empty comment text signature
				if ( is_null( $comment->comment_text_signature ) OR $comment->comment_text_signature == '' ) {
			
					// it's a match
					$filtered[] = $comment;
				
				}
			
			} else {
			
				// test for empty comment text signature
				if ( !is_null( $comment->comment_text_signature ) AND $comment->comment_text_signature != '' ) {
				
					// compare strings...
					similar_text( $comment->comment_text_signature, $text_signature, $score );
					
					// add to filtered array if it looks unchanged
					if( $score >= $confidence ) { $filtered[] = $comment; }
					
				}
				
			}
			
		}
		
		
	
		// --<
		return $filtered;
		
	}
	






	/** 
	 * @description: filter comments to find comments with no current paragraph
	 * @param array $comments array of comment objects
	 * @param integer $confidence the confidence level of paragraph identity - default 90%
	 * @return array $filtered array of comments with no existing text signature
	 * @todo: 
	 *
	 */
	function _orphaned_comment_filter( $comments, $confidence = 90 ) {
	  
	  	// init return
		$filtered = array();

		// kick out if no comments
		if( !is_array( $comments ) ) {
		
			// --<
			return $filtered;

		}
		
		
		
		// get post-paging global
		global $multipage; 
		
		// discard orphans for now...
		if ( $multipage ) {

			// --<
			return $filtered;

		}
		
		
		
		// get text signatures
		$_sigs = $this->db->get_text_sigs();
		
		// run through our comments...
		foreach( $comments AS $comment ) {
		
			// test for empty signature
			if ( $comment->comment_text_signature != '' ) {
			
				// init
				$matched = false;
				
				// if we have some text signatures
				if ( count ( $_sigs ) > 0 ) {
				
					// run through text_signatures
					foreach( $_sigs AS $text_signature ) {
					
						// compare strings...
						similar_text( $comment->comment_text_signature, $text_signature, $score );
						
						// flag that we found a match if we do
						if( $score >= $confidence ) { $matched = true; }
						
					}
					
				}
				
				// if we get no match, 
				if ( !$matched ) {
				
					// add to filtered array
					$filtered[] = $comment;
				
				}
				
			}
		
		}
		
		
	
		// --<
		return $filtered;
		
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
		$filepath = CP_PLUGIN_ABS_PATH . $filename;
	
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
	
	
	
	
	
	
		
//#################################################################







} // class ends






?>