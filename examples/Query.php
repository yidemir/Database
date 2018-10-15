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
  ->select('slug, content, tags') . PHP_EOL;

echo (new Query)
  ->table('posts')
  ->insert([
    'title' => 'foo',
    'body' => 'bar',
    'created_at' => 2018
  ]) . PHP_EOL;
// INSERT INTO posts (title,body,created_at) VALUES (?,?,?)

echo (new Query)
  ->table('posts')
  ->insert([
    'title' => 'foo',
    'body' => 'bar',
    'created_at' => 2018
  ])
  ->onDuplicateKeyUpdate([
    'foo' => 'bar',
    'bar' => 'baz',
    'baz' => 'qux'
  ]) . PHP_EOL;
// INSERT INTO posts (title,body,created_at) VALUES (?,?,?) ON DUPLICATE KEY UPDATE foo=?,bar=?,baz=?

echo (new Query)->table('posts')->insert('(title, body, created_at) VALUES (?,?,?)') . PHP_EOL;
// INSERT INTO posts (title, body, created_at) VALUES (?,?,?)

echo (new Query)->table('posts')->insert('(title) VALUES (?)')->onDuplicateKeyUpdate('foo=?, bar=?') . PHP_EOL;
// INSERT INTO posts (title) VALUES (?) ON DUPLICATE KEY UPDATE foo=?, bar=?

echo (new Query)->table('users')->where('id=12')->update(['username' => 'foo']) . PHP_EOL;
// UPDATE users SET username=? WHERE id=12

echo (new Query)->table('users')->where('id=20')->update('username=?, password=?') . PHP_EOL;
// UPDATE users SET username=?, password=? WHERE id=20

echo (new Query)->table('posts')->where('id=5')->delete()->orderBy('id DESC');
// DELETE FROM posts WHERE id=5 ORDER BY id DESC