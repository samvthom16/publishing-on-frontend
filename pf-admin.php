<?php

class PF_ADMIN{
	
	var $option_name;
	
	function __construct( $option_name ){
		
		$this->option_name = $option_name;
		
		
		
		/*****Options Page Initialization*****/
		add_action('admin_menu', function(){
			add_options_page("Publishing on Frontend", "Publishing on Frontend", "edit_pages", "publishing-on-frontend", array($this, 'admin_panel'));
		});
		
		
	}
	
	function set_default_settings(){
		
		$default_settings = array();
		$fields = $this->get_admin_form_fields();
		
		foreach( $fields as $id => $field ){
			
			if( 'checkbox' == $field['type'] ){
				$default_settings[ $id ] = 0;
			}
			else{
				$default_settings[ $id ] = isset( $field['default'] ) ? $field['default'] : '';
			}
		}
		
		$this->update_option( $default_settings );
	}
	
	function admin_panel(){
		include "templates/admin-panel.php";
	}
	
	function get_admin_form_fields(){
		return array(
			'message' => array(
				'label'		=> 'Success Message',
				'type'		=> 'text',
				'desc'		=> 'Message that needs to be displayed after the post has been submitted. Use {permalink} in the message to use the permalink of the new post.',
				'help'		=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form <br>{permalink} - link of the new post that has been created. Usage: &lt;a href="{permalink}"&gt;New Post&lt;/a&gt;',
				'default'	=> 'Your post has been submitted successfully! {continueEditing}'
				
			),
			'message_draft' => array(
				'label'	=> 'Draft Message',
				'type'	=> 'text',
				'desc'	=> 'Message that needs to be displayed after the post has been saved as draft.',
				'help'	=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form',
				'default'	=> 'Your post has been saved successfully! {continueEditing}'
			),
			'message_empty' => array(
				'label'	=> 'Empty Post Notification',
				'type'	=> 'text',
				'desc'	=> 'Message to be shown if an empty post has been submitted.',
				'help'	=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form',
				'default'	=> 'Post title or content is missing! {continueEditing}'
			),
			'message_spam' => array(
				'label'	=> 'Spam Notification',
				'type'	=> 'text',
				'desc'	=> 'Message to be shown for spam alert.',
				'help'	=> 'Variables can be used: <br>{continueEditing} - button that will help the users get back to the submission form',
				'default'	=> 'Post title or content contains spam words! {continueEditing}'
			),
			'spam_words' => array(
				'label'	=> 'Spam Words',
				'type'	=> 'text',
				'desc'	=> 'When the post contains any of these words in its content or title, the post will not be submitted. One word per line.',
				'help'	=> 'It will match inside words, so "press" will match "WordPress".',
				'default'	=> ''
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
				'default'	=> '#pf-message{}'
			),
			'message_guest' => array(
				'label'		=> 'Non Logged Users',
				'type'		=> 'text',
				'desc'		=> 'Text for non-logged-in users',
				'help'		=> 'By adding text, the default redirection to login page for non-logged-in users will stop.',
				'default'	=> ''
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
		update_option($this->option_name, $settings);
		
	}
	
	function get_option(){
		return get_option($this->option_name);
	}
}