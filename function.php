<?php

function debug($arr)
{
    echo "<pre>";
    print_r($arr);
    echo "</pre>";
}

function transliteration($str)
{
    $cyr = [
        'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п',
        'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я',
        'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П',
        'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я',
    ];
    $lat = [
        'a', 'b', 'v', 'g', 'd', 'e', 'io', 'zh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p',
        'r', 's', 't', 'u', 'f', 'h', 'ts', 'ch', 'sh', 'sht', 'a', 'i', 'y', 'e', 'yu', 'ya',
        'A', 'B', 'V', 'G', 'D', 'E', 'Io', 'Zh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P',
        'R', 'S', 'T', 'U', 'F', 'H', 'Ts', 'Ch', 'Sh', 'Sht', 'A', 'I', 'Y', 'e', 'Yu', 'Ya',
    ];
    $str = str_replace($cyr, $lat, $str);
    $str = preg_replace('/\s*\([^)]*\)/', '', $str);
    return str_replace(' ', '', strtolower($str));
}

function file_get_contents_curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    $data = curl_exec($ch);
    curl_close($ch);
    
    return $data;
}

function get_title($page)
{
    $title = pq($page)->find('#main h1.title');
    if (!empty($title)) {
        return pq($title)->text();
    }
    
}

function get_book_image($page)
{
    $img = pq($page)->find('#main img[align="left"]');
    if (!empty($img)) {
        return pq($img)->attr('src');
    }
    
}

function get_description($page)
{
    $titles = pq($page)->find('#main h2');
    foreach ($titles as $key => $title) {
        if (pq($title)->text() == 'Аннотация') {
            $desc = pq($title)->nextAll('p');
            if (!empty($desc)) {
                return pq($desc)->html();
            }
            
        }
    }
}

function get_author_desc($page)
{
    $desc = pq($page)->find('#divabio')->html();
    $content = preg_replace('/<img[^>]*>.*?/i', '', $desc);
    if (!empty($content)) {
        return $content;
    }
    
}

function get_book_link_array($content)
{
    $links = pq($content)->find('#main form[method="POST"] input');
    $autor_books = [];
    foreach ($links as $key => $link) {
        if (!empty(pq($link)->next('a')->attr('href'))) {
            $autor_books[] = DOMAIN . pq($link)->next('a')->attr('href');
        }
    }
    if (empty($autor_books)) {
        $links = pq($content)->find('#main form[method="POST"] svg');
        foreach ($links as $key => $link) {
            if (!empty(pq($link)->next('a')->attr('href'))) {
                $autor_books[] = DOMAIN . pq($link)->next('a')->attr('href');
            }
        }
    }
    if (!empty($autor_books)) {
        return $autor_books;
    }
    
}

function download_file($domain, $url = null)
{
    $arr = [];
    $url = str_replace('/author_image', '', $url);
    $file_names = explode('/', $url);
    foreach ($file_names as $file_n) {
        if ($file_n == 'https:' || $file_n == '' || $file_n == 'flibusta.is' || $file_n == 'img' || $file_n == 'static') {
            continue;
        }
        
        $arr[] = $file_n;
    }
  
    $file_name = array_pop($arr);
    $folder = create_dir($arr);
    $destination = $folder . $file_name;
    if (file_exists($destination)) {
        return;
    }
    $ch = curl_init();
    $source = $domain . $url;
    curl_setopt($ch, CURLOPT_URL, $source);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec($ch);
    curl_close($ch);
    $file = fopen($destination, "w+");
    fputs($file, $data);
    fclose($file);
    return $destination;
}

function create_dir($dir)
{
    $fold = FOLDER;
    array_unshift($dir, $fold);
    $dir = implode('/', $dir);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    return $dir . '/';
}

function wp_mkdir_p($target)
{
    $wrapper = null;
    if (wp_is_stream($target)) {
        list($wrapper, $target) = explode('://', $target, 2);
    }
    $target = str_replace('//', '/', $target);
    if ($wrapper !== null) {
        $target = $wrapper . '://' . $target;
    }
    $target = rtrim($target, '/');
    if (empty($target)) {
        $target = '/';
    }
    if (file_exists($target)) {
        return @is_dir($target);
    }
    $target_parent = dirname($target);
    while ('.' != $target_parent && !is_dir($target_parent) && dirname($target_parent) !== $target_parent) {
        $target_parent = dirname($target_parent);
    }
    if ($stat = @stat($target_parent)) {
        $dir_perms = $stat['mode'] & 0007777;
    } else {
        $dir_perms = 0777;
    }
    if (@mkdir($target, $dir_perms, true)) {
        if ($dir_perms != ($dir_perms & ~umask())) {
            $folder_parts = explode('/', substr($target, strlen($target_parent) + 1));
            for ($i = 1, $c = count($folder_parts); $i <= $c; $i++) {
                @chmod($target_parent . '/' . implode('/', array_slice($folder_parts, 0, $i)), $dir_perms);
            }
        }
        return true;
    }
    return false;
}

function wp_is_stream($path)
{
    $scheme_separator = strpos($path, '://');
    
    if (false === $scheme_separator) {
        return false;
    }
    
    $stream = substr($path, 0, $scheme_separator);
    
    return in_array($stream, stream_get_wrappers(), true);
}

function get_gurl_location($location)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $location);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $file_name = get_name_file($result);
    return str_replace('http://static.flibusta.is:443/b.fb2/', '', $file_name);
}

function get_name_file($result)
{
    if (preg_match('~Content-Disposition: (.*)~i', $result, $match)) {
        $location = trim($match[1]);
    } else {
        preg_match('~Location: (.*)~i', $result, $match);
        $location = trim($match[1]);
    }
    $location = str_replace('attachment; filename=', '', $location);
    $location = str_replace('"', '', $location);
    return $location;
}

function download_file_curl($path, $url)
{
    exec("curl -L -o " . $path . ' ' . $url);
}

function file_name($file_url)
{
    $file_name = explode('/', $file_url);
    $file_name = $file_name[count($file_name) - 1];
    return $file_name;
}

function download_image_from_page($auhtor_folder, $content)
{
    $file = pq($content)->find('#main');
    $images = pq($file)->find('img');
    foreach ($images as $key => $image) {
        $path_url = pq($image)->attr('src');
        $file_name = file_name(DOMAIN . $path_url);
        $file_path = explode('/', $path_url);
        array_pop($file_path);
        $path = str_replace('/', '', $auhtor_folder) . implode('/', $file_path);
        if (!is_dir($path)) {
            wp_mkdir_p($path);
        }
        if (!file_exists($path . "/" . $file_name)) {
            echo "Cкачать изображение \r\n";
            download_file_curl($path . "/" . $file_name, DOMAIN . $path_url);
        }
    }
    
}

function get_page_content_book($content)
{
    $content->find('.breadcrumb')->remove();
    $content->find('#content-top')->remove();
    $file = pq($content)->find('#main');
    $document = $file->html();
    return $document;
}
