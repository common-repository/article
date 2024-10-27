<?php

/**

Plugin Name: Article 
Plugin URI: https://howtocreateapressrelease.com/article-plugin-information-wordpress 
Description: Create, edit and store articles or notes, easily within your wordpress dashboard with the visual editor. Creates a new sections for articles or personal notes "within" your wordpress dashboard.
Version: 1.0 
Author: How To Create A Press Release 
Author URI: https://howtocreateapressrelease.com 
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

Create, edit and store articles or notes, easily within your wordpress dashboard with the visual editor. 

**/

# Exit if accessed directly
if (!defined("ABSPATH"))
{
	exit;
}

# Constant

/**
 * Exec Mode
 **/
define("ARTICLEAPP_EXEC",true);

/**
 * Plugin Base File
 **/
define("ARTICLEAPP_PATH",dirname(__FILE__));

/**
 * Plugin Base Directory
 **/
define("ARTICLEAPP_DIR",basename(ARTICLEAPP_PATH));

/**
 * Plugin Base URL
 **/
define("ARTICLEAPP_URL",plugins_url("/",__FILE__));

/**
 * Plugin Version
 **/
define("ARTICLEAPP_VERSION","1.0"); 

/**
 * Debug Mode
 **/
define("ARTICLEAPP_DEBUG",false);  //change false for distribution



/**
 * Base Class Plugin
 * @author prwirepro
 *
 * @access public
 * @version 1.0
 * @package Article
 *
 **/

class Article
{

	/**
	 * Instance of a class
	 * @access public
	 * @return void
	 **/

	function __construct()
	{
		add_action("plugins_loaded", array($this, "articleapp_textdomain")); //load language/textdomain
		add_action("wp_enqueue_scripts",array($this,"articleapp_enqueue_scripts")); //add js
		add_action("wp_enqueue_scripts",array($this,"articleapp_enqueue_styles")); //add css
		add_action("init", array($this, "articleapp_post_type_article_init")); // register a article post type.
		add_filter("the_content", array($this, "articleapp_post_type_article_the_content")); // modif page for article
		add_action("after_setup_theme", array($this, "articleapp_image_size")); // register image size.
		add_filter("image_size_names_choose", array($this, "articleapp_image_sizes_choose")); // image size choose.
		add_action("init", array($this, "articleapp_register_taxonomy")); // register register_taxonomy.
		add_action("wp_head",array($this,"articleapp_dinamic_js"),1); //load dinamic js
		if(is_admin()){
			add_action("admin_enqueue_scripts",array($this,"articleapp_admin_enqueue_scripts")); //add js for admin
			add_action("admin_enqueue_scripts",array($this,"articleapp_admin_enqueue_styles")); //add css for admin
		}
	}


	/**
	 * Loads the plugin's translated strings
	 * @link http://codex.wordpress.org/Function_Reference/load_plugin_textdomain
	 * @access public
	 * @return void
	 **/
	public function articleapp_textdomain()
	{
		load_plugin_textdomain("article", false, ARTICLEAPP_DIR . "/languages");
	}


	/**
	 * Insert javascripts for back-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function articleapp_admin_enqueue_scripts($hooks)
	{
		if (function_exists("get_current_screen")) {
			$screen = get_current_screen();
		}else{
			$screen = $hooks;
		}
	}


	/**
	 * Insert javascripts for front-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_script
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function articleapp_enqueue_scripts($hooks)
	{
			wp_enqueue_script("articleapp_main", ARTICLEAPP_URL . "assets/js/articleapp_main.js", array("jquery"),"1.0",true );
	}


	/**
	 * Insert CSS for back-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_register_style
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function articleapp_admin_enqueue_styles($hooks)
	{
		if (function_exists("get_current_screen")) {
			$screen = get_current_screen();
		}else{
			$screen = $hooks;
		}
	}


	/**
	 * Insert CSS for front-end
	 * 
	 * @link http://codex.wordpress.org/Function_Reference/wp_register_style
	 * @link http://codex.wordpress.org/Function_Reference/wp_enqueue_style
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function articleapp_enqueue_styles($hooks)
	{
		// register css
		wp_register_style("articleapp_main", ARTICLEAPP_URL . "assets/css/articleapp_main.css",array(),"1.0" );
			wp_enqueue_style("articleapp_main");
	}


	/**
	 * Register custom post types (article)
	 *
	 * @link http://codex.wordpress.org/Function_Reference/register_post_type
	 * @access public
	 * @return void
	 **/

	public function articleapp_post_type_article_init()
	{

		$labels = array(
			'name' => _x('Articles', 'post type general name', 'article'),
			'singular_name' => _x('Article', 'post type singular name', 'article'),
			'menu_name' => _x('Article', 'admin menu', 'article'),
			'name_admin_bar' => _x('Article', 'add new on admin bar', 'article'),
			'add_new' => _x('Add New', 'book', 'article'),
			'add_new_item' => __('Add New Article', 'article'),
			'new_item' => __('New Article', 'article'),
			'edit_item' => __('Edit Article', 'article'),
			'view_item' => __('View Article', 'article'),
			'all_items' => __('All Articles', 'article'),
			'search_items' => __('Search Articles', 'article'),
			'parent_item_colon' => __('Parent Articles', 'article'),
			'not_found' => __('No articles found', 'article'),
			'not_found_in_trash' => __('No articles found in trash bin', 'article'));

			$supports = array('title','editor','author');

			$args = array(
				'labels' => $labels,
				'description' => __('', 'article'),
				'public' => true,
				'menu_icon' => 'dashicons-welcome-write-blog',
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => array('slug' => 'article'),
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => true,
				'menu_position' => null,
				'taxonomies' => array(), // array('category', 'post_tag','page-category'),
				'supports' => $supports);

			register_post_type('article', $args);


	}


	/**
	 * Retrieved data custom post-types (article)
	 *
	 * @access public
	 * @param mixed $content
	 * @return void
	 * @link https://codex.wordpress.org/Plugin_API/Filter_Reference/the_content
	 **/

	public function articleapp_post_type_article_the_content($content)
	{

		$new_content = $content ;
		if(is_singular("article")){
			if(file_exists(ARTICLEAPP_PATH . "/includes/post_type.article.inc.php")){
				require_once(ARTICLEAPP_PATH . "/includes/post_type.article.inc.php");
				$article_content = new Article_TheContent();
				$new_content = $article_content->Markup($content);
				wp_reset_postdata();
			}
		}

		return $new_content ;

	}


	/**
	 * Register a new image size.
	 * @link http://codex.wordpress.org/Function_Reference/add_image_size
	 * @access public
	 * @return void
	 **/
	public function articleapp_image_size()
	{
	}


	/**
	 * Choose a image size.
	 * @access public
	 * @param mixed $sizes
	 * @return void
	 **/
	public function articleapp_image_sizes_choose($sizes)
	{
		$custom_sizes = array(
		);
		return array_merge($sizes,$custom_sizes);
	}


	/**
	 * Register Taxonomies
	 * @https://codex.wordpress.org/Taxonomies
	 * @access public
	 * @return void
	 **/
	public function articleapp_register_taxonomy()
	{
	}


	/**
	 * Insert Dinamic JS
	 * @param object $hooks
	 * @access public
	 * @return void
	 **/
	public function articleapp_dinamic_js($hooks)
	{
		_e("<script type=\"text/javascript\">");
		_e("</script>");
	}
}


new Article();
