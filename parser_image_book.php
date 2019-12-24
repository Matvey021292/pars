<?php

header('Content-Type: text/html; charset=utf-8');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


require 'function.php';
require 'phpQuery-onefile.php';
require 'db_conf.php';
require 'db_function.php';
require 'flibusta.php';

$books = DB::query("SELECT link, id from book WHERE id IN (SELECT `id` FROM `book_description` WHERE book_img ='http://parser/book')" );


foreach($books as $book){
    $image = get_book_information_image($book['link']);
    
    if(empty($image)){
        echo 'Изображение не найдено';
        continue;
    }
    DB::update('book_description', array(
        'book_img' => $image['image'],
    ), "book_id=%s", $book['id']);
    echo 'Запись обновлена ' . $book['id'];
}


