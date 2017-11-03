<?php

function the_pf_permalink(){
	global $pf;
	echo $pf->get_current_page_permalink();
}

function the_pf_article_edit_link(){
	global $pf;
	echo $pf->get_current_page_permalink()."&pf_action=edit&post_id=".get_the_ID();
}

function the_pf_article_delete_link(){
	global $pf;
	echo $pf->get_current_page_permalink()."&pf_action=delete&post_id=".get_the_ID();
}

function the_pf_article(){
	global $pf;
	$pf->include_template_file( "pf-theme-templates/article.php" );
}

function the_pf_article_action_btns(){
	global $pf;
	$pf->include_template_file( "pf-theme-templates/article-action-btns.php" );
}

function the_pf_article_edit_btn(){
	global $pf;
	$pf->include_template_file( "pf-theme-templates/article-edit-btn.php" );
}

function the_pf_article_delete_btn(){
	global $pf;
	$pf->include_template_file( "pf-theme-templates/article-delete-btn.php" );
}

function the_pf_pagination($the_query){
	global $pf;
	$pf->pagination( $the_query );
}
