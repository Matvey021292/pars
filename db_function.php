<?php

function insert_db($arr)
{
    DB::insert('author', array(
        'title' => $arr['title'],
        'link' => $arr['link'],
    ));
}

function if_book_isset($url)
{
    $book = DB::queryFirstRow("SELECT `book` FROM `book` WHERE link=%s", $url);
    if (empty($book)) {
        return false;
    }

    return $book['book'];
}

function get_author($slug)
{
    $slug = str_replace('http://flibustahezeous3.onion', '', $slug);
    $slug = str_replace('http://flibusta.is', '', $slug);
    $author = DB::queryFirstRow("SELECT `id` FROM `author` WHERE link like '%".$slug."'");
    if (!empty($author)) {
        return $author['id'];
    }
}

function insert_author($title, $link, $slug)
{
    DB::insert('author', array(
        'title' => $title,
        'link' => $link,
        'slug' => $slug,
    ));

}

function get_book_relationship($author_id, $book_id, $db)
{
    $relation_id = DB::queryFirstRow("SELECT `id` FROM `book_" . $db . "_relationship` WHERE author_id=%i AND book_id=%i", $author_id, $book_id);
    if (!empty($relation_id)) {
        return $relation_id['id'];
    }
}

function set_book_relationship($author_id, $book_id, $db)
{
    DB::insert('book_' . $db . '_relationship', array(
        'author_id' => $author_id,
        'book_id' => $book_id,
    ));
}

function update_author_link($id, $link)
{
    DB::update('author', array(
        'link' => $link,
    ), "id=%s", $id);

}

function get_author_inform($id)
{
    $author = DB::queryFirstRow("SELECT `id` FROM `author_inform` WHERE id_author=%s", $id);
    if (!empty($author)) {
        return $author['id'];
    }

}

function get_book_content_db($id)
{
    $format = DB::queryFirstRow("SELECT `id` FROM `book_content` WHERE format_id=%s", $id);
    if (!empty($format)) {
        return $format['id'];
    }

}

function insert_book_content_db($book_id, $content)
{
    DB::insert('book_content', array(
        'format_id' => $book_id,
        'content' => $content,
    ));
}

function insert_author_inform($id, $name, $img = '', $desc = '')
{
    DB::insert('author_inform', array(
        'id_author' => $id,
        'image' => $img,
        'desc_author' => $desc,
        'name' => $name,
    ));
}

function get_category($title, $author_id)
{
    $category = DB::queryFirstRow("SELECT `id` FROM `category` WHERE category=%s AND author_id=%s", $title, $author_id);
    if (!empty($category)) {
        return $category['id'];
    }

}

function insert_category($id, $name, $category_slug)
{
    DB::insert('category', array(
        'author_id' => $id,
        'category' => $name,
        'category_slug' => $category_slug,
    ));
}

function get_book($title, $slug)
{
    $slug = str_replace('http://flibustahezeous3.onion', '', $slug);
    $slug = str_replace('http://flibusta.is', '', $slug);
    
    $book = DB::queryFirstRow("SELECT `id` FROM `book` WHERE book=%s AND link like '%".$slug."'", $title);
    if (!empty($book)) {
        return $book['id'];
    }

}

function insert_book($author_id, $category_book_id, $category_id = 0, $book, $link, $slug)
{
    DB::insert('book', array(
        'author_id' => $author_id,
        'category_book_id' => $category_book_id,
        'category_id' => $category_id,
        'book' => $book,
        'link' => $link,
        'slug' => $slug,

    ));
}

function get_rating_db($book_id)
{
    $rating_id = DB::queryFirstRow("SELECT `id` FROM `rating` WHERE book_id=%s", $book_id);
    if (!empty($rating_id)) {
        return $rating_id['id'];
    }

}

function insert_rating_db($book_id, $rating_count, $user_count)
{
    DB::insert('rating', array(
        'book_id' => $book_id,
        'rating_count' => $rating_count,
        'user_count' => $user_count,
    ));
}

function get_relationship_category($category, $book_id)
{
    $category_id = DB::queryFirstRow("SELECT `id` FROM `book_relationship` WHERE category_id=%s AND book_id=%s", $category, $book_id);
    if (!empty($category_id)) {
        return $category_id['id'];
    }

}

function insert_relationship_category($category, $book_id)
{
    DB::insert('book_relationship', array(
        'category_id' => $category,
        'book_id' => $book_id,
    ));
}

function get_category_book($cat)
{
    $category = DB::queryFirstRow("SELECT `id` FROM `category_book` WHERE category=%s", $cat);
    if (!empty($category)) {
        return $category['id'];
    }

}

function insert_category_book($category, $category_slug)
{
    DB::insert('category_book', array(
        'category' => $category,
        'slug' => $category_slug,
    ));
}

function get_book_format($book_id, $fomat)
{
    $format = DB::queryFirstRow("SELECT `id` FROM `book_format` WHERE book_id=%s AND format=%s", $book_id, $fomat);
    if (!empty($format)) {
        return $format['id'];
    }

}

function insert_book_format($book_id, $fomat, $link, $file_name = 'more')
{
    DB::insert('book_format', array(
        'book_id' => $book_id,
        'format' => $fomat,
        'link' => $link,
        'slug' => $file_name,
    ));
}

function get_book_desc($book_id)
{
    $format = DB::queryFirstRow("SELECT `id` FROM `book_description` WHERE book_id=%s", $book_id);
    if (!empty($format)) {
        return $format['id'];
    }

}

function insert_book_desc($book_id, $desc, $img, $size)
{
    DB::insert('book_description', array(
        'book_id' => $book_id,
        'book_desc' => $desc,
        'book_img' => $img,
        'size' => $size,
    ));
}


function edit_author_id_in_book($table, $book_id, $author_id){
    $col_name = 'author_id';
    if($table == 'author_inform'){
        $col_name = 'id_author';
    }
    DB::update($table, array(
        $col_name=> $author_id
        ), "id=%d", $book_id);
}