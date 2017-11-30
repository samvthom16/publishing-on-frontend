<?php
/*
Plugin Name: Publish on the Front End Using Rest API
Plugin URI: http://wordpress.org/plugins/publishing-on-frontend/
Description: Publishing on front end using WP REST API. Using the WP Backbone API and REST API. 
Author: Sputznik
Version: 1.0
Author URI: http://sputznik.com/
*/


defined('ABSPATH') or die('Permission Denied!!');


class PF_REST{
	
	var $admin;
	var $option_name;
	
	/** CLASS CONSTRUCTOR */
	function __construct(){
		
		$this->option_name = 'pf_settings';
		
		/** INITIALIZE ADMIN PANEL SETTINGS */
		include("pf-admin.php");
		$this->admin = new PF_ADMIN( $this->option_name );
		
		/* SET DEFAULT SETTINGS ONCE ACTIVATED */
		register_activation_hook(__FILE__, function(){
			$this->admin->set_default_settings();
		});
		
		/* DELETE CUSTOMIZE OPTIONS IF THE PLUGIN HAS BEEN DEACTIVATED */
		register_deactivation_hook(__FILE__, function(){
			delete_option( $this->option_name );
		});
		
		/** REGISTER SHORTCODES */
		add_shortcode('pf_form', array( $this, 'form' ), 100);
		add_shortcode('pf_articles', array( $this, 'articles' ), 100);
		/** END OF REGISTRATION */
		
		/** TO LOAD THE ASSETS - SCRIPTS AND STYLES */
		add_action('the_posts', array( $this, 'assets') );
		
		/** STARTS OUTPUT BUFFER FOR auth_redirect() TO WORK IN SHORTCODES */
		add_action('init', function(){ ob_start();});
	}
	
	
	/** MESSAGE FOR GUESTS/NON LOGGED-IN USERS ON PAGES WITH SHORTCODES */
	function guest_message(){
		$pf_settings = $this->get_option();
			
		if( isset( $pf_settings['message_guest'] ) && $pf_settings['message_guest'] ){
			return $pf_settings['message_guest'];
		}
		
		return false;
	}
	
	/** PERMALINK OF THE PAGE THAT IS USING THE SHORTCODE */
	function get_current_page_permalink(){
		global $wp_query;
		
		if( $wp_query )
			return $wp_query->queried_object->guid;
		
		return '';
	}
	
	// PAGINATION LINK OF THE ARTICLES DISPLAYED THROUGH THE LIST SHORTCODE
	function get_pagenum_link( $page ){
		
		global $wp_query;
		
		return $this->get_current_page_permalink()."&pf_paged=".$page;
		
		
	}
	
	function pagination( $wp_query ) {
		
		/** STOP EXECUTION IF THERE IS ONLY 1 PAGE */
		if( $wp_query->max_num_pages <= 1 )
			return;
		
		/** GET QUERY PARAMETERS */
		$paged = isset( $_GET['pf_paged'] ) ? absint( $_GET['pf_paged'] ) : 1;
		$max   = intval( $wp_query->max_num_pages );
	 
		/** ADD CURRENT PAGE TO THE ARRAY */
		if ( $paged >= 1 )
			$links[] = $paged;
	 
		/** ADD THE PAGES AROUND THE CURRENT PAGE TO THE ARRAY */
		if ( $paged >= 3 ) {
			$links[] = $paged - 1;
			$links[] = $paged - 2;
		}
		
		/* FOR THE LAST TWO PAGE LINKS */
		if ( ( $paged + 2 ) <= $max ) {
			$links[] = $paged + 2;
			$links[] = $paged + 1;
		}
	 
		echo '<div class="pf-navigation"><ul>' . "\n";
	 
		/** Previous Post Link */
		if ( get_previous_posts_link() )
			printf( '<li>%s</li>' . "\n", get_previous_posts_link() );
	 
		/** Link to first page, plus ellipses if necessary */
		if ( ! in_array( 1, $links ) ) {
			$class = 1 == $paged ? ' class="active"' : '';
	 
			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( $this->get_pagenum_link( 1 ) ), '1' );
	 
			if ( ! in_array( 2, $links ) )
				echo '<li>…</li>';
		}
	 
