
<?php

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require 'db_conf.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<style>
	.desc{
		max-height: 200px;
		overflow-y: auto;
		max-width: 100%;
	}
</style>
<body>
	<div class="container">
		<div class="row">
			<?php
			$authors = DB::query("SELECT * FROM `bk_author` WHERE id IN ( SELECT `bk_author_inform`.`id_author` FROM `bk_author_inform`)   ORDER BY `id` DESC LIMIT 10");
			foreach($authors as $key => $author){
				$image = DB::queryFirstRow("SELECT `image`,`desc_author` FROM `bk_author_inform` WHERE id_author=%s", $author['id']);
				$authors[$key]['content'] = $image;
			}
			foreach($authors as $key => $author){
				$books = DB::query("SELECT `book`,`link` FROM `bk_book` WHERE author_id=%s", $author['id']);
				$authors[$key]['book'] = $books;
			}
			// debug($authors);
			foreach($authors as $author){?>
				<?php  
				// if(!empty($author['content']['image'])){

					$img = str_replace('/author_image','author_image', $author['content']['image']);
				// }else{
				// 	$img = 'http://placehold.it/150';
				// }
				?>
				<div class="col-md-3">
					<div class="card mb-3" >
						<img src="<?php echo file_exists($img) ? $img : 'http://placehold.it/150'; ?>" class="card-img-top" alt="...">
						<div class="card-body">
							<h5 class="card-title"><a target="_blank" href="<?= $author['link']; ?>"><?= $author['title']; ?></a></h5>
							<ul>
								<?php 
								foreach ($author['book'] as $key1 => $book) {
									echo "<li><a href='" . $book['link'] . "'>" . $book['book'] . "</a></li>";
								}
								?>
							</ul>
						</div>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
</html>
<?php

function debug($arr){
	echo "<pre>";
	print_r($arr);
	echo "</pre>";
}

?>