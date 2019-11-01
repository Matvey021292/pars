<?php
require 'function.php';

function getDirContents($dir, &$results = array()){
    $files = scandir($dir);
    foreach($files as $key => $value){
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if(!is_dir($path)) {
        	if(strpos($path, '.jpg.jpg') == true)
            $paths = str_replace('.jpg.jpg', '.jpg', $path);
            echo $paths . "</br>";
            echo $path . "</br>";
        	var_dump(rename($path, $paths));
        } else if($value != "." && $value != "..") {
            getDirContents($path, $results);
            if(!is_dir($path)){
            	$results[] = $path;
            }

        }

    }

    return $results;
}

getDirContents('../../test.com/html/bookread/public/book/author_image/');