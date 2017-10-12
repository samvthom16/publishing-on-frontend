<?php while( $the_query->have_posts() ) : $the_query->the_post();?>
	<h3><a href="??pf_action=edit&post_id=<?php the_ID();?>"><?php the_title();?></a></h3>
<?php endwhile;?>