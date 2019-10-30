<?php

$table = 'book_description';
$table_row = 'book_img';
$table_db = DB::query("SELECT * FROM `book_description` WHERE `book_img` <> '/images/190x288.jpg'");

// var_dump($table_db);
// debug($table_db);


// foreach ($authors as $author) {
// 	if(empty($author['slug'])){
// 	$title = preg_replace('/\s+/', '_',transliteration($author['title']));
// 	$title = str_replace('.','',$title);
// 	$title = str_replace('(','',$title);
// 	$title = str_replace(')','',$title);
// 		// DB::query("UPDATE author SET slug=%i WHERE id=%s", $title, $author['id']);
// 	DB::update('author', array('slug' => $title ), "id=%s", $author['id']);
// 	debug($title);
// 	}
// }