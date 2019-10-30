<?php



function get_new_page($site,$domain){
	$html = file_get_contents($site);
	$author = [];	

	$content = phpQuery::newDocument( $html );
	$links = pq($content)->find('.acdn1:eq(0)');
	$author['name'] =  trim(pq($content)->find('h1.text-center')->text());
	foreach ($links as $key => $link) {
		$text_aside =  pq($link)->find('.nav-color')->text();
		if(trim($text_aside) == 'Об авторе'){
			$author['images'] = $domain . $image = pq($link)->find('.aimgcnt .imgbg img')->attr('src');
			if( pq($link)->find('.aimgcnt')->next()->attr('class') == 'mb30'){
				$author['description'] = $image = trim(pq($link)->find('.aimgcnt')->next()->html());  // HTML
			}
		}

	}
	$links = pq($content)->find('h1.text-center')->nextAll();
	if(!empty($author['images']) || !empty($author['description'])){
		$links = pq($links)->nextAll();
	}
		if($links){
			foreach ($links as $key => $link) {
				if(pq($link)->attr('class') == 'txt-b bold mt30'){
					$author[trim(pq($link)->text())] = [];
					$wrap_cat_lists = pq($link)->nextAll();
					foreach ($wrap_cat_lists as $k => $wrap_cat_list) {
						if(pq($wrap_cat_list)->attr('class') == 'txt-b bold mt30') break;
						if(pq($wrap_cat_list)->attr('class') == 'bold mt10 gray' || pq($wrap_cat_list)->attr('class') == 'bold mt30 gray'){
							$author[trim(pq($link)->text())][trim(pq($wrap_cat_list)->text())] = [];
							$wrap_cat_min_lists = pq($wrap_cat_list)->nextAll();
							$arr = [];
							foreach ($wrap_cat_min_lists as $k1 => $wrap_cat_min_list) {
								if(pq($wrap_cat_min_list)->attr('class') == 'bold mt30 gray' || pq($wrap_cat_min_list)->attr('class') == 'bold mt10 gray') break;
								$wrapp_titles = pq($wrap_cat_min_list)->find('.nav-color')->text();
								$arr[trim($wrapp_titles)] = [];
								$book_items = pq($wrap_cat_min_list)->find('.bline');
								foreach ($book_items as $k2 => $book_item) {
									$format = array('epub', 'fb2', 'mobi', 'rtf', 'txt');
									$link_book = pq($book_item)->find('a:eq(0)');
									$links_format = pq($link_book)->nextAll('a[href*="/download/"]');
									$links_category = pq($link_book)->nextAll('.control');
									$links_translate = trim(pq($link_book)->next()->text());
									$arrs = [];
									foreach ($links_format as $k3 => $link_format) {
										if(in_array(trim(pq($link_format)->text()), $format)){
											$arrs[] = array(
												trim(pq($link_format)->text()),
												$domain . trim(pq($link_format)->attr('href')));
										}
									}
									$cat_arrs_book = [];
									foreach ($links_category as $k3 => $link_category) {
											$cat_arrs_book[] = array(trim(pq($link_category)->text()));
									}
									$arr[trim($wrapp_titles)][] = array(
										'book'=> [trim(pq($link_book)->find('.txt-b')->text()),$domain . trim(pq($link_book)->attr('href'))],
										'book_link' => $arrs,
										'book_category' => $cat_arrs_book
									);
									
									
								}

							}
							$author[trim(pq($link)->text())][trim(pq($wrap_cat_list)->text())]  = $arr;
						}
					}

				}
			}
		}
		return $author;
	}
	


function single_page_parser($site,$domain){
	$html = file_get_contents($site);
	$arr = [];
	$content = phpQuery::newDocument( $html );
	$arr['description'] = pq($content)->find('.acdn1')->find('span[itemprop="description"]')->html();
	$arr['image'] = $domain . pq($content)->find('img.bgwhite')->attr('src');
	return $arr;

}


function btw($b1) {
        $b1 = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $b1);
        $b1 = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $b1);
        return $b1;
 }