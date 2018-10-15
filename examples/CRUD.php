<?php

use Demir\Database\{CRUD, Connection};

require __DIR__ . '/../vendor/autoload.php';

new Connection(new PDO('sqlite:database.sqlite'));
Connection::get()->exec("CREATE TABLE IF NOT EXISTS posts (id INTEGER PRIMARY KEY, title TEXT, content TEXT)");
new Connection(new PDO('sqlite:secondarydb.sqlite'), 'secondary');

$posts = CRUD::query('select * from posts')->fetchAll();
$post = CRUD::query('select * from posts where id=?', [3])->fetch();
$title = CRUD::query('select title from posts where id=3')->fetchColumn();
$postCount = CRUD::query('select count(id) from posts')->fetchColumn();
$insert = CRUD::insert('posts', ['title' => 'Post Title', 'content' => 'Lorem lipsum']);
$update = CRUD::update('posts', ['title' => 'New Post Title'], 'where id=?', [5]);
$delete = CRUD::delete('posts', 'where id=?', [5]);
$insertedCount = $insert->rowCount();
$updatedCount = $update->rowCount();
$deletedCount = $delete->rowCount(); // etkilenen satır sayısı

// Başka bir bağlantı kullanarak sorgu çalıştırma
// $secondaryDatabaseUsers = CRUD::connection('secondary')->query('select * from users')->fetchAll();