<?php

function get_book_information($url)
{
    $book = [];
    $page = file_get_contents($url);
    if (!$page) {
        $page = file_get_contents($url);
        echo "Страница не загрузилась, повторная загрузка";
    }
    $content = phpQuery::newDocument($page);
    $book['title'] = get_title($content);
    $book['image'] = DOMAIN . get_book_image($content);
    $book['category'] = get_book_category($content);
    $book['description'] = get_description($content);
    $book['seria'] = get_author_category($content);
    $book['format'] = get_format($content);
    $book['size'] = get_size($content);
    $book['rating'] = get_rating($content);
    $book['authors_book'] = get_authors($content);

    $book['translate'] = get_author_translate($content);
    $arr_url = explode('/', $url);
    $id = $arr_url[count($arr_url) - 1];
    // $ajax = "http://flibusta.is/ajaxro/book?op=getBookAdvise&b=$id&async=true";
    // $pages  = file_get_contents($ajax);
    // $new_page = json_decode($pages);
    // $content_new = phpQuery::newDocument($new_page->data);
    // $book['anozer'] = get_another_book($content_new);
    return $book;
}

function get_book_category($page)
{
    $category = [];
    $cats = pq($page)->find('#main p.genre a');
    foreach ($cats as $key => $cat) {
        $category[] = pq($cat)->text();
    }
    if (!empty($category)) {
        return $category;
    }

}

function get_another_book($page)
{
    $books_arr = [];
    $books = pq($page)->find('img');
    foreach ($books as $key => $book) {
        $books_arr[] = pq($book)->next('a')->text();
    }
    if (!empty($books_arr)) {
        return $books_arr;
    }

}

function get_author_category($page)
{
    $categories = [];
    $cats = pq($page)->find('#main p.genre')->next('a');
    foreach ($cats as $key => $cat) {
        $categories[] = pq($cat)->text();
    }
    if (!empty($categories)) {
        return $categories;
    }

}

function get_format($page)
{
    $links_arr = [];
    $links = pq($page)->find('#main span[style="size"]')->nextAll('a');
    foreach ($links as $key => $link) {
        $format = str_replace('(', '', pq($link)->text());
        $format = str_replace(')', '', $format);
        $links_arr[$format] = DOMAIN . pq($link)->attr('href');
    }
    if (!empty($links_arr)) {
        return $links_arr;
    }

}

function get_author_translate($page)
{
    $links_arr = [];
    $links = pq($page)->find('#main .g-sf_fantasy')->prevAll('a');
    foreach ($links as $key => $link) {
        $links_arr[] = pq($link)->text();
    }
    if (!empty($links_arr)) {
        return $links_arr;
    }

}

function get_rating($page)
{
    $title = pq($page)->find('#main table p');
    if (!empty($title)) {
        return pq($title)->text();
    }

}

function get_authors($page)
{
    $authors = pq($page)->find('#main');
    $authors_lists = pq($page)->find('#content-top')->next('.title')->next('script')->nextAll('a[href*="/a/"]');
    foreach (pq($authors_lists) as $key => $value) {
        if (pq($value)->html() != ' ' && pq($value)->html() != ', ') {
            $author_list['author'][] = [
                'title' => pq($value)->html(),
                'link' => pq($value)->attr('href'),
            ];
        }
    }
    $author = $authors;
    pq($author)->find('form,br,table,div,hr,a[href*="/g/"],h1,p,span,img,script')->remove();
    pq($author)->find('a[href*="/polka"]')->nextAll()->remove();
    pq($author)->find('a[href*="/polka"]')->remove();
    preg_match('/\(перевод:(.*)\)/', pq($author)->html(), $match);
    if (isset($match[1])) {
        foreach (pq($match[1]) as $key => $value) {
            if (pq($value)->html() != ' ' && pq($value)->html() != ', ') {
                foreach ($author_list['author'] as $k => $v) {
                    if ($v['link'] == pq($value)->attr('href') && $v['title'] == pq($value)->html()) {
                        unset($author_list['author'][$k]);
                    }
                }
                $author_list['translation'][] = [
                    'title' => pq($value)->html(),
                    'link' => pq($value)->attr('href'),
                ];
            }
        }
    }
    return $author_list;
}

function get_size($page)
{
    $title = pq($page)->find('#main span[style="size"]');
    if (!empty($title)) {
        return pq($title)->text();
    }

}

function file_get_contents_status($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpcode == 200) {
        if (page_not_found($url)) {
            return true;
        }
    }
    return false;
}

function page_not_found($url)
{
    $data = file_get_contents_curl($url);
    $page = file_get_contents($url);
    if (strpos(htmlspecialchars($page), 'jQuery(document).ready(libRB)') == 0) {
        return true;
    } else {
        return false;
    }

}
