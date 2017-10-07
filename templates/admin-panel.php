<?php
	
	if( isset( $_POST['submit'] ) ){
		
		$pf_settings = array(
			'message' 		=> $_POST['message'],
			'spam_words'	=> $_POST['spam_words']
		);
		
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
				<tr>
					<th scope="row"><label for="message">Message Post Submission</label></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span>Message Post Submission</span></legend>
							<p><label for="message">Message that needs to be displayed after the post has been submitted.</label></p>
							<p><textarea name="message" id="message" class="large-text code" rows="10" cols="50"><?php _e( $pf_settings['message'] );?></textarea></p>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="spam_words">Spam Words</label></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text"><span>Spam Words</span></legend>
							<p><label for="spam_words">When the post contains any of these words in its content or title, it will be put in the trash. One word per line. It will match inside words, so “press” will match “WordPress”.</label></p>
							<p><textarea name="spam_words" id="spam_words" class="large-text code" rows="10" cols="50"><?php _e( $pf_settings['spam_words'] );?></textarea></p>
						</fieldset>
					</td>
				</tr>
			</tbody>
		</table>	
		<p class="submit"><input type="submit" name="submit" id="submit" class="button" value="Save Changes"></p>
	</form>	
</div>