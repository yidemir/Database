<?php

use Demir\Database\{Model, Connection};

require __DIR__ . '/../vendor/autoload.php';

new Connection(new PDO('sqlite:database.sqlite'));

class Post extends Model
{
  /**
   * Eğer tablo adı belirtilmezse,
   * varsayılan tablo adı sınıf adı olur (küçük harflerle)
   */
  public static $table = 'posts';
}

/** Bütün gönderileri döndürür */
$posts = Post::get();
//$posts = Post::get('where is_featured=1');
// $posts = Post::get('where user_id=?', [10]);

/** Tekil gönderi döndürür */
$post = Post::first();
$post = Post::first('where id=?', [1]);

/** Sütun döndürür */
$postCount = Post::select('count(id)')->column();
$postTitle = Post::select('title')->column();

/** Yeni gönderi ekler */
$insert = Post::insert(['title' => 'Post Title', 'content' => 'Lorem lipsum']);
$lastId = Post::lastInsertId();

/** Varolan gönderiyi düzenler */
$update = Post::update(['title' => 'New Post Title'], 'where id=?', [5]);
$update->rowCount(); // Etkilenen/güncellenen satır sayısı

/** Gönderi siler */
$delete = Post::delete('where id=?', [5]);
$delete->rowCount(); // Etiketlenen/silinen satır sayısı

/** Sayfalama yapar */
$page = $_GET['page'] ?? 1;
$posts = Post::paginate(['perPage' => 1]);

// or
$pagesArray = Demir\Database\Pagination::getInstance()->getPages();
