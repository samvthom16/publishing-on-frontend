<div>
<?php while( $the_query->have_posts() ) : $the_query->the_post();?>
	<?php the_pf_article();?>
<?php endwhile;?>
</div>
<?php the_pf_pagination( $the_query );?>