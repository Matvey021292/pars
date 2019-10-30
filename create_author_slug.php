<?php

$authors = DB::query("SELECT * FROM `author`");


foreach ($authors as $author) {
	if(empty($author['slug'])){
	$title = preg_replace('/\s+/', '_',transliteration($author['title']));
	$title = str_replace('.','',$title);
	$title = str_replace('(','',$title);
	$title = str_replace(')','',$title);
		// DB::query("UPDATE author SET slug=%i WHERE id=%s", $title, $author['id']);
	DB::update('author', array('slug' => $title ), "id=%s", $author['id']);
	debug($title);
	}
}