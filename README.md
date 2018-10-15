# Demir Database

## Introduction
Simple and lightweight database package

## Features
* Multiple connection class
* Simple CRUD operations (create, read, update, delete) class
* Abstract model class (Simple ORM)
* Powerful pagination class
* Simple query builder

Documentation is coming soon.

Türkçe dökümantasyona [buradan](https://yilmazdemir.com.tr/demir-database-veritabani-sinifi) ulaşabilirsiniz.

## Installation
The recommended way to install is via Composer:
```bash
$ composer require yidemir/database
```

## Usage

### Adding Connection
```php
use Demir\Database\Connection;

$pdo = new \PDO('sqlite:db.sqlite');
$connection = new Connection($pdo);

$secondaryPdo = new \PDO('sqlite:secondary.sqlite');
$secondary = new Connection($secondaryPdo, 'secondary');

// default connection
$pdo = Connection::get();

// another connection
$secondary = Connection::get('secondary');
```

### CRUD Operations
If you are going to use the CRUD class, you need to create the connection from the Connection class.

Assume that you create the connection class as above.

```php
use Demir\Database\CRUD;

CRUD::query('select * from posts where id=?', [12])->fetch();

// The Query method returns the PDOStatement class:

CRUD::query('select * from posts')->fetchAll();

// You can select the desired database connection and perform the operation:
CRUD::connection('secondary')->query('select * from users')->fetchAll();

// Set default connection
CRUD::connection('default');

// INSERT operation:
CRUD::insert('posts', ['slug' => 'foo', 'content' => 'Foo']);
// Duplicate key update:
CRUD::insert('posts', ['slug' => 'foo', 'content' => 'Foo'], ['content' => 'Foo']);

// UPDATE operation:
CRUD::update('users', ['name' => 'Foo']);
CRUD::update('users', ['name' => 'Foo'], 'where id=?', [5]);

// DELETE operation:
CRUD::delete('users', 'where id=?', [5]);
```

### Abstract Model Class (Simple ORM)
```php
use Demir\Database\Model;

class Posts extends Model {}

$posts = Posts::get();
$featuredPosts = Posts::get('where is_featured=1');
$spesificFields = Posts::select('id, title, body')->get();

$post = Posts::first('where id=?', [$id]);
$postTitle = Posts::select('title')->column('where id=?', [$id]);
$postCount = Posts::select('count(*)')->column();

Posts::insert(['title' => 'Post title']);
Posts::insert(
  $insertData = ['slug' => 'foo', 'title' => 'foo'],
  $onDuplicateKeyUpdate = ['title' => 'foo']
);

$lastInsertId = Posts::lastInsertId();

Posts::update(['title' => 'New post title'], 'where id=?', [5]);
// or
Posts::update(['title' => ''], 5);

Posts::delete(); // delete all posts
Posts::delete('where id=?', [5]);
Posts::delete(5);
```

#### Paginating Data
```php
$posts = Posts::paginate();
// defaults: perPage: 10, currentPage: $_GET['page'], url: ?page={number}
$pagesArray = Posts::getPagination()->getPages();

$posts = Posts::paginate([
  'perPage' => 5,
  'currentPage' => $_GET['page'] ?? 1,
  'url' => '/foo/bar?page={number}',
]);

$posts = Posts::paginate([ .. ], 'where is_published=?', [1]);
```


#### Model configuration
```php
class Post extends Model
{
  public static $table = 'posts_table_name';
  public static $primaryKey = 'post_id';
  public static $connection = 'secondary_database_name';
}

// all static class variables are optional
```

### Powerful Pagination Class
Example:
```php
use Demir\Database\Pagination;

$items = ['foo', 'bar', 'baz', 'qux', 'mux', 'foo'];
$page = $_GET['page'] ?? 1;
$pagination = new Pagination(count($items), $perPage = 2, $page);
[$start, $end] = explode(',', $pagination->getLimit());
$paginatedItems = array_slice($items, $start, $end);
$pagesArray = $pagination->getPages();
```

### Query Builder Class
Example:
```php
use Demir\Database\Query;

$query = new Query();

$posts = $query
  ->select('*')
  ->from('posts')
  ->where('is_featured=1')
  ->orderBy('id DESC')
  ->limit(10);
  
echo $posts;
// SELECT * FROM posts WHERE is_featured=1 ORDER BY id DESC LIMIT 10

echo $posts->select('title, body, created_at');
// SELECT title, body, created_at FROM posts WHERE is_featured=1 ORDER BY id DESC LIMIT 10

echo (new Query)
  ->table('posts')
  ->where('is_published=1')
  ->where('AND is_featured=1')
  ->orderBy('id DESC')
  ->limit('100,150')
  ->select('slug, content, tags');
  
// SELECT slug, content, tags FROM posts WHERE is_published=1 AND is_featured=1 ORDER BY id DESC limit 100,150

echo (new Query)->table('users')->where('id=?');
// SELECT * FROM users WHERE id=?
```