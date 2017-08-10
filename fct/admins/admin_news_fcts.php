<?php

// ajoute une news en BDD
function add_news ($title, $text, $visible, $creatorID) {
	if (!$title && !$text) { throw new Exception ('$title or $text undefined !'); return; }
	$n = new News();
	$n->setCreator($creatorID);
	$n->setTitle(addslashes($title));
	$n->setText(addslashes($text));
	$n->setVisibility($visible);
	$n->setDate(date('Y-m-d H:i:s'));
	$n->save();
}

// modifie les infos d'une nouvelle en BDD
function mod_news_title_text($idNews, $title=false, $text=false) {
	if (!$title && !$text) { throw new Exception ('$title or $text undefined !'); return; }
	$n = new News($idNews);
	$n->setTitle(addslashes($title));
	$n->setText(addslashes($text));
	if ($idNews != 1)
		$n->setDate(date('Y-m-d H:i:s'));
	$n->save();
	return date('d/m/Y à H\hi');
}

// modifie la publication d'une nouvelle en BDD
function mod_news_visibility($idNews, $visibility) {
	$n = new News($idNews);
	$n->setVisibility($visibility);
	$n->save();
	return (int)$visibility;
}

// supprime une nouvelle en BDD
function delete_news ($idNews) {
	$n = new News($idNews);
	$n->delete();
}

?>