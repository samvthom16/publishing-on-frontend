<?php

	$pf_settings = $this->get_option();
	
?>
<script id="tmpl-pf-form" type="text/html">
	<div id="pf-message" class="warning"></div>
	<form id="pf-form">
		<input placeholder="Title" type="text" name="post_title" id="pf-title" value="{{data.title.rendered}}">
		<textarea id="pf-content" name="post_content" style="height: 400px;">{{data.content.rendered}}</textarea>
		<?php if( isset( $pf_settings['enable_featured_image'] ) && $pf_settings['enable_featured_image'] ):?>
		<div id="pf-featured-image">
			<div id="pf-featured-image-container" style="background-image: url('{{data.featured_image}}');"></div>
			<a id="pf-featured-image-link" href="#">Choose Featured Image</a>
			<input type="hidden" name="post_image" value="{{data.featured_media}}" />
		</div>
		<?php endif;?>
		<ul id="list-buttons">
			<li><button type="button" id="pf-submit-post" data-status="publish">Submit</button></li>
			<?php if( isset( $pf_settings['enable_drafts'] ) && $pf_settings['enable_drafts'] ):?>
			<li><button type="button" id="pf-draft-post" data-status="draft">Save Draft</button></li>
			<?php endif;?>
			<?php if( isset( $pf_settings['cancel_link'] ) && $pf_settings['cancel_link'] ):?>
			<li>or</li>
			<li><a id="pf-cancel" href="<?php echo( $pf_settings['cancel_link'] );?>">Cancel</a></li>
			<?php endif;?>
			<li><i class="fa fa-refresh fa-spin"></i></li>
		</ul>
	</form>
</script>
<?php if( isset( $pf_settings['css'] ) ):?>
<style><?php echo( $pf_settings['css'] );?></style>
<?php endif; ?>
