<?php
require "lib/meekrodb/db.class.php";
DB::$user = 'matvey';
DB::$password = '15112016';
DB::$dbName = 'flibusta';
DB::$encoding = 'utf8'; // defaults to latin1 if omitted
// DB::$error_handler = false; // since we're catching errors, don't need error handler
// DB::$throw_exception_on_error = true;
