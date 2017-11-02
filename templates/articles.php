<div>
<?php while( $the_query->have_posts() ) : $the_query->the_post();?>
	<?php include("article.php");?>
<?php endwhile;?>
</div>
<?php $this->pagination( $the_query );?>