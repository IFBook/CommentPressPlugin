<?php /*
===============================================================
Class CommentPressDisplay Version 1.0
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

class CommentPressDisplay {






	/*
	===============================================================
	Properties
	===============================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	// path to jQuery directory
	var $jquery_path;
	
	// path to jQuery plugins directory
	var $jquery_plugins_path;
	
	// standard mobile browser
	var $is_mobile = false;
	
	// touch-based mobile browser
	var $is_mobile_touch = false;
	






	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function CommentPressDisplay( $parent_obj ) {
	
		// store reference to parent
		$this->parent_obj = $parent_obj;
	
		// init
		$this->_init();

		// --<
		return $this;

	}






	/** 
	 * @description: if needed, sets up this object
	 * @param integer $blog_id the ID of the blog - default null
	 * @todo: 
	 *
	 */
	function initialise( $blog_id = null ) {
	
		// if we're force-activating in multisite (or sitewide) and we want the official theme
		if ( 
		
			( CP_PLUGIN_CONTEXT == 'mu_forced' OR CP_PLUGIN_CONTEXT == 'mu_sitewide' ) AND 
			CP_ACTIVATE_THEME === true 
			
		) {
		
			// activate the default Commentpress theme
			switch_theme( 'commentpress', 'commentpress' );
		
		}

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
	 * @description: enqueue jQuery, jQuery UI and plugins
	 * @todo: 
	 *
	 */
	function get_jquery() {
	
		// add our javascript plugin and dependencies
		// NOTE: the UI has to be added separately, as the built in one is not the latest
		wp_enqueue_script(
		
			'jquery_commentpress', 
			$this->jquery_plugins_path.'jquery.commentpress.js', 
			array('jquery','jquery-form')
		
		);
		
		// add jQuery Scroll-To plugin
		wp_enqueue_script( 
			
			'jquery_scrollto', 
			$this->jquery_plugins_path.'jquery.scrollTo.js', 
			array('jquery_commentpress') 
		
		);
		
		// add jQuery Cookie plugin
		wp_enqueue_script( 
		
			'jquery_cookie', 
			$this->jquery_plugins_path.'jquery.cookie.js', 
			array('jquery_commentpress') 
			
		);
		
		// add jQuery UI
		wp_enqueue_script(
		
			'jquery_ui_all', 
			$this->jquery_path.'jquery-ui-1.8.5.custom.min.js', 
			array('jquery_commentpress')
			
		);

	}
	
	
	
	
	


	/** 
	 * @description: enqueue our quicktags script
	 * @todo: 
	 *
	 */
	function get_custom_quicktags() {
	
		// add our javascript script and dependencies
		wp_enqueue_script(
		
			'cp_custom_quicktags',
			trailingslashit( get_bloginfo('wpurl') ) . CP_PLUGIN_REL_PATH . 'js/cp_quicktags.js',
			array('quicktags'),
			NULL, // no version
			FALSE // not in footer (but may need to be in WP 3.3)
			
		);

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
		$filepath = TEMPLATEPATH . '/style/css/admin.css';
	
		// is our stylesheet present?
		if ( file_exists( $filepath ) ) {
		
			// add Admin UI stylesheet
			$styles = '<!-- Commentpress Admin styles -->
<link rel="stylesheet" type="text/css" media="screen" href="'.get_bloginfo('template_url') . '/style/css/admin.css" />
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
	function get_styles() {
		
		// init
		$styles = '';
		
		
		
		/*
		// We should eventually use:
		wp_enqueue_style( $handle, $src, $deps, $ver, $media );
		
		// always add jQuery UI
		wp_enqueue_style( 'jquery.ui.all', $this->jquery_path.'theme/ui.all.css' );
		*/


		
		// add jQuery UI stylesheet -> needed for resizable columns
		$styles = '<!-- jQuery UI styles -->
<link rel="stylesheet" type="text/css" media="screen" href="'.$this->jquery_path.'theme/ui.base.css" />
'."\n\n";
		
		
		
		// do we have a custom header bg colour?
		if ( $this->parent_obj->db->option_get_header_bg() != $this->parent_obj->db->header_bg_colour ) {
		
			// echo inline style
			echo '
			
<style type="text/css">
	
	#book_header {
		background: #'.$this->parent_obj->db->option_get_header_bg().';
	}

</style>

';
		
		}
		
		
		
		// --<
		return $styles;
			
	}
	
	
	
	


	/** 
	 * @description: get built-in TinyMCE scripts from Wordpress Includes directory
	 * @return string $scripts
	 * @todo: 
	 *
	 */
	function get_tinymce() {
	
		// check option
		if ( 
		
			$this->parent_obj->db->option_exists( 'cp_comment_editor' ) AND
			$this->parent_obj->db->option_get( 'cp_comment_editor' ) != '1'
			
		) {
		
			// --<
			return;
		
		}
		
		
		
		// don't return TinyMCE for mobile phones
		if ( $this->is_mobile_touch OR $this->is_mobile ) {
		
			// --<
			return;
		
		}
		
		
		
		// Is it one of our themes?
		if ( $this->parent_obj->is_allowed_theme() ) {
		
			// test for WordPress version
			global $wp_version;
			
			// for WP 3.2+
			if ( version_compare( $wp_version, '3.2', '>=' ) ) {
				
				// predefine some settings
				$settings = array(
				
					'editor_class' => 'comment',
					'elements' => 'comment',
					'mode' => 'exact',
					'editor_selector' => null,
					'textarea_rows' => 3
					
				);
		
				// use method adapted from WP core
				//$this->_get_tinymce( $settings );
				
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

		// Is it one of our themes?
		if ( $this->parent_obj->is_allowed_theme() ) {
		
			// base url
			$_base = trailingslashit( get_bloginfo('template_url') );
			
			// enqueue common js
			wp_enqueue_script(
			
				'cp_common', 
				$_base.'style/js/cp_js_common.js', 
				array('jquery_commentpress')
			
			);
			
			// enqueue form js
			wp_enqueue_script(
			
				'cp_form', 
				$_base.'style/js/cp_js_form.js', 
				array('cp_common')
			
			);
			
			// get vars
			$this->localise_js( $this->parent_obj->db->get_javascript_vars(), 'cp_common' );
			
		}
	
	}
	
	
	
	
	
	

	/** 
	 * @description: get help text
	 * @return HTML $help
	 * @todo: 
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
	function list_posts( $params = 'numberposts=-1&order=ASC' ) {
	
		// declare access to post
		global $post;
		
		/*
		---------------------
		Alternative approach:
		---------------------
		$postslist = get_posts( 'numberposts=-1' );
		foreach ( $postslist AS $post ) { 
			setup_postdata( $post );
			echo 'list item in loop';
		}
		---------------------
		*/
		

		
		// get all posts
		$posts = get_posts( $params );
		
		// run through them...
		foreach( $posts AS $post ) {
	
			// get comment count for that post
			$count = count( $this->parent_obj->db->get_approved_comments( $post->ID ) );
	
			// write list item
			echo '<li class="title"><a href="'.get_permalink().'">'.the_title('','',false).' ('.$count.')</a></li>'."\n";
		
		}

	}
	
	
	
	
	
	
	
	/** 
	 * @description: print the posts and their comment count in a list format
	 * @todo:
	 *
	 */
	function list_pages() {
	
		// get page display option
		$depth = $this->parent_obj->db->option_get('cp_show_subpages');
		
		// ALWAYS write subpages into page, even if they aren't displayed
		$depth = 0;
		
		

		// get pages to exclude
		$exclude = $this->parent_obj->db->option_get('cp_special_pages');
		
		// do we have any?
		if ( !$exclude ) { $exclude = array(); }
		
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
	 * @description: get the paragraph icon
	 * @param integer $comment_count number of comments
	 * @param string $text_signature comment text signature
	 * @return string $comment_icon
	 * @todo: 
	 *
	 */
	function get_icon( $comment_count, $text_signature ) {
	
		// reset icon
		$icon = null;

		// if we have no comments...
		if( $comment_count == 0 ) {
			
			// show add comment icon
			$icon = 'comment_add.png';
			$class = ' no_comments';
			$number_span = '<small class="comment_count">'.(string) $comment_count.'</small>';
			
		} elseif( $comment_count > 0 ) {
		
			// show comments present icon
			$icon = 'comment.png';
			$class = ' has_comments';
			
		}
		
		// init s
		$s = 's';
		$are_is = 'are';
		
		// if just one, add s
		if ( $comment_count == 1 ) { 
			$s = ''; 
			$are_is = 'is';
		}
		
		// define small
		$small = '<small class="comment_count" title="There '.$are_is.' '.$comment_count.' comment'.$s.' written for this paragraph">'.(string) $comment_count.'</small>';

		
		
		/*
		// define HTML for comment icon
		$comment_icon = '<span title="There '.$are_is.' '.$comment_count.' comment'.$s.' written for this paragraph" class="commenticonbox"><a class="para_permalink" href="#'.$text_signature.'" title="Permalink for this paragraph"><img alt="Comment icon for this paragraph" class="commenticon" id="'.$text_signature.'" src="'.get_bloginfo('template_directory').'/style/images/icons/'.$icon.'" /></a>'.$number_span.'</span>'."\n";
		*/
		
		// define more accessable HTML for comment icon
		$comment_icon = '<span class="commenticonbox"><a id="'.$text_signature.'" class="para_permalink'.$class.'" href="#'.$text_signature.'" title="Permalink for this paragraph">Permalink for this paragraph</a> '.$small.'</span>'."\n";
		
		
		
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
							
			case 'p':
		
				// define para tag
				$para_tag = '<'.$tag.' class="textblock" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
			case 'div':
		
				// define opening tag (we'll close it later)
				$para_tag = '<div class="textblock" id="textblock-'.$text_signature.'">'.$commenticon; 
				break;
							
		}
	


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
	 * @description: get the minimise button
	 * @param: string $sidebar type of sidebar (comments, toc, archive)
	 * @return string $tag
	 * @todo: 
	 *
	 */
	function get_minimise_button( $sidebar = 'comments' ) {
	
		// define minimise button
		$tag = '<img id="cp_minimise_'.$sidebar.'" class="cp_button" src="'.get_bloginfo('template_directory').'/style/images/icons/close.png" alt="minimise button" title="Toggle Sidebar" />';
		
		// --<
		return $tag;
		
	}
	
	
	
	
	



	/** 
	 * @description: get the minimise all button
	 * @param: string $sidebar type of sidebar (comments, toc, archive)
	 * @return string $tag
	 * @todo: 
	 *
	 */
	function get_minimise_all_button( $sidebar = 'comments' ) {
	
		// define minimise button
		$tag = '<span id="cp_minimise_all_comments" title="Minimise all Comment Sections"></span>';
		
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
		$link = '<li><a href="#" id="btn_header_min" class="css_btn" title="Minimise Header">Minimise Header</a></li>'."\n";
		
		
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
		


		// get path to our plugin directory
		$_plugin_path = trailingslashit( get_bloginfo('wpurl') ) . CP_PLUGIN_REL_PATH;


		
		// define path to plugin jQuery directory
		$this->jquery_path = $_plugin_path. 'js/jquery/';
		
		// define path to jQuery plugins directory
		$this->jquery_plugins_path =  $this->jquery_path. 'plugins/';
		
	}







	/** 
	 * @description: returns the admin form HTML
	 * @return string $admin_page
	 * @todo: 
	 *
	 */
	function _get_admin_form() {
	
		// sanitise admin page url
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( $url_array ) { $url = $url_array[0]; }



		// if we need to upgrade...
		if ( $this->parent_obj->db->check_upgrade() ) {
		
			// stripped out url
			// define admin page
			$admin_page = '
<div class="icon32" id="icon-options-general"><br/></div>

<h2>Commentpress Upgrade</h2>



<form method="post" action="'.htmlentities($url.'&updated=true').'">

'.wp_nonce_field( 'cp_admin_action', 'cp_nonce', true, false ).'
'.wp_referer_field( false ).'
<input id="cp_upgrade" name="cp_upgrade" value="1" type="hidden" /></td>



<h3>Please upgrade Commentpress</h3>

<p>It looks like you are running an older version of Commentpress. The following options have become available in the new version.</p>



<table class="form-table">

'.$this->_get_upgrade().'

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

$this->_get_internal_options().
$this->_get_external_options().



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
	function _get_internal_options() {
	
		// Is it one of our themes?
		if ( $this->parent_obj->is_allowed_theme() ) {
		


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
	</tr>' : '' );
	
	
	
			// define Commentpress theme options
			$options = '
<h3>Options for the Commentpress Theme</h3>

<p>When the special Commentpress theme is active, the following options modify its behaviour.</p>



'.$this->_get_db_mod().'



<h4>Special Pages</h4>

<p><strong style="color: red;">NOTE!</strong> Special pages add a lot of extra functionality to Commentpress. Create them when you first install the plugin and (optionally, if you want to remove all traces of the plugin) delete them when you uninstall Commentpress.</p>

<table class="form-table">

'.$this->_get_special_pages().'

</table>



<h4>Global Options</h4>

<table class="form-table">

'.$this->_get_reset().'

'.$this->_get_override().'

'.$this->_get_editor().'

	<tr valign="top">
		<th scope="row"><label for="cp_title_visibility">Default page title visibility</label></th>
		<td><select id="cp_title_visibility" name="cp_title_visibility">
				<option value="show" '.(($this->parent_obj->db->option_get('cp_title_visibility') == 'show') ? ' selected="selected"' : '').'>Show page titles</option>
				<option value="hide" '.(($this->parent_obj->db->option_get('cp_title_visibility') == 'hide') ? ' selected="selected"' : '').'>Hide page titles</option>
			</select>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cp_minimise_sidebar">Allow Sidebar to be minimized</label></th>
		<td><input id="cp_minimise_sidebar" name="cp_minimise_sidebar" value="1" type="checkbox" '.( $this->parent_obj->db->option_get('cp_minimise_sidebar') ? ' checked="checked"' : ''  ).' /></td>
	</tr>

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

'.$toc.'

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
	
		}
		
		

		// --<
		return $options;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns the options for themes other than Commentpress for the admin form
	 * @return string $options
	 * @todo: 
	 *
	 */
	function _get_external_options() {
	
		$options = '';	
	
		// Is it one of our themes?
		if ( !$this->parent_obj->is_allowed_theme() ) {
	
			// define options for themes other than Commentpress
			$options = '
<h3>Options for a theme other than the Commentpress Theme</h3>

<p><strong style="color: red;">PLEASE NOTE!</strong> we have decided to drop support for all themes other than the official Commentpress theme. This is partly because there has been no demand for Commentpress functionality with other themes &mdash; and partly because it allows us to concentrate on making the official theme as good as we can make it.</p>

<p><strong style="color: red;">Please enable the Commentpress theme and then come back to this page for access to Commentpress settings.</strong></p>

<p>PS: if you do want a plugin that enables paragraph-level commenting on other themes, you could try <a href="http://digress.it/">Digress.it</a>, which was based on an old version of Commentpress and see if they support the theme that you are using.</p>

';

		}
		


		// --<
		return $options;
		
	}
	
	
	
	
	



	/** 
	 * @description: returns either the install or uninstall button
	 * @return string $reset
	 * @todo: 
	 *
	 */
	function _get_db_mod() {
	
		// do we have comment_text_signature in our comments table?
		if ( $this->parent_obj->db->db_is_modified() ) {
		
			// yes -> show uninstall
			return $this->_get_uninstall();
		
		} else {
		
			// no -> show install
			return $this->_get_install();
		
		}
	
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
	 * @description: returns the upgrade button for the admin form
	 * @return string $upgrade
	 * @todo: 
	 *
	 */
	function _get_upgrade() {
		
		// init
		$upgrade = '';
		
		
		
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
	 * @description: returns the special page options
	 * @return string $editor
	 * @todo: 
	 *
	 */
	function _get_special_pages() {
	
		// init
		$pages = '';
		
		
		
		// get special pages array, if it's there
		$special_pages = $this->parent_obj->db->option_get( 'cp_special_pages' );
	
		// do we already have special pages?
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {

			// define pages options
			$pages = '
	<tr valign="top">
		<th scope="row"><label for="cp_delete_pages">Delete all special pages</label></th>
		<td><input id="cp_delete_pages" name="cp_delete_pages" value="1" type="checkbox" /></td>
	</tr>
	
	';
			
			/*
			// define individual pages
			$individual_pages = '
	<tr valign="top">
		<th scope="row"><label for="cp_delete_welcome_page">Delete Title Page</label></th>
		<td><input id="cp_delete_welcome_page" name="cp_delete_welcome_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_delete_gen_page">Delete General Comments Page</label></th>
		<td><input id="cp_delete_gen_page" name="cp_delete_gen_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_delete_all_page">Delete All Comments Page</label></th>
		<td><input id="cp_delete_all_page" name="cp_delete_all_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_delete_by_page">Delete Comments By Author Page</label></th>
		<td><input id="cp_delete_by_page" name="cp_delete_by_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_delete_blog_page">Delete Blog Page</label></th>
		<td><input id="cp_delete_blog_page" name="cp_delete_blog_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_delete_toc_page">Delete Table of Contents Page</label></th>
		<td><input id="cp_delete_toc_page" name="cp_delete_toc_page" value="1" type="checkbox" /></td>
	</tr>
	
	';
			*/
	
	

		} else {



			// don't allow
			$allowed = false;

			// if we're in a multisite context 
			if ( CP_PLUGIN_CONTEXT != 'standard' ) {
				
				// is our user a super admin or are they the blog admin?
				if( is_super_admin() OR current_user_can('manage_options') ) {
					
					// allow
					$allowed = true;
					
				}
				
			} else {
			
				// sanity check function exists
				if ( function_exists('current_user_can') ) {
			
					// check user permissions
					if ( current_user_can('manage_options') ) {
					
						// allow
						$allowed = true;

					}
				
				}
			
			}
			
			
			
			// can we?
			if ( $allowed ) {
			
			
			
				// add auto-create pages
				$pages = '
		<tr valign="top">
			<th scope="row"><label for="cp_create_pages">Create all special pages</label></th>
			<td><input id="cp_create_pages" name="cp_create_pages" value="1" type="checkbox" /></td>
		</tr>
		
		';


		
				// define individual pages
				$individual_pages = '
	<tr valign="top">
		<th scope="row"><label for="cp_create_welcome_page">Create Title Page</label></th>
		<td><input id="cp_create_welcome_page" name="cp_create_welcome_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_create_gen_page">Create General Comments Page</label></th>
		<td><input id="cp_create_gen_page" name="cp_create_gen_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_create_all_page">Create All Comments Page</label></th>
		<td><input id="cp_create_all_page" name="cp_create_all_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_create_by_page">Create Comments By Author Page</label></th>
		<td><input id="cp_create_by_page" name="cp_create_by_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_create_blog_page">Create Blog Page</label></th>
		<td><input id="cp_create_blog_page" name="cp_create_blog_page" value="1" type="checkbox" /></td>
	</tr>
	
	<tr valign="top">
		<th scope="row"><label for="cp_create_toc_page">Create Table of Contents Page</label></th>
		<td><input id="cp_create_toc_page" name="cp_create_toc_page" value="1" type="checkbox" /></td>
	</tr>
	
	';
	
			
			
			}

		}
		
			
			
		// --<
		return $pages;
		
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
	
		// init
		$submit = '';
		
		// Is it one of our themes?
		if ( $this->parent_obj->is_allowed_theme() ) {
	
			// define editor
			$submit = '
<p class="submit">
	<input type="submit" name="cp_submit" value="Save Changes" class="button-primary" />
</p>
				


';
		}
		

		
		// --<
		return $submit;
		
	}
	
	
	
	
	



	/** 
	 * @description: sets class properties for mobile browsers
	 * @todo: 
	 *
	 */
	function _test_for_mobile() {
	
		// NOTE: the following lists of phones are derived from WordPress Mobile Edition
		// <http://crowdfavorite.com/wordpress/> We probably need to investigate if there
		// are licencing issues here - and if so, where to get a similar list.
		
		// do we have a user agent?
		if ( isset( $_SERVER["HTTP_USER_AGENT"] ) ) {
		
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
			
			// init mobile array
			$mobiles = array(
				'2.0 MMP',
				'240x320',
				'400X240',
				'AvantGo',
				'BlackBerry',
				'Blazer',
				'Cellphone',
				'Danger',
				'DoCoMo',
				'Elaine/3.0',
				'EudoraWeb',
				'Googlebot-Mobile',
				'hiptop',
				'IEMobile',
				'KYOCERA/WX310K',
				'LG/U990',
				'MIDP-2.',
				'MMEF20',
				'MOT-V',
				'NetFront',
				'Newt',
				'Nintendo Wii',
				'Nitro', // Nintendo DS
				'Nokia',
				'Opera Mini',
				'Palm',
				'PlayStation Portable',
				'portalmmm',
				'Proxinet',
				'ProxiNet',
				'SHARP-TQ-GX10',
				'SHG-i900',
				'Small',
				'SonyEricsson',
				'Symbian OS',
				'SymbianOS',
				'TS21i-10',
				'UP.Browser',
				'UP.Link',
				'webOS', // Palm Pre, etc.
				'Windows CE',
				'WinWAP',
				'YahooSeeker/M1A1-R2D2',
			);
		
			// loop through them
			foreach( $mobiles AS $phone ) {

				// test for its name in the agent string
				if ( strpos( $agent, $phone ) !== false ) {
				
					// set flag
					$this->is_mobile = true;
				
				}
			
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

	if ( ! $compressed ) {
?>
(function(){var t=tinyMCEPreInit,sl=tinymce.ScriptLoader,ln=t.mceInit.language,th=t.mceInit.theme,pl=t.mceInit.plugins;sl.markDone(t.base+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'.js');sl.markDone(t.base+'/themes/'+th+'/langs/'+ln+'_dlg.js');tinymce.each(pl.split(','),function(n){if(n&&n.charAt(0)!='-'){sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'.js');sl.markDone(t.base+'/plugins/'+n+'/langs/'+ln+'_dlg.js');}});})();
<?php } ?>
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