<ul>
<?php while( $the_query->have_posts() ) : $the_query->the_post();?>
	<li>
		<?php the_title();?>
		<div style="float:right">
			<a href="?pf_action=edit&post_id=<?php the_ID();?>">Edit</a>
			&nbsp;
			<a href="?pf_action=delete&post_id=<?php the_ID();?>">Delete</a>
		</div>
	</li>
<?php endwhile;?>
<ul>
<?php $this->pagination( $the_query );?>