		/** Link to current page, plus 2 pages in either direction if necessary */
		sort( $links );
		foreach ( (array) $links as $link ) {
			$class = $paged == $link ? ' class="active"' : '';
			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( $this->get_pagenum_link( $link ) ), $link );
		}
	 
		/** Link to last page, plus ellipses if necessary */
		if ( ! in_array( $max, $links ) ) {
			if ( ! in_array( $max - 1, $links ) )
				echo '<li>…</li>' . "\n";
	 
			$class = $paged == $max ? ' class="active"' : '';
			printf( '<li%s><a href="%s">%s</a></li>' . "\n", $class, esc_url( $this->get_pagenum_link( $max ) ), $max );
		}
	 
		/** Next Post Link */
		if ( get_next_posts_link() )
			printf( '<li>%s</li>' . "\n", get_next_posts_link() );
	 
		echo '</ul></div>' . "\n";
	 
	}
	
	/** LIST OF AUTHOR POSTS */
	function articles(){
		
		if ( !is_user_logged_in() ){
			$message = $this->guest_message();
			
			if( !$message ){
				// REDIRECT TO LOGIN PAGE ONLY IF THE MESSAGE TO THE GUEST IS MISSING
				auth_redirect();
			}
			else{
				return $message;
			}
		}
		else{
			
			ob_start();
		
			
			if (isset($_GET['post_id']) && isset($_GET['pf_action']) && $_GET['pf_action'] == 'edit') { /* EDIT FORM FOR ARTICLE */
				
				include "templates/form.php";
			}
			else if (isset($_GET['post_id']) && isset($_GET['pf_action']) && $_GET['pf_action'] == 'delete') { /* DELETE ARTICLES */
				
				/** CHECK FOR PERMISSIONS */
				if (!current_user_can('delete_post', $_GET['post_id']))
					throw new Exception(__("You don't have permission to delete this post", 'publishing-on-frontend'), 1);

				/** DELETE POST */
				$result = wp_delete_post($_GET['post_id'], true);
				
				/** CHECK IF ARTICLE HAS BEEN DELETED */
				if (!$result)
					throw new Exception(__("The article could not be deleted", 'publishing-on-frontend'), 1);
				
				/** REDIRECT TO THE CURRENT PAGE, IF ALL GOES WELL */
				wp_redirect($this->get_current_page_permalink());
			}
			else{
				
				/** LIST OF ARTICLES FOR THE CURRENT AUTHOR */
				
				$query_atts = array(
					'author' 		=>  get_the_author_meta( 'ID' ), // CURRENT AUTHOR
					'post_status'	=> 'any',						 // GET POSTS OF ALL STATUS
					'posts_per_page'=> 5,							
					'paged'			=> isset( $_GET[ 'pf_paged' ] ) ? $_GET[ 'pf_paged' ] : 1
				);
					
				$the_query = new WP_Query( $query_atts );
					
				if( $the_query->have_posts() ){
					include "templates/articles.php";
					wp_reset_postdata();
				}
				
			}
			
				
			return ob_get_clean();
			
		}
		
		
	}
	
	/** SHORTCODE TO THE SUBMISSION FORM */
	function form(){
		
		if ( !is_user_logged_in() ){
			
			$message = $this->guest_message();
			
			if( !$message ){
				// REDIRECT TO LOGIN PAGE ONLY IF THE MESSAGE TO THE GUEST IS MISSING
				auth_redirect();
			}
			else{
				return $message;
			}
			
		}
		else{
			
			/** DISPLAY THE TEMPLATE FOR THE SUBMISSION FORM */
			
			ob_start();
			
			global $current_user;
			
			$current_user = wp_get_current_user();
			
			include "templates/form.php";
			
			return ob_get_clean();
		}	
		

	}
	
	/** CHECK IF THE CONTENT HAS THE SHORTCODE */
	function has_shortcode( $content, $tag ) {
		if(stripos($content, '['.$tag.']') !== false)
			return true;
		return false;
	}
	
	/** LOAD SCRIPTS AND STYLES IF THE SHORTCODE IS USED */
	function assets($posts){
		
		$found = false;
		if ( !empty($posts) ){
			foreach ($posts as $post) {
				if ( $this->has_shortcode($post->post_content, 'pf_articles') ||  $this->has_shortcode($post->post_content, 'pf_form') ){
					$found = true;
					break;
				}
			}	
		}
 
		if( $found ){
			
			$uri = plugin_dir_url( __FILE__ );
			
			/** ENQUEUE SCRIPTS */
			wp_enqueue_script('underscore');
			wp_enqueue_script('backbone');
			wp_enqueue_script('pf-script', $uri.'js/pf-rest.js', array('wp-backbone', 'wp-api'), '2.1.3', true);
			
			/** ENQUEUE MEDIA */
			wp_enqueue_media();
				
			/** ENQUEUE THE EDITOR */
			wp_enqueue_editor();
			
			$pf_settings = $this->get_option();
			
			wp_localize_script('pf-script', 'pf_settings', array(
				'message' 			=>  str_replace("\r\n","<br>", stripslashes($pf_settings['message'])),
				'message_spam' 		=>  str_replace("\r\n","<br>", stripslashes($pf_settings['message_spam'])),	
				'message_draft' 	=>  str_replace("\r\n","<br>", stripslashes($pf_settings['message_draft'])),	
				'message_empty' 	=>  str_replace("\r\n","<br>", stripslashes($pf_settings['message_empty'])),	
				'spam_words'		=> ($pf_settings['spam_words']) ? explode("\r\n", $pf_settings['spam_words']) : array(),
				'disable_moderation'=> ($pf_settings['disable_moderation']) ? 1 : 0
			));
			
			/** ENQUEUE STYLES */
			wp_enqueue_style('pf-style', $uri.'style.css', false, '1.0.6' );
			
			add_action('wp_footer', array( $this, 'load_backbone_templates') );
			
		}
		
		return $posts;
		
	}
	
	function get_option(){
		return get_option($this->option_name);
	}
	
	
	function load_backbone_templates(){
		include "templates/backbone_templates.php";
	}
	
	// CHECK IF THE TEMPLATE FILE EXISTS IN THE THEME
	function include_template_file( $template_url ){
		if( file_exists( get_stylesheet_directory()."/".$template_url ) ){
			include( get_stylesheet_directory()."/".$template_url );
		}
		else{
			include( $template_url );
		}
	}
	
}

global $pf;	
$pf = new PF_REST;


include("the.php");
