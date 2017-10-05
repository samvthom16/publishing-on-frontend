<script id="tmpl-pf-form" type="text/html">
	<div id="pf-message" class="warning"></div>
	<form id="pf-form">
		<input placeholder="Title" type="text" name="post_title" id="pf-title" value="{{data.title.rendered}}">
		<textarea id="pf-content" name="post_content" style="height: 400px;">{{data.content.rendered}}</textarea>
		<div id="pf-featured-image">
			<div id="pf-featured-image-container" style="background-image: url('{{data.featured_image}}');"></div>
			<a id="pf-featured-image-link" href="#">Choose Featured Image</a>
			<input type="hidden" name="post_image" value="{{data.featured_media}}" />
		</div>
		<ul id="list-buttons">
			<li><button type="button" id="pf-submit-post" data-status="publish">Submit</button></li>
			<li><button type="button" id="pf-draft-post" data-status="pending">Save Draft</button></li>
			<li>or</li>
			<li><a id="pf-cancel" href="" style="text-decoration: underline;">Cancel</a></li>
			<li><i class="fa fa-refresh fa-spin"></i></li>
		</ul>
	</form>
</script>

