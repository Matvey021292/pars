<?php

define('DOMAIN', 'https://flibusta.is');
define('FOLDER', 'author_image');

$authors = DB::query("SELECT * FROM `author` WHERE id>=%s LIMIT 1", 16430);

if (!is_dir(FOLDER)) {
    mkdir(FOLDER);
}
foreach ($authors as $author) {

    if (!$author['link']) {
        echo $author['title'] . "не существует\n";
        continue;
    }

    $author_inform = get_author_page($author, DOMAIN);

    if (empty($author_inform)) {
        echo $author['title'] . "не существует\n";
        continue;
    }

    insert_author_to_db($author_inform, $author);
    download_resource($author_inform['img']);
    $books_link = array_pop($author_inform);

    if (empty($books_link)) {
        echo "У автора {$author['id']} | {$author['title']} пустой список книг !!! \n";
        continue;
    }

    foreach ($books_link as $book_link) {
        if ($book_ID = if_book_isset($book_link)) {
            echo "Книга # {$book_ID} уже существует\n";
            continue;
        }
        $book = get_book_information($book_link);

        $series = set_author_series($book['seria'], $author['id']);
        $categories = set_book_categories($book['category']);
        $book_ID = set_book_db($book['title'], $author['id'], $book_link, $series, $categories);
        set_cat_book_relationship($categories, $book_ID);
        set_author_book_relationsip($book['authors_book'], $book_ID);
        set_book_information($book_ID, $book);
        download_resource($book['image'], false);
        $rating = get_rating_book($book['rating']);
        set_rating_book($book_ID, $rating);
        set_book_format($book_ID, $book['format']);
    }
}

function get_author_page($author)
{
    $id = $author['id'];
    $author_name = $author['title'];
    $page = str_replace('https://librusec.pro', DOMAIN, $author['link']);
    $author = array();
    if ($content = check_author_page($author_name, $page)) {
        $author = get_autor_inform($content);

    } else {
        $link = serach_author_page($author_name);
        if (!empty($link)) {
            update_author_link($id, DOMAIN . $link);
            $content = check_author_page($author_name, DOMAIN . $link);
            $author = get_autor_inform($content);
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

function get_autor_inform($page)
{
    $author = array();
    $author['title'] = get_title($page);
    $img = !empty(get_book_image($page)) ? str_replace('http://cn.flibusta.is', '', get_book_image($page)) : '/190x288.jpg';
    $author['img'] = '/author_image' . $img;
    $author['description'] = !empty(get_author_desc($page)) ? get_author_desc($page) : '';
    $author['books'] = get_book_link_array($page);
    return $author;
}

/*
Добавление авторов в базу
 */

function insert_author_to_db($arrs, $author)
{
    $id = $author['id'];
    if (!get_author_inform($id)) {
        insert_author_inform($id, $arrs['title'], $arrs['img'], $arrs['description']);
        echo "Создан новый атор в базе данных - {$arrs['title']}\r\n";
    } else {
        echo "Автор {$arrs['title']} - уже существует в базе данных\r\n";
    }
}

function set_book_db($title, $author_ID, $url_book, $seria = 0, $categoties)
{
    if (!get_book($title, $url_book)) {
        echo $title . " - добавлена в базу \n";
        insert_book($author_ID, json_encode($categoties), $seria, $title, $url_book, transliteration($title));
    } else {
        echo $title . " - уже существует \n";
    }
    return get_book($title, $url_book);
}

function set_book_information($book_id, $book_arr)
{
    if (!get_book_desc($book_id)) {
        insert_book_desc($book_id, $book_arr['description'], $book_arr['image'], $book_arr['size']);
    }
    return get_book_desc($book_id);
}

function set_author_series($series, $author_ID)
{
    if (empty($series)) {
        return;
    }
    foreach ($series as $serie) {
        if (get_category($serie, $author_ID)) {
            echo "Cерия {$serie} существует в базе данных \n";
        } else {
            echo "Добавляем серию - {$serie} в базу данных\n";
            insert_category($author_ID, $serie, transliteration($serie));
        }
    }
    return get_category($serie, $author_ID);
}

function set_book_categories($categories)
{
    $categories_id = [];
    if (empty($categories)) {
        return;
    }
    foreach ($categories as $key => $category) {
        if (get_category_book($category)) {
            echo "Категория ({$category}) существует в базе данных\n";
        } else {
            echo "Создана категория ({$category}) в базе данных\n";
            insert_category_book($category, transliteration($category));
        }
        $categories_id[] = get_category_book($category);
    }
    return !empty($categories_id) ? $categories_id : null;
}

function set_book_format($book_id, $formats)
{

    $format = get_current_format($formats);
    $key = array_search($format, $formats);

    if (!get_book_format($book_id, $key)) {
        $file = get_file_download($format);
        insert_book_format($book_id, $key, $format, $file);
    }
}

function get_current_format($formats)
{
    if (isset($formats['fb2'])) {
        $format = $formats['fb2'];
    } else if (isset($formats['epub'])) {
        $format = $formats['epub'];
    } else if (isset($formats['txt'])) {
        $format = $formats['txt'];
    } else {
        $format = reset($formats);
    }
    return $format;
}

function set_cat_book_relationship($categories, $book_id)
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

function set_rating_book($id_book, $rating)
{
    if (!get_rating_db($id_book)) {
        insert_rating_db($id_book, $rating['rating'], $rating['count_rating']);
    }
}

function get_file_download($format)
{
    $url = str_replace(DOMAIN . '/', '', $format);
    $path = FOLDER . '/' . $url;

    if (!is_dir($path)) {
        wp_mkdir_p($path);
    }

    $resource = get_gurl_location(DOMAIN . '/' . $url);

    if (!file_exists(FOLDER . '/' . $url . '/' . $resource)) {
        echo "Скачивание книги - {$resource} \n";
        download_file_curl(FOLDER . '/' . $url . '/' . $resource, $format . '/' . $resource);
    } else {
        echo "{$resource} - Книга уже существует \n";
    }
    return $resource;
}

function set_author_book_relationsip($authors_work, $book_ID)
{
    if (empty($authors_work)) {
        echo "У книги $book_ID нету авторов !!!! \n";
        return;
    }
    foreach ($authors_work as $work => $authors) {
        if (empty($authors)) {
            echo "Список $work у книги $book_ID существует но пустой. Проверь !!!! \n";
            continue;
        }
        foreach ($authors as $key => $author) {
            $title = isset($author['title']) ? $author['title'] : null;
            $link = isset($author['link']) ? DOMAIN . $author['link'] : null;
            $slug = transliteration(str_replace(' ', '', $title));
            if (!$title) {
                echo "У книги $book_ID не найден заголовок !!! \n";
                return;
            }
            if (!$link) {
                echo "У книги $book_ID не найдена ссылка !!! \n";
            }
            if ($author_ID = get_author($link)) {
                echo "Автор $title существует в базе!!! \n";
            } else {
                insert_author($title, $link, $slug);
                $author_ID = get_author($link);
                echo "Автор $title добавлен в базу данных! \n";
            }
            $relationship_ID = get_book_relationship($author_ID, $book_ID, $work);
            if (!$relationship_ID) {
                set_book_relationship($author_ID, $book_ID, $work);
            }

        }
    }
}

function download_resource($image_url, $flag = true)
{
    $flag = $flag ? DOMAIN : '';
    if (isset($image_url)) {
        if (download_file($flag, $image_url)) {
            echo "Скачивание ресурса\n";
        } else {
            echo "Ресурс уже существует\n";
        }
    }
}
