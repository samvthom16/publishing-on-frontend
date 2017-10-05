<?php
/*
Plugin Name: Publish on the Front End Using Rest API
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.
Author: Sputznik
Version: 1.0
Author URI: http://sputznik.com/
*/

class PF_REST{
	
	function __construct(){
		
		add_shortcode('pf_form', array( $this, 'form' ), 100);
		
		add_action('the_posts', array( $this, 'assets') );
		
	}
	
	/* SUBMISSION FORM */
	function form(){
		ob_start();
		if ( !is_user_logged_in() ){
			
			echo "Not logged in.";
			
		}
		else{
			global $current_user;
			
			$current_user = wp_get_current_user();
			
			include "templates/form.php";
		}	
		return ob_get_clean();

	}
	
	/* CHECK IF THE CONTENT HAS THE SHORTCODE */
	function has_shortcode( $content, $tag ) {
		if(stripos($content, '['.$tag.']') !== false)
			return true;
		return false;
	}
	
	/* LOAD SCRIPTS AND STYLES IF THE SHORTCODE IS USED */
	function assets($posts){
		
		$found = false;
		if ( !empty($posts) ){
			foreach ($posts as $post) {
				if ( $this->has_shortcode($post->post_content, 'fep_article_list') ||  $this->has_shortcode($post->post_content, 'pf_form') ){
					$found = true;
					break;
				}
			}	
		}
 
		if( $found ){
			
			$uri = plugin_dir_url( __FILE__ );
			
			// ENQUEUE SCRIPT
			wp_enqueue_script('underscore');
			wp_enqueue_script('backbone');
			wp_enqueue_script('pf-script', $uri.'js/pf-rest.js', array('wp-backbone', 'wp-api'), '1.0.8', true);
			
			// ENQUEUE MEDIA
			wp_enqueue_media();
				
			// ENQUEUE THE EDITOR
			wp_enqueue_editor();
			
			// ENQUEUE STYLES
			wp_enqueue_style('pf-style', $uri.'style.css', false, '1.0.4' );
			
			add_action('wp_footer', array( $this, 'load_backbone_templates') );
			
		}
		
		
		return $posts;
		
	}
	
	function load_backbone_templates(){
		include "templates/backbone_templates.php";
	}
	
}


	
$pf = new PF_REST;
