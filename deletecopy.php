<?php

require 'function.php';
require 'phpQuery-onefile.php';
require 'db_conf.php';
require 'db_function.php';

DB::$dbName = 'read_bk';
$authors = DB::query("SELECT * FROM author WHERE title IN (SELECT title from author GROUP BY title HAVING COUNT(title)>1 )  order by title DESC"  );
$exclude = ['id' => FALSE, 'link' => FALSE];
foreach($authors as $v) {
    $id = $v['title'];
    $record = array_diff_key( $v, $exclude );
    
    if ( ! isset($result[$id] ) ) {
        $result[$id] = $record;
        $result[$id]['authors'] = [];
    }
    $company = array_diff_key( $v, $record );
    $result[$id]['authors'][] = $company;
    $result[$id]['ids'][] = $company['id'];
}

foreach ($result as $key => $author){
    foreach($author['authors'] as $k => $author_uniq){
        $books = DB::query("SELECT id FROM book WHERE author_id = {$author_uniq['id']}");
        $author_inform = DB::query("SELECT id FROM author_inform WHERE id_author = {$author_uniq['id']}");
        $category = DB::query("SELECT id FROM category WHERE author_id = {$author_uniq['id']}");
        $book_author_relationship = DB::query("SELECT id FROM book_author_relship WHERE author_id = {$author_uniq['id']}");
        $book_translit_relship =  DB::query("SELECT id FROM book_translit_relship WHERE author_id = {$author_uniq['id']}");
        
        if($category){
            $result[$key]['authors'][$k]['category'] = $category;
        }
        
        if($author_inform){
            $result[$key]['authors'][$k]['author_inform'] = $author_inform;
        }
        
        if($book_author_relationship){
            $result[$key]['authors'][$k]['book_author_relationship'] = $book_author_relationship;
        }
        
        if($book_translit_relship){
            $result[$key]['authors'][$k]['book_translit_relship'] = $book_translit_relship;
        }
        
        if($books){
            $result[$key]['authors'][$k]['books'] = $books;
        }
        
        if(!strpos( $author_uniq['link'], '//flibusta.is/')){
            $k_ = $result[$key]['ids'][ ($k) ? 0 : 1];
            edit_author_by_table('book', $k_, $books);
            if($book_translit_relship){
                edit_author_by_table('book_translit_relship', $k_, $book_translit_relship);
            }
            if($book_author_relationship){
                edit_author_by_table('book_author_relship', $k_, $book_author_relationship);
            }
            if($category){
                edit_author_by_table('category', $k_, $category);
            }
            if($author_inform){
                edit_author_by_table('author_inform', $k_, $author_inform);
            }        
        }else{
            continue;
        }
    }
}


function edit_author_by_table($table, $k, $books){
    if(!$k) return;
    foreach ($books as $book) {
        if($book){
            edit_author_id_in_book($table, $book['id'], $k);
        }
    }
}

debug($result);
