<?php /*
===============================================================
Class CommentPressDisplay Version 1.0
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

class CommentPressDisplay {






	/*
	===============================================================
	Properties
	===============================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	// standard mobile browser
	var $is_mobile = false;
	
	// touch-based mobile browser
	var $is_mobile_touch = false;
	
	// touch-based tablet browser
	var $is_tablet = false;
	






	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct( $parent_obj ) {
	
		// store reference to parent
		$this->parent_obj = $parent_obj;
	
		// init
		$this->_init();

		// --<
		return $this;

	}






	/**
	 * @description: PHP 4 constructor
	 */
	function CommentPressDisplay( $parent_obj ) {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct( $parent_obj );
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: if needed, sets up this object
	 * @param integer $blog_id the ID of the blog - default null
	 * @todo: work out how to assess whether the theme needs activating and how to upgrade. 
	 * more urgently: ONLY activate theme if not called from CP for Multisite until 
	 * that plugin is updated NOT to activate the theme.
	 *
	 */
	function activate( $blog_id = null ) {
	
		// get all themes
		if ( function_exists( 'wp_get_themes' ) ) {
		
			// get Commentpress theme by default, but allow overrides
			$target_theme = apply_filters(
				'cp_groupblog_theme_slug',
				'commentpress'
			);
			
			// get the theme we want
			$theme = wp_get_theme( $target_theme );
			
			// if we get it...
			if ( $theme->exists() AND $theme->is_allowed() ) {

				// activate it
				switch_theme( 
					$theme->get_template(), 
					$theme->get_stylesheet() 
				);
				
			}

		} else {
			
			// use pre-3.4 logic
			$themes = get_themes();
			//print_r( $themes ); die();
		
			// get Commentpress theme by default, but allow overrides
			// NB, the key prior to WP 3.4 is the theme's *name*
			$target_theme = apply_filters(
				'cp_forced_theme_name',
				'Commentpress'
			);
			
			// the key is the theme name
			if ( isset( $themes[ $target_theme ] ) ) {
				
				// activate it
				switch_theme(
					$themes[ $target_theme ]['Template'], 
					$themes[ $target_theme ]['Stylesheet'] 
				);
		
			}
			
		}

	}







	/** 
	 * @description: if needed, destroys this object
	 * @todo: 
	 *
	 */
	function deactivate() {
	
		// switch to default theme
		switch_theme( WP_DEFAULT_THEME, WP_DEFAULT_THEME );

	}







//#################################################################







	/*
	===============================================================
	PUBLIC METHODS
	===============================================================
	*/
	




	/** 
	 * @description: enqueue jQuery, jQuery UI and plugins
	 * @todo: 
	 *
	 */
	function get_jquery() {
	
		// default to minified scripts
		$debug_state = '';
	
		// target different scripts when debugging
		if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		
			// use uncompressed scripts
			$debug_state = '.dev';
		
		}
		
		
		
		// add our javascript plugin and dependencies
		wp_enqueue_script(
		
			'jquery_commentpress', 
			plugins_url( 'js/jquery/plugins/jquery.commentpress'.$debug_state.'.js', CP_PLUGIN_FILE ),
			array('jquery','jquery-form','jquery-ui-core','jquery-ui-resizable')
		
		);
		
		// add jQuery Scroll-To plugin
		wp_enqueue_script( 
			
			'jquery_scrollto', 
			plugins_url( 'js/jquery/plugins/jquery.scrollTo.js', CP_PLUGIN_FILE ),
			array('jquery_commentpress') 
		
		);
		
		// add jQuery Cookie plugin
		wp_enqueue_script( 
		
			'jquery_cookie', 
			plugins_url( 'js/jquery/plugins/jquery.cookie.js', CP_PLUGIN_FILE ),
			array('jquery_commentpress') 
			
		);
		
		/*
		Prior to WP3.2 (IIRC), jQuery UI has to be added separately, as the built in one was not 
		sufficiently up-to-date. This is no longer the case, so the independent jQuery UI package 
		has been removed from Commentpress in favour of the built-in one.
		*/

	}
	
	
	
	
	


	/** 
	 * @description: enqueue our quicktags script
	 * @todo: 
	 *
	 */
	function get_custom_quicktags() {
	
		// don't bother if the current user lacks permissions
		if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
			return;
		}
		
		// need access to WP version
		global $wp_version;
	
		// there's a new quicktags script in 3.3
		if ( version_compare( $wp_version, '3.2.99999', '>=' ) ) {
		
			// add our javascript script and dependencies
			wp_enqueue_script(
			
				'cp_custom_quicktags',
				plugin_dir_url( CP_PLUGIN_FILE ) . 'js/cp_quicktags_3.3.js',
				array('quicktags'),
				NULL, // no version
				true // in footer
				
			);
			
		} else {
		
			// add our javascript script and dependencies
			wp_enqueue_script(
			
				'cp_custom_quicktags',
				plugin_dir_url( CP_PLUGIN_FILE ) . 'js/cp_quicktags.js',
				array('quicktags'),
				NULL, // no version
				false // not in footer (but may need to be in WP 3.3)
				
			);
			
		}

	}
	
	
	
	
	


	/** 
	 * @description: get admin stylesheet
	 * @return string $styles
	 * @todo: 
	 *
	 */
	function get_admin_style() {
	
		// init
		$styles = '';
		
		
		// construct path to admin.css
		$filepath = get_template_directory() . '/style/css/admin.css';
	
		// is our stylesheet present?
		if ( file_exists( $filepath ) ) {
		
			// add Admin UI stylesheet
			$styles = '<!-- Commentpress Admin styles -->
<link rel="stylesheet" type="text/css" media="screen" href="'.get_template_directory_uri().'/style/css/admin.css" />
'."\n\n";

		}
			


		// --<
		return $styles;
			
	}
	
	
	
	


	/** 
	 * @description: get plugin stylesheets
	 * @return string $styles
	 * @todo: 
	 *
	 */
	function get_frontend_styles() {
		
		// add jQuery UI stylesheet -> needed for resizable columns
		wp_enqueue_style(
		
			'jquery.ui.base', 
			plugins_url( 'js/jquery/theme/jquery.ui.css', CP_PLUGIN_FILE )
			
		);
		
	}
	
	
	
	


	/** 
	 * @description: test if TinyMCE is allowed
	 * @return boolean $allowed
	 * @todo: 
	 *
	 */
	function is_tinymce_allowed() {
	
		// check option
		if ( 
		
			$this->parent_obj->db->option_exists( 'cp_comment_editor' ) AND
			$this->parent_obj->db->option_get( 'cp_comment_editor' ) != '1'
			
		) {
		
			// --<
			return false;
		
		}
		
		
		
		// don't return TinyMCE for touchscreens, mobile phones or tablets
		if ( $this->is_mobile_touch OR $this->is_mobile OR $this->is_tablet ) {
		
			// --<
			return false;
		
		}
		
		
		
		// --<
		return true;
		
	}
	
	
	
		
		
		
	/** 
	 * @description: get built-in TinyMCE scripts from Wordpress Includes directory
	 * @return string $scripts
	 * @todo: 
	 *
	 */
	function get_tinymce() {
	
		// check if we can
		if ( !$this->is_tinymce_allowed() ) {
		
			// --<
			return;
		
		}
		
		
		
		// test for wp_editor()
		if ( function_exists( 'wp_editor' ) ) {
		
			// don't include anything - this will be done in the comment form template
			return;
			
		} else {
		
			// test for WordPress version
			global $wp_version;
			
			// for WP 3.2+
			if ( version_compare( $wp_version, '3.2', '>=' ) ) {
				
				// don't need settings
				$this->_get_tinymce();
			
			} else {
			
				// get site HTTP root
				$site_http_root = trailingslashit( get_bloginfo('wpurl') );
		
				// all TinyMCE scripts
				$scripts .= '<!-- TinyMCE -->
<script type="text/javascript" src="'.$site_http_root.'wp-includes/js/tinymce/tiny_mce.js"></script>
<script type="text/javascript" src="'.$site_http_root.'wp-includes/js/tinymce/langs/wp-langs-en.js?ver=20081129"></script>
'."\n";

				// add our init
				$scripts .= $this->_get_tinymce_init();
				
				// out to browser
				echo $scripts;
				
			}
			
		}

	}
	
	
	
	
	


	/** 
	 * @description: get javascript for the plugin, context dependent
	 * @return string $script
	 * @todo: 
	 *
	 */
	function get_javascript() {

		// base url for the Commentpress parent theme
		$_base = trailingslashit( get_template_directory_uri() );
		
		// test whether we can find the scripts (ie, is this a true CP theme?)
		$common = locate_template( array( 'style/js/cp_js_common.js' ), false );
		
		// well?
		if ( $common AND file_exists( $common ) ) {
		
			// default to minified scripts
			$debug_state = '';
		
			// target different scripts when debugging
			if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
			
				// use uncompressed scripts
				$debug_state = '.dev';
			
			}
			
			
			
			// enqueue common js
			wp_enqueue_script(
			
				'cp_common', 
				$_base.'style/js/cp_js_common'.$debug_state.'.js', 
				array('jquery_commentpress')
			
			);
			
			// test for buddypress special page
			if ( $this->parent_obj->is_buddypress() AND $this->parent_obj->is_buddypress_special_page() ) {
			
				// skip custom addComment
			
			} else {
				
				// enqueue form js
				wp_enqueue_script(
				
					'cp_form', 
					$_base.'style/js/cp_js_form'.$debug_state.'.js', 
					array('cp_common')
				
				);
					
			}
				
			// test for commentpress special page
			if ( $this->parent_obj->db->is_special_page() ) {
			
				// enqueue accordion-like js
				wp_enqueue_script(
				
					'cp_special', 
					$_base.'style/js/cp_js_all_comments.js', 
					array('cp_form')
				
				);
					
			}
				
			// is this a CPT?
			//$current_type = get_post_type();
			//print_r( $current_type ); die();
			
			// get vars
			$vars = $this->parent_obj->db->get_javascript_vars();
			
			// get vars
			$this->localise_js( $vars, 'cp_common' );
			
		}
		
	}
	
	
	
	
	
	

	/** 
	 * @description: get help text
	 * @return HTML $help
	 * @todo: translation
	 *
	 */
	function get_help() {
	
		$help = <<<HELPTEXT
<p>For further information about using Commentpress, please refer to the <a href="http://www.futureofthebook.org/commentpress/support/">Commentpress support pages</a> or use one of the links below:</p>

<ul>
<li><a href="http://www.futureofthebook.org/commentpress/support/structuring-your-document/">Structuring your Document</a></li>
<li><a href="http://www.futureofthebook.org/commentpress/support/formatting-your-document/">Formatting Your Document</a></li>
<li><a href="http://www.futureofthebook.org/commentpress/support/using-commentpress/">How to read a Commentpress document</a></li>
</ul>
HELPTEXT;

		// --<
		return $help;

	}
	
	
	
	
	
	

	/** 
	 * @description: construct a javascript for inclusion in the HTML of the page
	 * @param array $vars array of variables and their values
	 * @param string $comment description for reference
	 * @return string $js
	 * @todo: 
	 *
	 */
	function localise_js( $vars = array(), $script_ref ) {
	
		// use wp function
		wp_localize_script( $script_ref, 'CommentpressSettings', $vars );
		
	}
	
	
	
	
	


	/** 
	 * @description: show the posts and their comment count in a list format
	 * @todo:
	 *
	 */
	function list_posts( $params = 'numberposts=-1&order=DESC' ) {
	
		// get all posts
		$posts = get_posts( $params );
		
		// have we set the option?
		$list_style = $this->parent_obj->db->option_get('cp_show_extended_toc');
		//print_r( $list_style ); die();
		
		// if not set or set to 'off'
		if ( $list_style === false OR $list_style == '0' ) {
		
			// --------------------------
			// old-style undecorated list
			// --------------------------
		
			// run through them...
			foreach( $posts AS $item ) {
		
				// get comment count for that post
				$count = count( $this->parent_obj->db->get_approved_comments( $item->ID ) );
		
				// write list item
				echo '<li class="title"><a href="'.get_permalink( $item->ID ).'">'.get_the_title( $item->ID ).' ('.$count.')</a></li>'."\n";
			
			}
			
		} else {
	
			// ------------------------
			// new-style decorated list
			// ------------------------
		
			// run through them...
			foreach( $posts AS $item ) {
			
				// init output
				$_html = '';
			
				//print_r( $item ); die();
				//setup_postdata( $item );
		
				// get comment count for that post
				$count = count( $this->parent_obj->db->get_approved_comments( $item->ID ) );
				
				// compat with Co-Authors Plus
				if ( function_exists( 'get_coauthors' ) ) {
				
					// get multiple authors
					$authors = get_coauthors( $item->ID );
					//print_r( $authors ); die();
					
					// if we get some
					if ( !empty( $authors ) ) {
					
						// use the Co-Authors format of "name, name, name & name"
						$author_html = '';
						
						// init counter
						$n = 1;
						
						// find out how many author we have
						$author_count = count( $authors );
					
						// loop
						foreach( $authors AS $author ) {
							
							// default to comma
							$sep = ', ';
							
							// if we're on the penultimate
							if ( $n == ($author_count - 1) ) {
							
								// use ampersand
								$sep = __( ' &amp; ', 'commentpress-theme' );
								
							}
							
							// if we're on the last, don't add
							if ( $n == $author_count ) { $sep = ''; }
							
							// get name
							$author_html .= $this->echo_post_author( $author->ID, false );
							
							// and separator
							$author_html .= $sep;
							
							// increment
							$n++;
							
							// are we showing avatars?
							if ( get_option( 'show_avatars' ) ) {
							
								// get avatar
								$_html .= get_avatar( $author->ID, $size='32' );
								
							}
								
						}
						
						// add citation
						$_html .= '<cite class="fn">'.$author_html.'</cite>'."\n";
						
						// add permalink
						$_html .= '<p class="post_activity_date">'.get_the_time('l, F jS, Y', $item->ID).'</p>'."\n";
							
					}
				
				} else {
				
					// get avatar
					$author_id = $item->post_author;

					// are we showing avatars?
					if ( get_option( 'show_avatars' ) ) {
					
						$_html .= get_avatar( $author_id, $size='32' );
						
					}
					
					// add citation
					$_html .= '<cite class="fn">'.$this->echo_post_author( $author_id, false ).'</cite>';
					
					// add permalink
					$_html .= '<p class="post_activity_date">'.get_the_time('l, F jS, Y', $item->ID).'</p>';
					
				}
					
				// write list item
				echo '<li class="title">
				<div class="post-identifier">
				'.$_html.'
				</div>
				<a href="'.get_permalink( $item->ID ).'" class="post_activity_link">'.get_the_title( $item->ID ).' ('.$count.')</a>
				</li>'."\n";
			
			}
		
		}
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: show username (with link)
	 * @todo: remove from theme functions.php?
	 *
	 */
	function echo_post_author( $author_id, $echo = true ) {
	
		// get author details
		$user = get_userdata( $author_id );
		
		// kick out if we don't have a user with that ID
		if ( !is_object( $user ) ) { return; }
		
		
		
		// access plugin
		global $commentpress_obj, $post;
	
		// if we have the plugin enabled and it's BP
		if ( is_object( $post ) AND is_object( $commentpress_obj ) AND $commentpress_obj->is_buddypress() ) {
		
			// construct user link
			$author = bp_core_get_userlink( $user->ID );
	
		} else {
		
			// link to theme's author page
			$link = sprintf(
				'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
				get_author_posts_url( $user->ID, $user->user_nicename ),
				esc_attr( sprintf( __( 'Posts by %s' ), $user->display_name ) ),
				esc_html( $user->display_name )
			);
			$author = apply_filters( 'the_author_posts_link', $link );
	
		}
		
		// if we're echoing
		if ( $echo ) { 
			echo $author;
		} else {
			return $author;
		}
			
	}
	
	
	
	
	
	
	/** 
	 * @description: print the posts and their comment count in a list format
	 * @todo:
	 *
	 */
	function list_pages() {
	
		/* 
		Question: do we want to use WP menus? And if so, how?
		
		Currently, we're using wp_list_pages(), so let's try wp_page_menu() first
		
		
		
		// If we set the theme to use wp_nav_menu(), we need to register it
		register_nav_menu( 'primary', __( 'Primary Menu', 'twentyeleven' ) );
	
		Our navigation menu. If one isn't filled out, wp_nav_menu falls back to 
		wp_page_menu. The menu assiged to the primary position is the one used. If 
		none is assigned, the menu with the lowest ID is used. 

		//wp_nav_menu( array( 'theme_location' => 'primary' ) );
		
		// set list pages defaults
		$args = array(

			'sort_column' => 'menu_order, post_title',
			'menu_class' => 'menu',
			'include' => '',
			'exclude' => '',
			'echo' => true,
			'show_home' => false,
			'link_before' => '',
			'link_after' => ''

		);
		*/
		
		
		
		// test for custom menu
		if ( has_nav_menu( 'toc' ) ) {
		
			// try and use it
			wp_nav_menu( array( 
				
				'theme_location' => 'toc',
				'echo' => true,
				'container' => '',
				'items_wrap' => '%3$s',
				
			) );
			
			return;
		
		}
		
		
		
		// get welcome page ID
		$welcome_id = $this->parent_obj->db->option_get( 'cp_welcome_page' );
		
		// get front page
		$page_on_front = $this->parent_obj->db->option_wp_get( 'page_on_front' );
		
		// print link to title page, if we have one and it's the front page
		if ( $welcome_id !== false AND $page_on_front == $welcome_id ) {
		
			// define title page
			$title_page_title = get_the_title( $welcome_id );
		
			// allow overrides
			$title_page_title = apply_filters( 'cp_title_page_title', $title_page_title );
		
			// echo list item
			echo '<li class="page_item page-item-'.$welcome_id.'"><a href="'.get_permalink( $welcome_id ).'">'.$title_page_title.'</a></li>';
		
		}
		


		// get page display option
		//$depth = $this->parent_obj->db->option_get('cp_show_subpages');
		
		// ALWAYS write subpages into page, even if they aren't displayed
		$depth = 0;
		
		

		// get pages to exclude
		$exclude = $this->parent_obj->db->option_get('cp_special_pages');
		
		// do we have any?
		if ( !$exclude ) { $exclude = array(); }
		
		// exclude title page, if we have one
		if ( $welcome_id !== false ) { $exclude[] = $welcome_id; }


		// set list pages defaults
		$defaults = array(
		
			'depth' => $depth,
			'show_date' => '',
			'date_format' => $this->parent_obj->db->option_get('date_format'),
			'child_of' => 0,
			'exclude' => implode( ',', $exclude ),
			'title_li' => '',
			'echo' => 1,
			'authors' => '',
			'sort_column' => 'menu_order, post_title',
			'link_before' => '',
			'link_after' => '',
			'exclude_tree' => ''
		
		);
		
		// use Wordpress function to echo
		wp_list_pages( $defaults );


		
		
		/*
		
		// The following code manually lists pages, but adds the comment count to the name
		
		// init params
		$params = 'sort_column=menu_order';
		
		// exclude the special pages
		$params .= '&exclude='.implode( ',', $special );
		
		//echo $params; exit();
	
		// get all pages
		$_pages = get_pages( $params );
		
		// run through them...
		foreach( $_pages AS $_page ) {
	
			// get comment count for that page
			$count = count( $this->parent_obj->db->get_approved_comments( $_page->ID ) );
	
			// write list item
			echo '<li class="title"><a href="'.get_page_link( $_page->ID ).'">'.$_page->post_title.' ('.$count.')</a></li>'."\n";
		
		}
		*/

	}
	
	
	
	
	
	
	
	/** 
	 * @description: get the block icons
	 * @param integer $comment_count number of comments
	 * @param string $text_signature comment text signature
	 * @param string $block_type either 'auto', 'line' or 'block'
	 * @param integer $para_num sequnetial commentable block number
	 * @return string $comment_icon
	 * @todo: 
	 *
	 */
	function get_icon( $comment_count, $text_signature, $block_type = 'auto', $para_num = 1 ) {
	
		// reset icon
		$icon = null;

		// if we have no comments...
		if( $comment_count == 0 ) {
			
			// show add comment icon
			$icon = 'comment_add.png';
			$class = ' no_comments';
			
		} elseif( $comment_count > 0 ) {
		
			// show comments present icon
			$icon = 'comment.png';
			$class = ' has_comments';
			
		}
		
		// define block title by block type
		switch ( $block_type ) {
			
			// ----------------------------
			// auto-formatted
			// ----------------------------
			case 'auto':
			default:

				// define title text
				$title_text = sprintf( _n(
					
					// singular
					'There is %d comment written for this paragraph', 
					
					// plural
					'There are %d comments written for this paragraph', 
					
					// number
					$comment_count, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $comment_count );
				
				// define permalink text
				$permalink_text = sprintf( _n(
					
					// singular
					'Permalink for paragraph %d', 
					
					// plural
					'Permalink for paragraph %d', 
					
					// number
					$para_num, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $para_num );
				
				// define add comment text
				$add_text = sprintf( _n(
					
					// singular
					'Leave a comment on paragraph %d', 
					
					// plural
					'Leave a comment on paragraph %d', 
					
					// number
					$para_num, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $para_num );
				
				// define paragraph marker
				$para_marker = '<span class="para_marker"><a id="'.$text_signature.'" href="#'.$text_signature.'" title="'.$permalink_text.'">&para; <span>'.(string) $para_num.'</span></a></span>';
				
				break;
			
			// ----------------------------
			// line-by-line, eg poetry
			// ----------------------------
			case 'line':

				// define title text
				$title_text = sprintf( _n(
					
					// singular
					'There is %d comment written for this line', 
					
					// plural
					'There are %d comments written for this line', 
					
					// number
					$comment_count, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $comment_count );
				
				// define permalink text
				$permalink_text = sprintf( _n(
					
					// singular
					'Permalink for line %d', 
					
					// plural
					'Permalink for line %d', 
					
					// number
					$para_num, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $para_num );
				
				// define add comment text
				$add_text = sprintf( _n(
					
					// singular
					'Leave a comment on line %d', 
					
					// plural
					'Leave a comment on line %d', 
					
					// number
					$para_num, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $para_num );
				
				// define paragraph marker
				$para_marker = '<span class="para_marker"><a id="'.$text_signature.'" href="#'.$text_signature.'" title="'.$permalink_text.'">&para; <span>'.(string) $para_num.'</span></a></span>';
				
				break;
			

			// ----------------------------
			// comment-blocks
			// ----------------------------
			case 'block':

				// define title text
				$title_text = sprintf( _n(
					
					// singular
					'There is %d comment written for this block', 
					
					// plural
					'There are %d comments written for this block', 
					
					// number
					$comment_count, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $comment_count );
				
				// define permalink text
				$permalink_text = sprintf( _n(
					
					// singular
					'Permalink for block %d', 
					
					// plural
					'Permalink for block %d', 
					
					// number
					$para_num, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $para_num );
				
				// define add comment text
				$add_text = sprintf( _n(
					
					// singular
					'Leave a comment on block %d', 
					
					// plural
					'Leave a comment on block %d', 
					
					// number
					$para_num, 
					
					// domain
					'commentpress-plugin'
				
				// substitution
				), $para_num );
				
				// define paragraph marker
				$para_marker = '<span class="para_marker"><a id="'.$text_signature.'" href="#'.$text_signature.'" title="'.$permalink_text.'">&para; <span>'.(string) $para_num.'</span></a></span>';
				
				break;
		
		}
		
		// define small
		$small = '<small class="comment_count" title="'.$title_text.'">'.(string) $comment_count.'</small>';
		
		// define HTML for comment icon
		$comment_icon = $para_marker.'<span class="commenticonbox"><a class="para_permalink'.$class.'" href="#'.$text_signature.'" title="'.$add_text.'">'.$add_text.'</a> '.$small.'</span>'."\n";
		
		
		
		// --<
		return $comment_icon;
		
	}
	
	
	
	
	

	/** 
	 * @description: get the content comment icon tag
	 * @param string $text_signature comment text signature
	 * @return string $para_tag
	 * @todo: 
	 *
	 */
	function get_para_tag( $text_signature, $commenticon, $tag = 'p' ) {
	
		// return different stuff for different tags
		switch( $tag ) {
		
			case 'ul':
		
				// define list tag
				$para_tag = '<'.$tag.' class="textblock" id="textblock-'.$text_signature.'">'.
							'<li class="list_commenticon">'.$commenticon.'</li>'; 
				break;
							
			case 'ol':
		
				// define list tag
				$para_tag = '<'.$tag.' class="textblock" id="textblock-'.$text_signature.'" start="0">'.
							'<li class="list_commenticon">'.$commenticon.'</li>'; 
				break;
			
			// compat with WP Footnotes
			case 'ol class="footnotes"':
		
				// define list tag
				$para_tag = '<ol class="footnotes textblock" id="textblock-'.$text_signature.'" start="0">'.
							'<li class="list_commenticon">'.$commenticon.'</li>'; 
				break;
							
			case 'p':
			case 'p style="text-align:left"':
			case 'p style="text-align:left;"':
		
				// define para tag
				$para_tag = '<'.$tag.' class="textblock" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
			case 'p style="text-align:right"':
			case 'p style="text-align:right;"':
		
				// define para tag
				$para_tag = '<'.$tag.' class="textblock textblock-right" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
			case 'p style="text-align:center"':
			case 'p style="text-align:center;"':
		
				// define para tag
				$para_tag = '<'.$tag.' class="textblock textblock-center" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
			case 'p style="text-align:justify"':
			case 'p style="text-align:justify;"':
		
				// define para tag
				$para_tag = '<'.$tag.' class="textblock textblock-justify" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
			case 'p class="notes"':
		
				// define para tag
				$para_tag = '<p class="notes textblock" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
			case 'div':
		
				// define opening tag (we'll close it later)
				$para_tag = '<div class="textblock" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
			case 'span':
		
				// define opening tag (we'll close it later)
				$para_tag = '<span class="textblock" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
		}
	

		
		/*
		print_r( array( 
		
			't' => $text_signature,
			'p' => $para_tag 
		
		) );
		*/



		// --<
		return $para_tag;
		
	}
	
	
	
	
	



	/** 
	 * @description: get the text signature input for the comment form
	 * @param string $text_sig comment text signature
	 * @return string $input
	 * @todo: 
	 *
	 */
	function get_signature_input( $text_sig = '' ) {
	
		// define input tag
		$input = '<input type="hidden" name="text_signature" value="'.$text_sig.'" id="text_signature" />';
		
		// --<
		return $input;
		
	}
	
	
	
	
	



	/** 
	 * @description: get the minimise all button
	 * @param: string $sidebar type of sidebar (comments, toc, activity)
	 * @return string $tag
	 * @todo: 
	 *
	 */
	function get_minimise_all_button( $sidebar = 'comments' ) {
	
		switch( $sidebar ) {
	
			case 'comments':
				// define minimise button
				$tag = '<span id="cp_minimise_all_comments" title="'.__( 'Minimise all Comment Sections', 'commentpress-plugin' ).'"></span>';
				break;
			
			case 'activity':
				// define minimise button
				$tag = '<span id="cp_minimise_all_activity" title="'.__( 'Minimise all Activity Sections', 'commentpress-plugin' ).'"></span>';
				break;
			
		}
		
		// --<
		return $tag;
		
	}
	
	
	
	
	



	/** 
	 * @description: get the header minimise button
	 * @return string $tag
	 * @todo: 
	 *
	 */
	function get_header_min_link() {
	
		// define minimise button
		$link = '<li><a href="#" id="btn_header_min" class="css_btn" title="'.__( 'Minimise Header', 'commentpress-plugin' ).'">'.__( 'Minimise Header', 'commentpress-plugin' ).'</a></li>'."\n";
		
		
		// --<
		return $link;
		
	}
	
	
	
	
	



	/** 
	 * @description: get an image wrapped in a link
	 * @param: string $src location of image file
	 * @param: string $url link target
	 * @return string $tag
	 * @todo: 
	 *
	 */
	function get_linked_image( $src = '', $url = '' ) {
	
		// init html
		$html = '';
	
		// do we have an image?
		if ( $src != '' ) {
	
			// construct link
			$html .= '<img src="'.$src.'" />';
		
		}

		// do we have one?
		if ( $url != '' ) {
	
			// construct link around image
			$html .= '<a href="'.$url.'">'.$html.'</a>';
			
		}
		
		
		
		// --<
		return $html;
		
	}
	
	
	
	
	



	/** 
	 * @description: got the Wordpress admin page
	 * @return string $admin_page
	 * @todo: 
	 *
	 */
	function get_admin_page() {
	
		// init
		$admin_page = '';
		
		
		
		// open div
		$admin_page .= '<div class="wrap" id="cp_admin_wrapper">'."\n\n";
	
		// get our form
		$admin_page .= $this->_get_admin_form();
		
		// close div
		$admin_page .= '</div>'."\n\n";
		
		
		
		// --<
		return $admin_page;
		
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
	
		// test for mobile phone user agent
		$this->_test_for_mobile();
		
	}







	/** 
	 * @description: returns the admin form HTML
	 * @return string $admin_page
	 * @todo: translation
	 *
	 */
	function _get_admin_form() {
	
		// sanitise admin page url
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( $url_array ) { $url = $url_array[0]; }



		// if we need to upgrade...
		if ( $this->parent_obj->db->check_upgrade() ) {
		
			// get upgrade options
			$upgrade = $this->_get_upgrade();
			
			// init text
			$options_text = '';
			
			// if there are options
			if ( $upgrade != '' ) {
				
				$options_text = ' The following options have become available in the new version.';
				
			}
			
			// define admin page
			$admin_page = '
<div class="icon32" id="icon-options-general"><br/></div>

<h2>Commentpress Upgrade</h2>



<form method="post" action="'.htmlentities($url.'&updated=true').'">

'.wp_nonce_field( 'cp_admin_action', 'cp_nonce', true, false ).'
'.wp_referer_field( false ).'
<input id="cp_upgrade" name="cp_upgrade" value="1" type="hidden" /></td>



<h3>Please upgrade Commentpress</h3>

<p>It looks like you are running an older version of Commentpress.'.$options_text.'</p>



<table class="form-table">

'.$upgrade.'

</table>



'.
	


'<input type="hidden" name="action" value="update" />



<p class="submit">
	<input type="submit" name="cp_submit" value="Upgrade" class="button-primary" />
</p>
				


</form>'."\n\n\n\n";

		} else {
		
			// define admin page
			$admin_page = '
<div class="icon32" id="icon-options-general"><br/></div>

<h2>Commentpress Settings</h2>



<form method="post" action="'.htmlentities($url.'&updated=true').'">

'.wp_nonce_field( 'cp_admin_action', 'cp_nonce', true, false ).'
'.wp_referer_field( false ).'



'.

$this->_get_options().



'<input type="hidden" name="action" value="update" />



'.$this->_get_submit().'

</form>'."\n\n\n\n";

		}
		
		
		
		// --<
		return $admin_page;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the Commentpress theme options for the admin form
	 * @return string $options
	 * @todo: 
	 *
	 */
	function _get_options() {
	
		// define Commentpress theme options
		$options = '
<h3>Options for the Commentpress Theme</h3>

<p>When the special Commentpress theme is active, the following options modify its behaviour.</p>



'.$this->_get_db_mod().'



<h4>Global Options</h4>

<table class="form-table">

'.$this->_get_reset().'

'.$this->_get_override().'

'.$this->_get_editor().'

	<tr valign="top">
		<th scope="row"><label for="cp_title_visibility">Default page title visibility (can be overridden on individual pages)</label></th>
		<td><select id="cp_title_visibility" name="cp_title_visibility">
				<option value="show" '.(($this->parent_obj->db->option_get('cp_title_visibility') == 'show') ? ' selected="selected"' : '').'>Show page titles</option>
				<option value="hide" '.(($this->parent_obj->db->option_get('cp_title_visibility') == 'hide') ? ' selected="selected"' : '').'>Hide page titles</option>
			</select>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cp_page_meta_visibility">Default page meta visibility (can be overridden on individual pages)</label></th>
		<td><select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
				<option value="show" '.(($this->parent_obj->db->option_get('cp_page_meta_visibility') == 'show') ? ' selected="selected"' : '').'>Show page meta</option>
				<option value="hide" '.(($this->parent_obj->db->option_get('cp_page_meta_visibility') == 'hide') ? ' selected="selected"' : '').'>Hide page meta</option>
			</select>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cp_minimise_sidebar">Allow Sidebar to be minimized</label></th>
		<td><input id="cp_minimise_sidebar" name="cp_minimise_sidebar" value="1" type="checkbox" '.( $this->parent_obj->db->option_get('cp_minimise_sidebar') ? ' checked="checked"' : ''  ).' /></td>
	</tr>

'.$this->_get_optional_options().'

</table>



<h4>Theme Customisation</h4>

<p>You can set a custom background colour in <em>Appearance &#8594; Background</em>.<br />
You can also set a custom header image and header text colour in <em>Appearance &#8594; Header</em>.<br />
Below are extra options for changing how the theme looks.</p>

<table class="form-table">

	<tr valign="top" id="cp_header_bg_colour-row">
		<th scope="row"><label for="cp_header_bg_colour">Header Background Colour</label></th>
		<td><input type="text" name="cp_header_bg_colour" id="cp_header_bg_colour" value="'.$this->parent_obj->db->option_get('cp_header_bg_colour').'" /><span class="description hide-if-js">If you want to hide header text, add <strong>#blank</strong> as text colour.</span><input type="button" class="button hide-if-no-js" value="Select a Colour" id="pickcolor" /><div id="color-picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_js_scroll_speed">Scroll speed</label></th>
		<td><input type="text" id="cp_js_scroll_speed" name="cp_js_scroll_speed" value="'.$this->parent_obj->db->option_get('cp_js_scroll_speed').'" class="small-text" /> milliseconds</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cp_min_page_width">Minimum page width</label></th>
		<td><input type="text" id="cp_min_page_width" name="cp_min_page_width" value="'.$this->parent_obj->db->option_get('cp_min_page_width').'" class="small-text" /> pixels</td>
	</tr>

</table>



<h4>Table of Contents</h4>

<p>Choose how you want your Table of Contents to appear and function.<br />
<strong style="color: red;">NOTE!</strong> When Chapters are Pages, the TOC will always show Sub-Pages, since collapsing the TOC makes no sense in that situation.</p>

<table class="form-table">

'.$this->_get_toc().'

</table>



<h4>Sidebar</h4>

<p>Choose how you want your Sidebar to appear.</p>

<table class="form-table">

'.$this->_get_sidebar().'

</table>



<h4>Blog</h4>

<p>Options for the blog.</p>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cp_excerpt_length">Excerpt length</label></th>
		<td><input type="text" id="cp_excerpt_length" name="cp_excerpt_length" value="'.$this->parent_obj->db->option_get('cp_excerpt_length').'" class="small-text" /> words</td>
	</tr>

</table>


';
	
		

		// --<
		return $options;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns either the install or uninstall button
	 * @return string $reset
	 * @todo: assess whether we need this
	 *
	 */
	function _get_db_mod() {
	
		// do we have comment_text_signature in our comments table?
		if ( $this->parent_obj->db->db_is_modified() ) {
		
			// yes -> show uninstall
			//return $this->_get_uninstall();
		
		} else {
		
			// no -> show install
			//return $this->_get_install();
		
		}
	
	}
	
	
	
	
	



	/** 
	 * @description: returns optional options, if defined
	 * @return string $html
	 * @todo: 
	 *
	 */
	function _get_optional_options() {
	
		// init
		$html = '';
	
	
	
		// do we have the option to choose blog type (new in 3.3.1)?
		if ( $this->parent_obj->db->option_exists('cp_blog_type') ) {
		
			// define no types
			$types = array();
			
			// allow overrides
			$types = apply_filters( 'cp_blog_type_options', $types );
			
			// if we get some from a plugin, say...
			if ( !empty( $types ) ) {
			
				// define title
				$type_title = __( 'Blog Type', 'commentpress-plugin' );
			
				// allow overrides
				$type_title = apply_filters( 'cp_blog_type_label', $type_title );
			
				// construct options
				$type_option_list = array();
				$n = 0;
				
				// get existing
				$blog_type = $this->parent_obj->db->option_get('cp_blog_type');
				
				foreach( $types AS $type ) {
					if ( $n == $blog_type ) {
						$type_option_list[] = '<option value="'.$n.'" selected="selected">'.$type.'</option>';
					} else {
						$type_option_list[] = '<option value="'.$n.'">'.$type.'</option>';
					}
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );
				
				
				
				// define upgrade
				$html .= '
	<tr valign="top">
		<th scope="row"><label for="cp_blog_type">'.$type_title.'</label></th>
		<td><select id="cp_blog_type" name="cp_blog_type">
				'.$type_options.'
			</select>
		</td>
	</tr>

';

			}

		}
		

		
		// do we have the option to choose blog workflow (new in 3.3.1)?
		if ( $this->parent_obj->db->option_exists('cp_blog_workflow') ) {
		
			// off by default
			$has_workflow = false;
		
			// allow overrides
			$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );
			
			// if we have workflow enabled, by a plugin, say...
			if ( $has_workflow !== false ) {
			
				// define label
				$workflow_label = __( 'Enable Custom Workflow', 'commentpress-plugin' );
			
				// define label
				$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );
			
				// define upgrade
				$html .= '
	<tr valign="top">
		<th scope="row"><label for="cp_blog_workflow">'.$workflow_label.'</label></th>
		<td><input id="cp_blog_workflow" name="cp_blog_workflow" value="1" type="checkbox" '.( $this->parent_obj->db->option_get('cp_blog_workflow') ? ' checked="checked"' : ''  ).' /></td>

	</tr>

';

			}

		}
		
		
		
		// --<
		return $html;
		
	}
	
	
	
	
	
		

		
	/** 
	 * @description: returns the install button for the admin form
	 * @return string $reset
	 * @todo: 
	 *
	 */
	function _get_install() {
	
		// define install
		$install = '
<h4>Install Commentpress</h4>

<p><strong style="color: red;">WARNING!</strong> This option modifies your comments table by adding a field to store the linkage between a comment and a paragraph. Use only when other install methods have failed.</p>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cp_install">Install Commentpress database modifications</label></th>
		<td><input id="cp_install" name="cp_install" value="1" type="checkbox" /></td>
	</tr>

</table>



';		
		
		
		// --<
		return $install;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the uninstall button for the admin form
	 * @return string $reset
	 * @todo: 
	 *
	 */
	function _get_uninstall() {
	
		// define uninstall
		$uninstall = '
<h4>Uninstall Commentpress</h4>

<p><strong style="color: red;">WARNING!</strong> Only use this option if you are intending to completely remove Commentpress from your Wordpress installation. Selecting this option will erase all links between comments and the paragraphs that they have been submitted to. The comments themselves will not be deleted, but they will only apply to pages or posts. Complete the uninstall process by deactivating the plugin.</p>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cp_uninstall">Uninstall Commentpress database modifications</label></th>
		<td><input id="cp_uninstall" name="cp_uninstall" value="1" type="checkbox" /></td>
	</tr>

</table>



';		
		
		
		// --<
		return $uninstall;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the upgrade details for the admin form
	 * @return string $upgrade
	 * @todo: 
	 *
	 */
	function _get_upgrade() {
		
		// init
		$upgrade = '';
		
		
		
		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( !$this->parent_obj->db->option_exists('cp_sidebar_default') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_sidebar_default">Which sidebar do you want to be active by default? (can be overridden on individual pages)</label></th>
		<td><select id="cp_sidebar_default" name="cp_sidebar_default">
				<option value="toc">Contents</option>
				<option value="activity">Activity</option>
				<option value="comments" selected="selected">Comments</option>
			</select>
		</td>
	</tr>

';

		}
		

		
		// do we have the option to show or hide page meta (new in 3.3.2)?
		if ( !$this->parent_obj->db->option_exists('cp_page_meta_visibility') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_page_meta_visibility">Show or hide page meta by default</label></th>
		<td><select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
				<option value="show">Show page meta</option>
				<option value="hide" selected="selected">Hide page meta</option>
			</select>
		</td>
	</tr>
';

		}
		

		
		// do we have the option to choose blog type (new in 3.3.1)?
		if ( !$this->parent_obj->db->option_exists('cp_blog_type') ) {
		
			// define no types
			$types = array();
			
			// allow overrides
			$types = apply_filters( 'cp_blog_type_options', $types );
			
			// if we get some from a plugin, say...
			if ( !empty( $types ) ) {
			
				// define title
				$type_title = __( 'Blog Type', 'commentpress-plugin' );
			
				// allow overrides
				$type_title = apply_filters( 'cp_blog_type_label', $type_title );
			
				// construct options
				$type_option_list = array();
				$n = 0;
				foreach( $types AS $type ) {
					$type_option_list[] = '<option value="'.$n.'">'.$type.'</option>';
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );
				
				
				
				// define upgrade
				$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_blog_type">'.$type_title.'</label></th>
		<td><select id="cp_blog_type" name="cp_blog_type">
				'.$type_options.'
			</select>
		</td>
	</tr>

';

			}

		}
		

		
		// do we have the option to choose blog workflow (new in 3.3.1)?
		if ( !$this->parent_obj->db->option_exists('cp_blog_workflow') ) {
		
			// off by default
			$has_workflow = false;
		
			// allow overrides
			$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );
			
			// if we have workflow enabled, by a plugin, say...
			if ( $has_workflow !== false ) {
			
				// define label
				$workflow_label = __( 'Enable Custom Workflow', 'commentpress-plugin' );
			
				// define label
				$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );
			
				// define upgrade
				$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_blog_workflow">'.$workflow_label.'</label></th>
		<td><input id="cp_reset" name="cp_blog_workflow" value="1" type="checkbox" /></td>
	</tr>

';

			}

		}
		

		
		// do we have the option to choose the TOC layout (new in 3.3)?
		if ( !$this->parent_obj->db->option_exists('cp_show_extended_toc') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_show_extended_toc">Appearance of TOC for posts</label></th>
		<td><select id="cp_show_extended_toc" name="cp_show_extended_toc">
				<option value="1">Show extended information</option>
				<option value="0" selected="selected">Just title</option>
			</select>
		</td>
	</tr>

';

		}
		

		
		// do we have the option to set the comment editor?
		if ( !$this->parent_obj->db->option_exists('cp_comment_editor') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_reset">Comment form editor</label></th>
		<td><select id="cp_comment_editor" name="cp_comment_editor">
				<option value="1" selected="selected">Rich-text Editor</option>
				<option value="0">Plain-text Editor</option>
			</select>
		</td>
	</tr>
';
		
		}
		

		
		// do we have the option to set the default behaviour?
		if ( !$this->parent_obj->db->option_exists('cp_promote_reading') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_promote_reading">Default comment form behaviour</label></th>
		<td><select id="cp_promote_reading" name="cp_promote_reading">
				<option value="1">Promote reading</option>
				<option value="0" selected="selected">Promote commenting</option>
			</select>
		</td>
	</tr>
';

		}
		

		
		// do we have the option to show or hide titles?
		if ( !$this->parent_obj->db->option_exists('cp_title_visibility') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_title_visibility">Show or hide page titles by default</label></th>
		<td><select id="cp_title_visibility" name="cp_title_visibility">
				<option value="show" selected="selected">Show page titles</option>
				<option value="hide">Hide page titles</option>
			</select>
		</td>
	</tr>
';

		}
		

		
		// do we have the option to set the header bg colour?
		if ( !$this->parent_obj->db->option_exists('cp_header_bg_colour') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top" id="cp_header_bg_colour-row">
		<th scope="row"><label for="cp_header_bg_colour">Header Background Colour</label></th>
		<td><input type="text" name="cp_header_bg_colour" id="cp_header_bg_colour" value="'.$this->parent_obj->db->header_bg_colour.'" /><span class="description hide-if-js">If you want to hide header text, add <strong>#blank</strong> as text colour.</span><input type="button" class="button hide-if-no-js" value="Select a Colour" id="pickcolor" /><div id="color-picker" style="z-index: 100; background:#eee; border:1px solid #ccc; position:absolute; display:none;"></div></td>
	</tr>
	
';

		}
		

		
		// do we have the option to set the scroll speed?
		if ( !$this->parent_obj->db->option_exists('cp_js_scroll_speed') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_js_scroll_speed">Scroll speed</label></th>
		<td><input type="text" id="cp_js_scroll_speed" name="cp_js_scroll_speed" value="'.$this->parent_obj->db->js_scroll_speed.'" class="small-text" /> milliseconds</td>
	</tr>

';

		}
		

		
		// do we have the option to set the minimum page width?
		if ( !$this->parent_obj->db->option_exists('cp_min_page_width') ) {
		
			// define upgrade
			$upgrade .= '
	<tr valign="top">
		<th scope="row"><label for="cp_min_page_width">Minimum page width</label></th>
		<td><input type="text" id="cp_min_page_width" name="cp_min_page_width" value="'.$this->parent_obj->db->min_page_width.'" class="small-text" /> pixels</td>
	</tr>

';

		}
		

		
		// --<
		return $upgrade;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the reset button for the admin form
	 * @return string $reset
	 * @todo: 
	 *
	 */
	function _get_reset() {
	
		// define reset
		$reset = '
	<tr valign="top">
		<th scope="row"><label for="cp_reset">Reset options to plugin defaults</label></th>
		<td><input id="cp_reset" name="cp_reset" value="1" type="checkbox" /></td>
	</tr>
';		
		
		
		// --<
		return $reset;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the rich text editor button for the admin form
	 * @return string $editor
	 * @todo: 
	 *
	 */
	function _get_editor() {
		
		// define editor
		$editor = '
	<tr valign="top">
		<th scope="row"><label for="cp_comment_editor">Comment form editor</label></th>
		<td><select id="cp_comment_editor" name="cp_comment_editor">
				<option value="1" '.(($this->parent_obj->db->option_get('cp_comment_editor') == '1') ? ' selected="selected"' : '').'>Rich-text Editor</option>
				<option value="0" '.(($this->parent_obj->db->option_get('cp_comment_editor') == '0') ? ' selected="selected"' : '').'>Plain-text Editor</option>
			</select>
		</td>
	</tr>



	<tr valign="top">
		<th scope="row"><label for="cp_promote_reading">Default comment form behaviour</label></th>
		<td><select id="cp_promote_reading" name="cp_promote_reading">
				<option value="1" '.(($this->parent_obj->db->option_get('cp_promote_reading') == '1') ? ' selected="selected"' : '').'>Promote reading</option>
				<option value="0" '.(($this->parent_obj->db->option_get('cp_promote_reading') == '0') ? ' selected="selected"' : '').'>Promote commenting</option>
			</select>
		</td>
	</tr>
';
		

		
		// --<
		return $editor;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the TOC options for the admin form
	 * @return string $editor
	 * @todo: 
	 *
	 */
	function _get_toc() {
		
		// define table of contents options
		$toc = '
	<tr valign="top">
		<th scope="row"><label for="cp_show_posts_or_pages_in_toc">Table of Contents contains</label></th>
		<td><select id="cp_show_posts_or_pages_in_toc" name="cp_show_posts_or_pages_in_toc">
				<option value="post" '.(($this->parent_obj->db->option_get('cp_show_posts_or_pages_in_toc') == 'post') ? ' selected="selected"' : '').'>Posts</option>
				<option value="page" '.(($this->parent_obj->db->option_get('cp_show_posts_or_pages_in_toc') == 'page') ? ' selected="selected"' : '').'>Pages</option>
			</select>
		</td>
	</tr>

	'.(($this->parent_obj->db->option_get('cp_show_posts_or_pages_in_toc') == 'page') ? '
	<tr valign="top">
		<th scope="row"><label for="cp_toc_chapter_is_page">Chapters are</label></th>
		<td><select id="cp_toc_chapter_is_page" name="cp_toc_chapter_is_page">
				<option value="1" '.(($this->parent_obj->db->option_get('cp_toc_chapter_is_page') == '1') ? ' selected="selected"' : '').'>Pages</option>
				<option value="0" '.(($this->parent_obj->db->option_get('cp_toc_chapter_is_page') == '0') ? ' selected="selected"' : '').'>Headings</option>
			</select>
		</td>
	</tr>' : '' ).'

	'.(($this->parent_obj->db->option_get('cp_show_posts_or_pages_in_toc') == 'page' AND $this->parent_obj->db->option_get('cp_toc_chapter_is_page') == '0') ? '
	<tr valign="top">
		<th scope="row"><label for="cp_show_subpages">Show Sub-Pages</label></th>
		<td><input id="cp_show_subpages" name="cp_show_subpages" value="1"  type="checkbox" '.( $this->parent_obj->db->option_get('cp_show_subpages') ? ' checked="checked"' : ''  ).' /></td>
	</tr>' : '' ).'
	
	
	<tr valign="top">
		<th scope="row"><label for="cp_show_extended_toc">Appearance of TOC for posts</label></th>
		<td><select id="cp_show_extended_toc" name="cp_show_extended_toc">
				<option value="1" '.(($this->parent_obj->db->option_get('cp_show_extended_toc') == '1') ? ' selected="selected"' : '').'>Extended information</option>
				<option value="0" '.(($this->parent_obj->db->option_get('cp_show_extended_toc') == '0') ? ' selected="selected"' : '').'>Just the title</option>
			</select>
		</td>
	</tr>
	';
	
	
	
		// --<
		return $toc;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the Sidebar options for the admin form
	 * @return string $editor
	 * @todo: 
	 *
	 */
	function _get_sidebar() {
	
		// get option (but if we haven't got a value, use comments)
		$default = $this->parent_obj->db->option_get( 'cp_sidebar_default', 'comments' );
		
		// define table of contents options
		$toc = '
	<tr valign="top">
		<th scope="row"><label for="cp_sidebar_default">Which sidebar do you want to be active by default? (can be overridden on individual pages)</label></th>
		<td><select id="cp_sidebar_default" name="cp_sidebar_default">
				<option value="toc" '.(($default == 'contents') ? ' selected="selected"' : '').'>Contents</option>
				<option value="activity" '.(($default == 'activity') ? ' selected="selected"' : '').'>Activity</option>
				<option value="comments" '.(($default == 'comments') ? ' selected="selected"' : '').'>Comments</option>
			</select>
		</td>
	</tr>

	';
	
	
	
		// --<
		return $toc;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the override paragraph commenting button for the admin form
	 * @return string $reset
	 * @todo: 
	 *
	 */
	function _get_override() {
	
		// define override
		$override = '
	<tr valign="top">
		<th scope="row"><label for="cp_para_comments_enabled">Enable paragraph-level commenting</label></th>
		<td><input id="cp_para_comments_enabled" name="cp_para_comments_enabled" value="1" type="checkbox" '.( $this->parent_obj->db->option_get('cp_para_comments_enabled') ? ' checked="checked"' : ''  ).' /></td>
	</tr>
';		
		
		
		
		// is the AJAX-commenting plugin enabled?
		if ( function_exists( 'cpac_enable_plugin' ) ) {
		
			// define override
			$override .= '
	<tr valign="top">
		<th scope="row"><label for="cp_para_comments_enabled">Enable live commenting via Commentpress Ajaxified</label></th>
		<td><input id="cp_para_comments_live" name="cp_para_comments_live" value="1" type="checkbox" '.( get_option('cp_para_comments_live',0) ? ' checked="checked"' : ''  ).' /></td>
	</tr>
';		
		}
		
		
		
		// --<
		return $override;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the submit button
	 * @return string $editor
	 * @todo: 
	 *
	 */
	function _get_submit() {
	
		// define editor
		$submit = '
<p class="submit">
	<input type="submit" name="cp_submit" value="Save Changes" class="button-primary" />
</p>
				


';
		

		
		// --<
		return $submit;
		
	}
	
	
	
	
	



	/** 
	 * @description: sets class properties for mobile browsers
	 * @todo: 
	 *
	 */
	function _test_for_mobile() {
	
		// do we have a user agent?
		if ( isset( $_SERVER["HTTP_USER_AGENT"] ) ) {
		
			// NOTE: keep an eye on touchphone agents
		
			// get agent
			$agent = $_SERVER["HTTP_USER_AGENT"];
			
			// init touchphone array
			$touchphones = array(
				'iPhone',
				'iPod',
				'Android',
				'BlackBerry9530',
				'LG-TU915 Obigo', // LG touch browser
				'LGE VX',
				'webOS', // Palm Pre, etc.
			);
			
			// loop through them
			foreach( $touchphones AS $phone ) {

				// test for its name in the agent string
				if ( strpos( $agent, $phone ) !== false ) {
				
					// set flag
					$this->is_mobile_touch = true;
				
				}
			
			}
			
			// try using code from http://code.google.com/p/php-mobile-detect/
			include( plugin_dir_path( CP_PLUGIN_FILE ) . 'inc/mobile-detect/Mobile_Detect.php' );
			
			// init
			$detect = new Mobile_Detect();
			
			// is it mobile?
			if ( $detect->isMobile() ) {
			
				// set flag
				$this->is_mobile = true;

			}
			
			// is it a tablet?
			if ( $detect->isTablet() ) {
			
				// set flag
				$this->is_tablet = true;

			}
			
		}

	}
	
	





	/** 
	 * @description: get admin javascript, copied from wp-includes/custom-header.php
	 * @todo: 
	 *
	 */
	function get_admin_js() {
		
		// print inline js
		echo '
<script type="text/javascript">
//<![CDATA[
	var text_objects = ["#cp_header_bg_colour-row"];
	var farbtastic;
	var default_color = "#'.$this->parent_obj->db->option_get_header_bg().'";
	var old_color = null;

	function pickColor(color) {
		jQuery("#cp_header_bg_colour").val(color);
		farbtastic.setColor(color);
	}

	function toggle_text(s) {
		return;
		if (jQuery(s).attr("id") == "showtext" && jQuery("#cp_header_bg_colour").val() != "blank")
			return;

		if (jQuery(s).attr("id") == "hidetext" && jQuery("#cp_header_bg_colour").val() == "blank")
			return;

		if (jQuery("#cp_header_bg_colour").val() == "blank") {
			//Show text
			if (old_color == "#blank")
				old_color = default_color;

			jQuery( text_objects.toString() ).show();
			jQuery("#cp_header_bg_colour").val(old_color);
			pickColor(old_color);
		} else {
			//Hide text
			jQuery( text_objects.toString() ).hide();
			old_color = jQuery("#cp_header_bg_colour").val();
			jQuery("#cp_header_bg_colour").val("blank");
		}
	}

	jQuery(document).ready(function() {
		jQuery("#pickcolor").click(function() {
			jQuery("#color-picker").show();
		});

		jQuery('."'".'input[name="hidetext"]'."'".').click(function() {
			toggle_text(this);
		});

		jQuery("#defaultcolor").click(function() {
			pickColor(default_color);
			jQuery("#cp_header_bg_colour").val(default_color)
		});

		jQuery("#cp_header_bg_colour").keyup(function() {
			var _hex = jQuery("#cp_header_bg_colour").val();
			var hex = _hex;
			if ( hex[0] != "#" )
				hex = "#" + hex;
			hex = hex.replace(/[^#a-fA-F0-9]+/, "");
			if ( hex != _hex )
				jQuery("#cp_header_bg_colour").val(hex);
			if ( hex.length == 4 || hex.length == 7 )
				pickColor( hex );
		});

		jQuery(document).mousedown(function(){
			jQuery("#color-picker").each( function() {
				var display = jQuery(this).css("display");
				if (display == "block")
					jQuery(this).fadeOut(2);
			});
		});

		farbtastic = jQuery.farbtastic("#color-picker", function(color) { pickColor(color); });
		pickColor("#'.$this->parent_obj->db->option_get_header_bg().'");

		'.( ( 'blank' == $this->parent_obj->db->option_get_header_bg() OR '' == $this->parent_obj->db->option_get_header_bg() ) ? 'toggle_text();' : '' ).'
		});

//]]>
	</script>

';

	}
	
	
	
	


	/** 
	 * @description: return the javascript to init tinyMCE for WP < 3.2
	 * @return string $js
	 * @todo: 
	 *
	 */
	function _get_tinymce_init() {
	
		// base url
		//$_base = trailingslashit( get_bloginfo('wpurl') ).'wp-includes/js/tinymce';
		$_base = includes_url('js/tinymce');
		
		// locale
		$mce_locale = ( '' == get_locale() ) ? 'en' : strtolower( substr(get_locale(), 0, 2) ); // only ISO 639-1
		
		// content css
		$_content_css = ''; //trailingslashit( get_bloginfo('wpurl') ).'wp-includes/js/tinymce/wordpress.css';
	
	
	
		// define tinyMCE javascript
		$js = '
<script type="text/javascript">
//<![CDATA[



/** 
 * @description: tinyMCE callback function
 * @todo: 
 *
 */	
function br_to_nl( element_id, html, body ) {

	// replace brs with newlines
	html = html.replace(/<br\s*\/>/gi, "\n");
	
	// --<
	return html;
	
}



/** 
 * @description: tinyMCE init
 * @todo: 
 *
 */	
tinyMCEPreInit = {

	base : "'.$_base.'",
	
	suffix : "",
	
	query : "ver=20081129",
	
	mceInit : {	
		mode : "exact",
		editor_selector : "comment",
		width : "100%",
		theme : "advanced",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,bullist,numlist,|,link,unlink,|,removeformat,fullscreen",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "none",
		theme_advanced_resizing : "1",
		theme_advanced_resize_horizontal : false,
		theme_advanced_disable : "code",
		force_p_newlines : "1",
		force_br_newlines : false,
		forced_root_block : "p",
		gecko_spellcheck : true,
		directionality : "ltr",
		save_callback : "br_to_nl",
		entity_encoding : "raw",
		plugins : "safari,fullscreen",
		extended_valid_elements : "a[name|href|title],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],blockquote[cite],strike,s,del,div[class|style]",
		language : "en"
	},

	go : function() {
		var t = this, sl = tinymce.ScriptLoader, ln = t.mceInit.language, th = t.mceInit.theme, pl = t.mceInit.plugins;

		sl.markDone(t.base + "/langs/" + ln + ".js");
		sl.markDone(t.base + "/themes/" + th + "/langs/" + ln + ".js");
		sl.markDone(t.base + "/themes/" + th + "/langs/" + ln + "_dlg.js");

		tinymce.each(pl.split(","), function(n) {
			if (n && n.charAt(0) != "-") {
				sl.markDone(t.base + "/plugins/" + n + "/langs/" + ln + ".js");
				sl.markDone(t.base + "/plugins/" + n + "/langs/" + ln + "_dlg.js");
			}
		});
	},

	load_ext : function(url,lang) {
		var sl = tinymce.ScriptLoader;

		sl.markDone(url + "/langs/" + lang + ".js");
		sl.markDone(url + "/langs/" + lang + "_dlg.js");
	}
	
};



// load languages, themes and plugins
tinyMCEPreInit.go();

// init TinyMCE object
tinyMCE.init(tinyMCEPreInit.mceInit);



//]]>
</script>'."\n\n\n\n";
		
		
		
		// --<
		return $js;
		
	}
	
	
	
	
	



	/**
	 * Adds the TinyMCE editor to comment textareas in WP > 3.2
	 * Adapted from wp_tiny_mce in /wp-admin/includes/post.php
	 *
	 * @param mixed $settings optional An array that can add to or overwrite the default TinyMCE settings.
	 */
	function _get_tinymce( $settings = false ) {
	
		global $tinymce_version;
	
		$baseurl = includes_url('js/tinymce');
	
		$mce_locale = ( '' == get_locale() ) ? 'en' : strtolower( substr(get_locale(), 0, 2) ); // only ISO 639-1
	
		/*
		The following filter allows localization scripts to change the languages displayed in the spellchecker's drop-down menu.
		By default it uses Google's spellchecker API, but can be configured to use PSpell/ASpell if installed on the server.
		The + sign marks the default language. More information:
		http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/spellchecker
		*/
		$mce_spellchecker_languages = apply_filters('cprc_tinymce_spellchecker_languages', '+English=en,Danish=da,Dutch=nl,Finnish=fi,French=fr,German=de,Italian=it,Polish=pl,Portuguese=pt,Spanish=es,Swedish=sv');
		
		// default plugins
		$plugins = apply_filters( 'cprc_tinymce_plugins', array( 'spellchecker', 'tabfocus', 'fullscreen', 'safari' ) );
		$ext_plugins = '';
	
		// default buttons
		$mce_buttons = apply_filters( 'cprc_tinymce_buttons', array('bold', 'italic', 'underline', 'strikethrough', '|', 'link', 'unlink', '|', 'spellchecker', 'removeformat', 'fullscreen') );
		$mce_buttons = implode($mce_buttons, ',');
	
		// TinyMCE init settings
		$initArray = array (
			'mode' => 'specific_textareas',
			'editor_selector' => 'comment',
			'width' => '99%',
			'theme' => 'advanced',
			'theme_advanced_buttons1' => $mce_buttons,
			'theme_advanced_buttons2' => '',
			'theme_advanced_buttons3' => '',
			'theme_advanced_buttons4' => '',
			'language' => $mce_locale,
			'spellchecker_languages' => $mce_spellchecker_languages,
			'theme_advanced_toolbar_location' => 'top',
			'theme_advanced_toolbar_align' => 'left',
			'theme_advanced_statusbar_location' => 'none',
			'theme_advanced_resizing' => true,
			'theme_advanced_resize_horizontal' => false,
			'dialog_type' => 'modal',
			'formats' => "{
				alignleft : [
					{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'left'}},
					{selector : 'img,table', classes : 'alignleft'}
				],
				aligncenter : [
					{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'center'}},
					{selector : 'img,table', classes : 'aligncenter'}
				],
				alignright : [
					{selector : 'p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li', styles : {textAlign : 'right'}},
					{selector : 'img,table', classes : 'alignright'}
				],
				strikethrough : {inline : 'del'}
			}",
			'relative_urls' => false,
			'remove_script_host' => false,
			'convert_urls' => false,
			'apply_source_formatting' => false,
			'remove_linebreaks' => true,
			'gecko_spellcheck' => true,
			'keep_styles' => false,
			'entities' => '38,amp,60,lt,62,gt',
			'accessibility_focus' => true,
			'tabfocus_elements' => 'major-publishing-actions',
			'media_strict' => false,
			'paste_remove_styles' => true,
			'paste_remove_spans' => true,
			'paste_strip_class_attributes' => 'all',
			'paste_text_use_dialog' => true,
			'extended_valid_elements' => 'a[name|href|title],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style],blockquote[cite],strike,s,del,div[class|style]',
			'wpeditimage_disable_captions' => '',
			'wp_fullscreen_content_css' => "$baseurl/plugins/wpfullscreen/css/wp-fullscreen.css",
			'plugins' => implode( ',', $plugins ),
		);
	
		// editor styles - applied via filter
		$mce_css = '';
		$mce_css = trim( apply_filters( 'mce_css', $mce_css ), ' ,' );
	
		if ( ! empty($mce_css) )
			$initArray['content_css'] = $mce_css;
	
		if ( is_array($settings) )
			$initArray = array_merge($initArray, $settings);
	
		// For people who really REALLY know what they're doing with TinyMCE
		// You can modify initArray to add, remove, change elements of the config before tinyMCE.init
		// Setting "valid_elements", "invalid_elements" and "extended_valid_elements" can be done through "cprc_tinymce_before_init".
		// Best is to use the default cleanup by not specifying valid_elements, as TinyMCE contains full set of XHTML 1.0.
		$initArray = apply_filters('cprc_tinymce_before_init', $initArray);
	
		/**
		 * Deprecated
		 *
		 * The tiny_mce_version filter is not needed since external plugins are loaded directly by TinyMCE.
		 * These plugins can be refreshed by appending query string to the URL passed to mce_external_plugins filter.
		 * If the plugin has a popup dialog, a query string can be added to the button action that opens it (in the plugin's code).
		 */
		$version = apply_filters('tiny_mce_version', '');
		$version = 'ver=' . $tinymce_version . $version;
	
		$language = $initArray['language'];
		if ( 'en' != $language )
			include_once(ABSPATH . WPINC . '/js/tinymce/langs/wp-langs.php');
	
		$mce_options = '';
		foreach ( $initArray as $k => $v ) {
			if ( is_bool($v) ) {
				$val = $v ? 'true' : 'false';
				$mce_options .= $k . ':' . $val . ', ';
				continue;
			} elseif ( !empty($v) && is_string($v) && ( ('{' == $v{0} && '}' == $v{strlen($v) - 1}) || ('[' == $v{0} && ']' == $v{strlen($v) - 1}) || preg_match('/^\(?function ?\(/', $v) ) ) {
				$mce_options .= $k . ':' . $v . ', ';
				continue;
			}
	
			$mce_options .= $k . ':"' . $v . '", ';
		}
	
		$mce_options = rtrim( trim($mce_options), '\n\r,' );
	
		// not needed
		//do_action('before_wp_tiny_mce', $initArray);
		
?>
	
<script type="text/javascript">
/* <![CDATA[ */
tinyMCEPreInit = {
	base : "<?php echo $baseurl; ?>",
	suffix : "",
	query : "<?php echo $version; ?>",
	mceInit : {<?php echo $mce_options; ?>},
	load_ext : function(url,lang){var sl=tinymce.ScriptLoader;sl.markDone(url+'/langs/'+lang+'.js');sl.markDone(url+'/langs/'+lang+'_dlg.js');}
};
/* ]]> */
</script>

<?php
	
		// ditched compressed version
		echo "<script type='text/javascript' src='$baseurl/tiny_mce.js?$version'></script>\n";
	
		if ( 'en' != $language && isset($lang) )
			echo "<script type='text/javascript'>\n$lang\n</script>\n";
		else
			echo "<script type='text/javascript' src='$baseurl/langs/wp-langs-en.js?$version'></script>\n";

?>

<script type="text/javascript">
/* <![CDATA[ */
<?php
	if ( $ext_plugins )
		echo "$ext_plugins\n";

?>
(function(){var t=tinyMCEPreInit,sl=tinymce.ScriptLoader,ln=t.mceInit.language,th=t.mceInit.theme,pl=t.mceInit.plugins;sl.markDone(t.base+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'_dlg.js');tinymce.each(pl.split(','),function(n){if(n&&n.charAt(0)!='-'){sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'.js');sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'_dlg.js');}});})();

tinyMCE.init(tinyMCEPreInit.mceInit);
/* ]]> */
</script>

<?php
		
		// not needed
		//do_action('after_wp_tiny_mce', $initArray);
	
	}
	
	
	
	
	
	
//#################################################################







} // class ends






?>