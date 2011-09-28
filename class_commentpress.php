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
	
	// set the names of allowed Commentpress theme variants
	var $allowed_theme_names = array(
	
		'Commentpress Home', // the multisite home theme
		'Commentpress'       // the default Commentpress theme
	
	);
	
	// database object
	var $db;
	





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
	 * @description: checks for a valid user and redirects in GC
	 * @todo: 
	 *
	 */
	function check_user_login() {
	
		// do we have the object?
		if ( is_object( $this->auth ) ) {
		
			// check auth
			$this->auth->check_auth();
		
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
				
				// insert item in relevant menu
				$page = add_options_page(
					'Commentpress Settings', 
					'Commentpress', 
					'manage_options', 
					'cp_admin_page', 
					array( &$this, 'options_page' )
				);
				
				//print_r( $page );die();
				
				// add scripts and styles
				add_action( "admin_print_scripts-$page", array( &$this, 'admin_js' ) );
				add_action( "admin_print_styles-$page", array( &$this, 'admin_css' ) );
				add_action( "admin_head-$page", array( &$this, 'admin_head' ), 50 );
		
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
	 * @description: loads translation, if present
	 * @todo: 
	 *
	 */
	function translation() {
		
		// only use, if we have it...
		if( function_exists('load_plugin_textdomain') ) {
	
			// not used, as there are no translations as yet
			load_plugin_textdomain(
			
				'commentpress_textdomain',
				CP_PLUGIN_ABS_PATH, 
				dirname( plugin_basename(__FILE__) )
				
			);
			
		}
		
	}
	
	
	
	
	


	/** 
	 * @description: adds script libraries
	 * @todo: 
	 *
	 */
	function script_libs() {
		
		// don't include in admin
		if ( !is_admin() ) {
		
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
	
		// add plugin styles
		echo $this->display->get_styles();
	
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
			
				// set global
				define( 'CP_BLOCK', 'tag' );
			
				// filter content by <p>, <ul> and <ol> tags
				$content = $this->_filter_content( $content, 'p|ul|ol' );
				
			} else {
			
				// set global
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
	 * @param: string $sidebar type of sidebar (comments, toc, archive)
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
	 * @param: string $sidebar type of sidebar (comments, toc, archive)
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
		
		// add our meta box
		add_meta_box(
		
			'cp_page_options', 
			__( 'Commentpress Options', 'commentpress_textdomain' ), 
			array( &$this, 'custom_box' ),
			'page',
			'side'
			
		);

		
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds meta box to page edit screens
	 * @todo: 
	 *
	 */
	function custom_box() {
		
		// access post
		global $post;
		


		// Use nonce for verification
		wp_nonce_field( 'cp_page_settings', 'cp_nonce' );
		
		
		
		// --------------------------------------------------------------
		// Show or Hide Page Title
		// --------------------------------------------------------------
		
		// show a title
		echo '<p><strong><label for="cp_title_visibility">' . __("Page Title Visibility", 'commentpress_textdomain' ) . '</label></strong></p>';
		
		// set key
		$key = '_cp_title_visibility';
		
		// default to show
		$viz = $this->db->option_get( 'cp_title_visibility' );
		
		//if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
			// get it
			$viz = get_post_meta( $post->ID, $key, true );
			
		}
		
		// select
		echo '
<p>
<select id="cp_title_visibility" name="cp_title_visibility">
	<option value="show" '.(($viz == 'show') ? ' selected="selected"' : '').'>Show page title</option>
	<option value="hide" '.(($viz == 'hide') ? ' selected="selected"' : '').'>Hide page title</option>
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
			echo '<p><strong><label for="cp_number_format">' . __("Page Number Format", 'commentpress_textdomain' ) . '</label></strong></p>';
			
			// set key
			$key = '_cp_number_format';
			
			// default to arabic
			$format = 'arabic';
			
			//if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			
				// get it
				$format = get_post_meta( $post->ID, $key, true );
				
			}
			
			//print_r( $format ); die();
			
			// select
			echo '
<p>
<select id="cp_number_format" name="cp_number_format">
	<option value="arabic" '.(($format == 'arabic') ? ' selected="selected"' : '').'>Arabic numerals</option>
	<option value="roman" '.(($format == 'roman') ? ' selected="selected"' : '').'>Roman numerals</option>
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
			echo '<p><strong><label for="cp_page_layout">' . __("Page Layout", 'commentpress_textdomain' ) . '</label></strong></p>';
			
			// set key
			$key = '_cp_page_layout';
			
			// default to text
			$value = 'text';

			//if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			
				// get it
				$value = get_post_meta( $post->ID, $key, true );
				
			}
			
			// select
			echo '
<p>
<select id="cp_page_layout" name="cp_page_layout">
	<option value="text" '.(($value == 'text') ? ' selected="selected"' : '').'>Standard</option>
	<option value="wide" '.(($value == 'wide') ? ' selected="selected"' : '').'>Wide</option>
</select>
</p>
';

		}
		
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds help copy to admin page
	 * @todo: 
	 *
	 */
	function contextual_help( $text ) {
		
		$screen = $_GET['page'];
		if ($screen == 'cp_admin_page') {
		
			// test
			$text = '
<h5>Commentpress Help</h5>
<p>For further information about installing and using Commentpress, please refer to the <a href="http://www.futureofthebook.org/commentpress/support/">Commentpress support pages</a>.</p>
';
			
		}
		
		// --<
		return $text;
	
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds quicktag button to page editor
	 * @todo: 
	 *
	 */
	function commentblock_quicktag_button() {
	
		// palm off on display
		$this->display->get_custom_quicktags();
		
	}
	
	
	
	
		
		
		
	/** 
	 * @description: adds quicktag button to page editor in WP 3.3
	 * @todo: 
	 *
	 */
	function commentblock_quicktag_button_print() {
	
		// palm off on display
		$script = <<<QTAG
<script type='text/javascript'>
QTags.addButton( 'commentblock', 'c-block', '\n<!--commentblock-->\n' );
</script>
QTAG;
		
		echo $script;
		
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
	
		// store our page meta data
		$result = $this->db->save_page_meta( $post );
		
	}
	
	
	
	
	
	

	/** 
	 * @description: stores our additional param - the text signature
	 * @param integer $comment_ID the ID of the comment
	 * @param integer $comment_status the status of the comment
	 * @todo: 
	 *
	 */
	function comment_post( $comment_ID, $comment_status ) {
	
		// we don't use comment_status
	
		// store our comment signature
		$result = $this->db->save_comment_signature( $comment_ID );
		
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
		
			// loop through or signatures
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
	
		// get position in array - does not cope with multiple entries!
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
					$_link_title = 'Title Page'; $_button = 'cover'; break;
					
				case 'cp_all_comments_page': 
					$_link_title = 'All Comments'; $_button = 'allcomments'; break;
					
				case 'cp_general_comments_page': 
					$_link_title = 'General Comments'; $_button = 'general'; break;
					
				case 'cp_blog_page': 
					$_link_title = 'Blog'; $_button = 'blog'; break;
					
				case 'cp_comments_by_page': 
					$_link_title = 'Comments by Commenter'; $_button = 'members'; break;
					
				default: 
					$_link_title = 'Members'; $_button = 'members';
			
			}
			
			// show link
			$link = '<li><a href="'.$_url.'" id="btn_'.$_button.'" class="css_btn" title="'.$_link_title.'">'.$_link_title.'</a></li>'."\n";
		
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
	
		// have we tested this yet?
		if ( !isset( $this->is_allowed_theme ) ) {
		
			// assume it's not
			$this->is_allowed_theme = false;
	
			// detect theme name
			$this->theme_name = get_current_theme();

			// Is it one of our themes?
			if ( in_array( $this->theme_name, $this->allowed_theme_names ) ) {
			
				// okay
				$this->is_allowed_theme = true;
				
			} else {
			
				// is it a child theme?
				if ( is_child_theme() ) {
				
					// get theme data
					$theme_data = get_theme_data( STYLESHEETPATH.'/style.css' );
					
					// get parent theme dir
					$parent_theme = $theme_data['Template'];

					// is it a child of our theme?
					if ( $parent_theme == 'commentpress' ) {
					
						// okay
						$this->is_allowed_theme = true;
					
					}
					
				}
				
			}
			
		}
	


		// --<
		return $this->is_allowed_theme;

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
	
		// is it a commentable page?
		if ( is_single() OR is_page() OR is_attachment() ) {
		
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
	
		// test for Archive Sidebar (everything else)
		} elseif ( is_home() OR is_category() OR is_tag() OR is_day() OR is_month() OR is_year() ) {
		
			// set default sidebar
			return 'archive';
			
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
	
		// use translation
		add_action( 'init', array( &$this, 'translation' ) );
		
		// modify comment posting
		add_action( 'comment_post', array( &$this, 'comment_post' ), 10, 2 );
		
		// is this the back end?
		if ( is_admin() ) {
	
			// modify admin menu
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );
			
			// add meta boxes
			add_action( 'add_meta_boxes' , array( &$this, 'add_meta_boxes' ) );
			
			// intercept save
			add_action('save_post', array( &$this, 'save_post' ), 1, 2 );
			
			// help
			add_action( 'contextual_help', array( &$this, 'contextual_help' ) );
			
			// there's a new quicktags script in 3.3
			if ( version_compare( $wp_version, '3.3', '>=' ) ) {
				
				// comment block quicktag (NEEDS TESTING!)
				add_action('admin_print_footer_scripts', array( &$this, 'commentblock_quicktag_button_print' ), 20 );
				
			} else {
			
				// comment block quicktag
				add_action('admin_print_scripts', array( &$this, 'commentblock_quicktag_button' ) );
				
			}
			
		} else {
		
			// modify the document head
			add_filter( 'wp_head', array( &$this, 'head' ) );
			
			// add script libraries
			add_action( 'wp_print_scripts', array( &$this, 'script_libs' ) );
			
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
				add_action( 'wpmu_activate_blog', array( &$this, 'activate' ) );
				
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
		
		
		
		// run through 'em...
		foreach( $matches[0] AS $paragraph ) {
	  
			// get a signature for the paragraph
			$text_signature = $this->_generate_text_signature( $paragraph );
			
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

			// assign icons to paras
			// NOTE: temporary fix to exclude <param> and <pre> tags by excluding subsequent 'a' and 'r' chars
			// this regex needs more attention so that only <p> and <p ...> are captured
			$pattern = array('#<('.$tag.'[^a^r>]*)>#');
			$replace = array( $this->display->get_para_tag( $text_signature, $commenticon, $tag ) );
			$block = preg_replace( $pattern, $replace, $paragraph );
			
			// replace the paragraph in the original context, preserving all other content
			$content = str_replace( $paragraph, $block, $content );
			
		}
		


		// --<
		return $content;

	}
	






	/** 
	 * @description: filters the content by comment block
	 * @param string $content the post content
	 * @return string $content the parsed content
	 * @todo: 
	 *
	 */
	function _filter_comment_blocks( $content ) {
	
		// reference our post
		global $post;

		
		// wp_texturize() does an okay job with creating paragraphs, but comments tend
		// to screw things up. let's try and fix...

		// first, replace all instances of '<br />\n<!--commentblock--><br />\n' with
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

		//print_r( $content ); die();
		
		
		
		// init our content array
		$content_array = array();
	


		// explode by <p> version to temp array
		$output_array = explode( '<p><'.'!--commentblock--></p>', $content );

		// run through 'em...
		foreach( $output_array AS $paragraph ) {
		
			// is there any content?
			if ( $paragraph != '' ) {
	  
				// get a signature for the paragraph
				$text_signature = $this->_generate_text_signature( $paragraph );
				
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

		//print_r( $content_array ); die();
	

		
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
	
		// look for < !--more--> span
		if ( preg_match('/<span(.*?)?'.'><\/span><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		// look for < !--more--> span
		if ( preg_match('/<p><span(.*?)?'.'><\/span><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// look for < !--noteaser--> comment
		if ( preg_match('/<'.'!--noteaser--><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// look for < !--noteaser--> comment
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
		
		// filter
		$filtered = $this->_text_signature_filter( $comments, $text_signature );
		
		// count them
		$comment_count = count( $filtered );
		
		// --<
		return ( $comment_count > 0) ? $comment_count : 0;	
	
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
	
	
	
	
	
	
	

//#################################################################







} // class ends






?>