<?php

$authors = DB::query("SELECT * FROM `author` WHERE id>=%s LIMIT 1", 0);

$domain = 'https://flibusta.is';
$folder = 'author_image';
if (!is_dir($folder)) {
    mkdir($folder);
}

foreach ($authors as $author) {
    if (!$author['link']) {
        echo $author['title'] . "не существует\r\n";
        continue;
    }

    $author_arr = get_author_page($author, $domain);
    if (empty($author_arr)) {
        echo $author['title'] . "не существует\r\n";
        continue;
    }
    $books = array_pop($author_arr);
    $id_author_inform = insert_author_to_db($author_arr, $author);
    if (isset($author_arr['img'])) {
        echo "Скачивание изобрадение автора\r\n";
        download_file($domain, $author_arr['img'], $folder);
    }
    if (empty($books)) {
        continue;
    }

    foreach ($books as $book) {
        $book_isset = if_book_isset($book);
        if ($book_isset) {
            echo $book_isset . "Книга уже существует\r\n";
            continue;
        }
        $book_arr = get_book_page($domain, $book);
        if(!empty($book_arr['authors_book'])){
            if(!empty($book_arr['authors_book']['original'])){
                foreach ($book_arr['authors_book']['original'] as $original => $authors_original) {
                    if(!get_author($authors_original['title'])){
                        $insert_author = insert_author($authors_original['title'], $domain . $authors_original['link'], transliteration(str_replace(' ', '', $authors_original['title'])));
                    }
                    $insert_author = get_author($authors_original['title']);
                        debug($insert_author);
                }
            }
        }
        exit();
        if (!empty($book_arr['seria'])) {
            echo "Добавляем серию в базу данных\r\n";
            $id_author_seria = inser_autor_category_to_db($book_arr['seria'], $author);
        } else {
            $id_author_seria = 0;
        }
        if (!empty($book_arr['category'])) {
            echo "Добавляем категорию в базу данных\r\n";
            $id_book_category = insert_book_category_to_db($book_arr['category']);
        } else {
            $id_book_category = 0;
        }
        $id_book = insert_book_to_db($book_arr, $author, $book, $id_author_seria, $id_book_category);
        if (!empty($id_book_category)) {
            insert_relationship_to_db($id_book_category, $id_book);
        }
        $id_book_inform = insert_book_inform_to_db($id_book, $book_arr);
        if (isset($book_arr['image'])) {
            echo "Скачивание изобрадение книги\r\n";
            download_file('', $book_arr['image'], $folder);
        }
        $rating = get_rating_book($book_arr['rating']);
        insert_rating_to_db($id_book, $rating);
        insert_book_format_to_db($id_book, $book_arr['format'], $folder, $domain);
    }
}

function get_author_page($author, $domain)
{
    $id = $author['id'];
    $author_name = $author['title'];
    $page = str_replace('https://librusec.pro', 'https://flibusta.is', $author['link']);
    $author = array();
    if ($content = check_author_page($author_name, $page)) {
        $author = get_autor_inform($domain, $content);

    } else {
        $link = serach_author_page($author_name);
        if (!empty($link)) {
            update_author_link($id, $domain . $link);
            $content = check_author_page($author_name, $domain . $link);
            $author = get_autor_inform($domain, $content);
        } else {
            update_author_link($id, 0);
        }
    }
    if (!empty($author)) {
        return $author;
    }
}

function check_author_page($author_name, $page)
{

    $page = file_get_contents_curl($page);
    $content = phpQuery::newDocument($page);
    $title = get_title($content);
    if (check_text_similar($author_name, $title)) {
        return $content;
    }
    return false;
}

function check_text_similar($author_name, $title)
{
    $author_arr = array_values(array_filter(explode('.', str_replace(' ', '.', $author_name))));
    $title_arr = array_values(array_filter(explode('.', str_replace(' ', '.', $title))));
    foreach ($title_arr as $key => $titl) {
        if (in_array($titl, $author_arr)) {
            return true;
        }
    }
    return false;
}

function serach_author_page($author_name)
{
    $author_name_unslash = str_replace(' ', '+', $author_name);
    $serach = "https://flibusta.is/booksearch?ask=$author_name_unslash";
    $page = file_get_contents_curl($serach);
    $content = phpQuery::newDocument($page);
    $link_authors = get_link_authors($content);
    if (!empty($link_authors)) {
        foreach ($link_authors as $link_author) {
            if (check_text_similar($author_name, $link_author['name'])) {
                return $link_author['href'];
            }
        }
    }
}

function get_link_authors($page)
{
    $links_arr = [];
    $links = pq($page)->find('#main ul li a');
    foreach ($links as $key => $link) {
        $links_arr[$key]['name'] = pq($link)->text();
        $links_arr[$key]['href'] = pq($link)->attr('href');
    }
    if (!empty($links_arr)) {
        return $links_arr;
    }

}

