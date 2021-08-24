<?php
require_once 'core/autoload.php';
$data = DB::raw('SELECT count(id) FROM article_likes GROUP BY article_id;')->get();
Helper::echoArr($data);
