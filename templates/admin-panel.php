<?php

	$fields = array(
		'message' => array(
			'label'	=> 'Success Message',
			'desc'	=> 'Message that needs to be displayed after the post has been submitted. Use {permalink} in the message to use the permalink of the new post.',
			'help'	=> 'Variables can be used: <br>{continueEditing} - anchor link that will help the users get back to the submission form <br>{permalink} - link of the new post that has been created. Usage: &lt;a href="{permalink}"&gt;New Post&lt;/a&gt;'
		),
		'message_draft' => array(
			'label'	=> 'Draft Message',
			'desc'	=> 'Message that needs to be displayed after the post has been saved as draft.',
			'help'	=> 'Variables can be used: <br>{continueEditing} - anchor link that will help the users get back to the submission form'
		),
		'message_spam' => array(
			'label'	=> 'Spam Notification',
			'desc'	=> 'Message to be shown for spam alert.',
			'help'	=> 'Variables can be used: <br>{continueEditing} - anchor link that will help the users get back to the submission form'
		),
		'message_empty' => array(
			'label'	=> 'Empty Post Notification',
			'desc'	=> 'Message to be shown if an empty post has been submitted.',
			'help'	=> 'Variables can be used: <br>{continueEditing} - anchor link that will help the users get back to the submission form'
		),
		'spam_words' => array(
			'label'	=> 'Spam Words',
			'desc'	=> 'When the post contains any of these words in its content or title, the post will not be submitted. One word per line. It will match inside words, so “press” will match “WordPress”.',
		)
	);
	
	if( isset( $_POST['submit'] ) ){
		
		$pf_settings = array();
		
		foreach( $fields as $id => $field ){
			$pf_settings[ $id ] = $_POST[ $id ];
		}
		
		$this->update_option( $pf_settings );
		
	}
	
	$pf_settings = $this->get_option();
	
	
	
	
	
?>



<div class="wrap">
	<div id="icon-options-general" class="icon32"></div>
	<h2><?php _e('Publishing on Frontend', 'publishing-on-frontend'); ?></h2>
	<?php settings_errors(); ?>
	<form method="post" action="">
		<table class="form-table">
			<tbody>
				<?php foreach( $fields as $id => $field ): ?>
				<tr>
					<th scope="row"><label for="<?php _e( $id );?>"><?php _e( $field['label'] );?></label></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span><?php _e( $field['label'] );?></span></legend>
							<p><label for="<?php _e( $id );?>"><?php _e( $field['desc'] );?></label></p>
							<p><textarea name="<?php _e( $id );?>" id="<?php _e( $id );?>" class="large-text code" rows="10" cols="50"><?php _e( $pf_settings[ $id ] );?></textarea></p>
							<p class="help"><?php _e( $field['help'] );?></p>
						</fieldset>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>	
		<p class="submit"><input type="submit" name="submit" id="submit" class="button" value="Save Changes"></p>
	</form>	
</div>