function get_autor_inform($site, $page)
{
    $author = array();
    $author['title'] = get_title($page);
    $img = !empty(get_book_image($page)) ? str_replace('http://cn.flibusta.is', '', get_book_image($page)) : '/190x288.jpg';
    $author['img'] = '/author_image' . $img;
    $author['description'] = !empty(get_author_desc($page)) ? get_author_desc($page) : '';
    $author['books'] = get_book_link_array($site, $page);
    return $author;
}

function insert_author_to_db($arrs, $author)
{
    $id = $author['id'];
    if (!get_author_inform($id)) {
        insert_author_inform($id, $arrs['title'], $arrs['img'], $arrs['description']);
        echo $arrs['title'] . " - добавлен в базу данных\r\n";

    } else {
        echo $arrs['title'] . " - уже существует в базе данных\r\n";
    }
    return get_author_inform($id);
}

function insert_book_to_db($arr_book, $author, $url_book, $seria_id = 0, $category_arr)
{
    $author_id = $author['id'];
    if (!get_book($arr_book['title'], $url_book)) {
        echo $arr_book['title'] . " - добавлена в базу\r\n";
        $slug = transliteration(str_replace(' ', '', $arr_book['title']));
        $slug = preg_replace('/\s*\([^)]*\)/', '', $slug);
        insert_book($author_id, json_encode($category_arr), $seria_id, $arr_book['title'], $url_book, $slug);
    }
    return get_book($arr_book['title'], $url_book);
}

function insert_book_inform_to_db($book_id, $book_arr)
{
    if (!get_book_desc($book_id)) {
        insert_book_desc($book_id, $book_arr['description'], $book_arr['image'], $book_arr['size']);
    }
    return get_book_desc($book_id);
}

function inser_autor_category_to_db($categories, $author)
{
    $id = $author['id'];
    foreach ($categories as $key => $category) {
        if (!get_category($category, $id)) {
            $slug = transliteration(str_replace(' ', '', $category));
            insert_category($id, $category, $slug);

        }
    }
    return get_category($category, $id);
}

function insert_book_category_to_db($categories)
{
    $cat_id = [];
    foreach ($categories as $key => $category) {
        if (!get_category_book($category)) {
            $slug = transliteration(str_replace(' ', '', $category));
            insert_category_book($category, $slug);
        }
        $cat_id[] = get_category_book($category);
    }
    return $cat_id;
}

function insert_book_format_to_db($book_id, $array_formats, $folder, $domain)
{
    foreach ($array_formats as $key => $format) {
        if (!get_book_format($book_id, $key)) {
            $url = str_replace('https://flibusta.is/', '', $format);
            $path = $folder . '/' . $url;
            if (array_key_exists('fb2', $array_formats)) {
                if ($key == 'mobi' || $key == 'epub') {
                    continue;
                }

                $res = get_file_download($path, $folder, $url, $domain);
            } else {
                $res = get_file_download($path, $folder, $url, $domain);
            }
            if (isset($res['file_name'])) {
                insert_book_format($book_id, $key, $format, $res['file_name']);
            } else if (isset($res['file_content'])) {
                insert_book_format($book_id, $key, $format);
                $book_ = get_book_format($book_id, $key);
                if (!get_book_content_db($book_)) {
                    insert_book_content_db($book_, $res['file_content']);
                }
            }
        }
    }
}

function insert_relationship_to_db($categories, $book_id)
{
    foreach ($categories as $key => $category) {
        if (!get_relationship_category($category, $book_id)) {
            insert_relationship_category($category, $book_id);
        }
    }
}

function get_rating_book($arr_rating)
{
    $arr = array();
    $ratings = explode(' ', $arr_rating);
    if ($ratings[0] == 'Оценки:') {
        $arr['count_rating'] = (int) str_replace(',', '', $ratings[1]);
        $arr['rating'] = (float) $ratings[count($ratings) - 1];
    } else {
        $arr['count_rating'] = 0;
        $arr['rating'] = 0;
    }
    return $arr;
}

function insert_rating_to_db($id_book, $rating)
{
    if (!get_rating_db($id_book)) {
        insert_rating_db($id_book, $rating['rating'], $rating['count_rating']);
    }
}

function get_file_download($path, $auhtor_folder, $url, $domain)
{
    $data_arr = array();
    if (!is_dir($path)) {
        wp_mkdir_p($path);
    }

    if (stripos($url, 'read') == 0) {
        $file_name = get_gurl_location($domain . '/' . $url);
        $file_url = $domain . '/' . $url . '/' . $file_name;
        $file_path = $auhtor_folder . '/' . $url . '/' . $file_name;
        if (!file_exists($file_path)) {
            echo "Скачивание книги " . $file_path . "\r\n";
            $file_name = file_name($file_name);
            download_file_curl($file_path, $file_url);
            $data_arr['file_name'] = $file_name;
        }
    } else {
        echo "Добавление книги " . $url . " в базу \r\n";
        // $page = file_get_contents($domain . '/' . $url);
        // $content = phpQuery::newDocument($page);
        // download_image_from_page($auhtor_folder, $content, $domain);
        // $data = get_page_content_book($content);
        // $data_arr['file_content'] = $data;
        $data_arr['file_content'] = $domain . '/' . $url;
    }
    return $data_arr;
}
