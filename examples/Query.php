<?php

use Demir\Database\Query;

require __DIR__ . '/../vendor/autoload.php';

$query = new Query();

$posts = $query
  ->select('*')
  ->from('posts')
  ->where('is_featured=1')
  ->orderBy('id DESC')
  ->limit(10);

header('Content-Type:text/plain');

echo $posts . PHP_EOL;
// SELECT * FROM posts WHERE is_featured=1 ORDER BY id DESC LIMIT 10

echo $posts->select('title, body, created_at') . PHP_EOL;
// SELECT title, body, created_at FROM posts WHERE is_featured=1 ORDER BY id DESC LIMIT 10

echo (new Query)
  ->table('posts')
  ->where('is_published=1')
  ->where('AND is_featured=1')
  ->orderBy('id DESC')
  ->limit('100,150')
  ->select('slug, content, tags');
