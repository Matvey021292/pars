<?php

set_time_limit(2000);
echo ini_get('max_execution_time'); // 100
// $page = 'https://librusec.pro/a/7910';
$domain = 'https://librusec.pro';

$authors = DB::query("SELECT * FROM `bk_author` WHERE id>%s AND id<=%s", 28468, 50000);
// $author = DB::queryFirstRow("SELECT * FROM `bk_author` WHERE id=%s", 9);

foreach ($authors as $author) {
	$file_name = str_replace(' ', '_', $author['title']);
	$dir = 'author';
	$folder_file = 'author/'.$file_name .'/authors_info.json';
	$page = $author['link'];
	if(!file_exists($folder_file)){
		echo "string";
		mkdir('author/'.$file_name .'/',0777, true);
		$authors = get_new_page($page, $domain );
		$fp = fopen($folder_file, 'w');
		fwrite($fp, json_encode($authors));
		fclose($fp);
		$json = file_get_contents($folder_file);
		$arrs = json_decode($json,true);
	}else{
		$json = file_get_contents($folder_file);
		$arrs = json_decode($json,true);
	}
	if(!empty($arrs)){
		$id = get_author_inform($author['id']);
		if(!$id){
			$arrs_image = isset($arrs['images']) ? $arrs['images'] : '';
			$arrs_description = isset($arrs['description']) ? $arrs['description'] : '';
			if(!empty($arrs['name'])){
				$insert = insert_author_inform((int)$author['id'], $arrs['name'], $arrs_image , $arrs_description);
			}else{
				Delete('author/'. $file_name);
				continue;
			}

		}
	// $fold = transliteration($file_name)[0];
		$folder = 'author_image';
		if(!is_dir($folder)){
			mkdir($folder);
		}
		if (isset($arrs['images'])) {
			download_file($arrs['images'],$folder);
		}
	}

	if(isset($arrs['name'])){
		unset($arrs['name']);
	}
	if(isset($arrs['images'])){
		unset($arrs['images']);
	}
	if(isset($arrs['description'])){
		unset($arrs['description']);
	}


	foreach ($arrs as $key => $category) {
		$id = (int) get_category($key, (int)$author['id'], 0);
		if(!$id){
			insert_category((int)$author['id'], $key, transliteration(str_replace(' ', '', $key)), 0);
			$id = (int) get_category($key, (int)$author['id'], 0);
		}
		foreach ($category as $key1 => $category1) {
			$id1 = (int) get_category($key1,(int)$author['id'], $id);
			if(!$id1){
				insert_category((int)$author['id'], $key1, transliteration(str_replace(' ', '', $key1)), $id);
				$id1 = (int) get_category($key1, (int)$author['id'], $id);
			}
			foreach ($category1 as $key2 => $category2) {
				if(!empty($key2)){
					$id2 = (int) get_category($key2, (int)$author['id'], $id1);
					if(!$id2){
						insert_category((int)$author['id'], $key2, transliteration(str_replace(' ', '', $key2)), $id1);
						$id2 = (int) get_category($key2,(int)$author['id'], $id1);
					}
					foreach ($category2 as $key3 => $books) {

						$books_category = $books['book_category'];
						$cat_un_arr = [];
						foreach ($books_category as $cat_key => $book_category) {
							$cat_a = array_shift($book_category);
							$cat_b = get_category_book($cat_a);
							if(!$cat_b){
								insert_category_book($cat_a,transliteration(str_replace(' ','', $cat_a)));
							}
							$cat_un_arr[] = get_category_book($cat_a);

						}

						$book = get_book($books['book'][0],$books['book'][1]);
						if(!$book){
							insert_book((int)$author['id'], serialize($cat_un_arr), $id2, $books['book'][0] ,$books['book'][1], transliteration(str_replace(' ','', $books['book'][0])));
						}
						$book = get_book($books['book'][0],$books['book'][1]);
						$books_link = $books['book_link'];
						$book_desc = get_book_desc($book);
						if(!$book_desc){
							echo $books['book'][0] . "</br>";
							$description_book = single_page_parser($books['book'][1], $domain);
							insert_book_desc($book, $description_book['description'], $description_book['image']);
							download_file($description_book['image'],$folder);
						}
						foreach ($books_link as $book_key => $book_link) {
							$book_link_formt = $book_link[0];
							$book_link_slug = $book_link[1];
							$formt = get_book_format($book, $book_link_formt);
							if(!$formt){
								insert_book_format($book, $book_link_formt,$book_link_slug);
							}
							$formt = get_book_format($book, $book_link_formt);

						}
					}
				}

			}
		}
	}
}


// $authors = DB::query('SELECT * FROM `bk_author` WHERE `id` IN (SELECT `bk_author_inform`.`id_author` FROM `bk_author_inform` WHERE `bk_author_inform`.`id` > 29246)');

// foreach ($authors as $key => $author) {
// 	$file_name = str_replace(' ', '_', $author['title']);
// 	if(is_dir('author/'. $file_name)){
// 		DB::delete('bk_author_inform', "id_author=%s", $author['id']);
// 		Delete('author/'. $file_name);
// 		echo $file_name;
	// }
// }

function Delete($path)
{
	if (is_dir($path) === true)
	{
		$files = array_diff(scandir($path), array('.', '..'));

		foreach ($files as $file)
		{
			Delete(realpath($path) . '/' . $file);
		}

		return rmdir($path);
	}

	else if (is_file($path) === true)
	{
		return unlink($path);
	}

	return false;
}

function download_file($url = null, $folder = null, $file_name = null){
	if($url){
		$arr = [];
		$file_names = explode('/',$url);
		// $file_name = explode('/',$url)[count(explode('/',$url)) - 1];
		foreach ($file_names as  $file_n) {
			if($file_n == 'https:' || $file_n == '' || $file_n == 'librusec.pro' || $file_n == 'img' || $file_n == 'static') continue;
			$arr[] = $file_n;
		}
		$file_name = array_pop($arr);
		$folder = create_dir($arr, $folder);
	}
	$destination = $folder . $file_name;
	if(!file_exists($destination)){
		$ch = curl_init();
		$source = $url;
		curl_setopt($ch, CURLOPT_URL, $source);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$data = curl_exec ($ch);
		curl_close ($ch);
		$file = fopen($destination, "w+");
		fputs($file, $data);
		fclose($file);
	}
	
}

function create_dir($dir,$fold){
	array_unshift($dir, $fold);
	$dir = implode('/', $dir);
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
	return $dir . '/';
}



// $count = 1976;
// while($count <= 1977){
// 	get_new_page($site . $count, $domain);
// 	echo  $site . $count;
// 	$count++;
// }



// function get_new_page($site,$domain){
// 	$html = file_get_contents($site);
// 	$author = [];	

// 	$content = phpQuery::newDocument( $html );
// 	$links = pq($content)->find('.bline a');
// 	foreach ($links as $key => $link) {
// 		$author['title'] = trim(pq($link)->text());
// 		$author['link'] = $domain . trim(pq($link)->attr('href'));
// 		if(!get_author($author['title'])){
// 			insert_db($author);
// 		}

// 	}

// }
// TRUNCATE TABLE `bf_book_format`;
// TRUNCATE TABLE `k_author_inform`;
// TRUNCATE TABLE `bk_book`;
// TRUNCATE TABLE `bk_book_description`;
// TRUNCATE TABLE `bk_categoty_book`;
// TRUNCATE TABLE `bk_categoty`;