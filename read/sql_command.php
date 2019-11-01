
Обновить изображения
UPDATE book_description SET book_img = replace(book_img, 'https://librusec.pro/static/img/', '/author_image/') WHERE book_img like '%https://librusec.pro/static/img/%'


Добавить .jpg

UPDATE book_description SET book_img = CONCAT(book_img, '.jpg') WHERE book_img <> '/images/190x288.jpg' AND book_img NOT LIKE '%.jpg%'