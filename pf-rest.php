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
		
		add_shortcode('pf_articles', array( $this, 'articles' ), 100);
		
		add_action('the_posts', array( $this, 'assets') );
		
		/*****Options Page Initialization*****/
		add_action('admin_menu', function(){
			add_options_page("Publishing on Frontend", "Publishing on Frontend", "edit_pages", "publishing-on-frontend", array($this, 'admin_panel'));
		});
		
		/** Starts output buffer so that auth_redirect() can work in shortcodes */
		add_action('init', function(){ ob_start();});
	}
	
	function admin_panel(){
		include "templates/admin-panel.php";
	}
	
	function articles(){
		
		if ( !is_user_logged_in() ){
			return '';
		}
		
		ob_start();
		
		
		if (isset($_GET['post_id']) && isset($_GET['pf_action']) && $_GET['pf_action'] == 'edit') {
			include "templates/form.php";
		}
		else{
			$query_atts = array(
				'author' => 1,
			);
				
			$the_query = new WP_Query( $query_atts );
				
			if($the_query->have_posts()){
				include "templates/articles.php";
				wp_reset_postdata();
			}
			
		}
		
			
		return ob_get_clean();
	}
	
	/* SUBMISSION FORM */
	function form(){
		
		if ( !is_user_logged_in() ){
			
			$pf_settings = $this->get_option();
			
			if( isset( $pf_settings['message_guest'] ) && $pf_settings['message_guest'] ){
				return $pf_settings['message_guest'];
			}
			else{
				
				// REDIRECT TO LOGIN PAGE ONLY IF THE MESSAGE TO THE GUEST IS MISSING
				
				auth_redirect();
			}
			
			
			
		}
		else{
			
			ob_start();
			
			global $current_user;
			
			$current_user = wp_get_current_user();
			
			include "templates/form.php";
			
			return ob_get_clean();
		}	
		

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
				if ( $this->has_shortcode($post->post_content, 'pf_articles') ||  $this->has_shortcode($post->post_content, 'pf_form') ){
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
			wp_enqueue_script('pf-script', $uri.'js/pf-rest.js', array('wp-backbone', 'wp-api'), '2.1.3', true);
			
			// ENQUEUE MEDIA
			wp_enqueue_media();
				
			// ENQUEUE THE EDITOR
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
			
			// ENQUEUE STYLES
			wp_enqueue_style('pf-style', $uri.'style.css', false, '1.0.5' );
			
			add_action('wp_footer', array( $this, 'load_backbone_templates') );
			
		}
		
		return $posts;
		
	}
	
	function get_admin_form_fields(){
		return array(
			'message' => array(
				'label'	=> 'Success Message',
				'type'	=> 'text',
				'desc'	=> 'Message that needs to be displayed after the post has been submitted. Use {permalink} in the message to use the permalink of the new post.',
				'help'	=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form <br>{permalink} - link of the new post that has been created. Usage: &lt;a href="{permalink}"&gt;New Post&lt;/a&gt;'
			),
			'message_draft' => array(
				'label'	=> 'Draft Message',
				'type'	=> 'text',
				'desc'	=> 'Message that needs to be displayed after the post has been saved as draft.',
				'help'	=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form'
			),
			'message_empty' => array(
				'label'	=> 'Empty Post Notification',
				'type'	=> 'text',
				'desc'	=> 'Message to be shown if an empty post has been submitted.',
				'help'	=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form'
			),
			'message_spam' => array(
				'label'	=> 'Spam Notification',
				'type'	=> 'text',
				'desc'	=> 'Message to be shown for spam alert.',
				'help'	=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form'
			),
			'spam_words' => array(
				'label'	=> 'Spam Words',
				'type'	=> 'text',
				'desc'	=> 'When the post contains any of these words in its content or title, the post will not be submitted. One word per line.',
				'help'	=> 'It will match inside words, so "press" will match "WordPress".'
			),
			'disable_moderation' => array(
				'label'	=> 'Disable Moderation',
				'type'	=> 'checkbox',
				'desc'	=> 'Anyone can publish directly',
				'help'	=> ''
			),
			'enable_drafts' => array(
				'label'	=> 'Enable Drafts',
				'type'	=> 'checkbox',
				'desc'	=> 'Saving drafts before submitting the post',
				'help'	=> ''
			),
			'enable_featured_image' => array(
				'label'	=> 'Enable Featured Image',
				'type'	=> 'checkbox',
				'desc'	=> 'Enable uploading of featured image',
				'help'	=> ''
			),
			'css' => array(
				'label'		=> 'CSS Styles',
				'type'		=> 'text',
				'desc'		=> 'Apply styles to the form',
				'help'		=> 'CSS Variables:<br>Message Box: #pf-message<br>Form Buttons: #pf-cancel, #pf-submit-post, #pf-draft-post <br>
							Form Fields: #pf-title, #pf-form .mce-tinymce <br>
							Featured Image: #pf-featured-image, #pf-featured-image-container, #pf-featured-image-link <br>',
			),
			'message_guest' => array(
				'label'		=> 'Non Logged Users',
				'type'		=> 'text',
				'desc'		=> 'Text for non-logged-in users',
				'help'		=> 'By adding text, the default redirection to login page for non-logged-in users will stop.',
			),
			'cancel_link' => array(
				'label'		=> 'Cancel Link',
				'type'		=> 'textfield',
				'desc'		=> '',
				'help'		=> '',
			),
		);
	}
	
	function update_option( $settings ){
		
		// UPDATE OPTION - PF SETTINGS
		update_option('pf_settings', $settings);
		
	}
	
	function get_option(){
		return get_option('pf_settings');
	}
	
	function load_backbone_templates(){
		include "templates/backbone_templates.php";
	}
	
}




	
$pf = new PF_REST;
