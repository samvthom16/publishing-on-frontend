<?php

	$fields = $this->get_admin_form_fields();
	
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
							<?php if( $field['type'] == 'checkbox' ) :?>
							<label for="<?php _e( $id );?>">
								<input name="<?php _e( $id );?>" type="checkbox" id="<?php _e( $id );?>" value="1"  <?php if( $pf_settings[ $id ] ) _e("checked='checked'");?> />
								<?php _e( $field['desc'] );?>
							</label>
							<?php else: ?>
							<p><label for="<?php _e( $id );?>"><?php _e( $field['desc'] );?></label></p>
							<p><textarea name="<?php _e( $id );?>" id="<?php _e( $id );?>" class="large-text code" rows="10" cols="50"><?php _e( $pf_settings[ $id ] );?></textarea></p>
							<p class="help"><?php _e( $field['help'] );?></p>
							<?php endif; ?>
						</fieldset>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>	
		<p class="submit"><input type="submit" name="submit" id="submit" class="button" value="Save Changes"></p>
	</form>	
</div>