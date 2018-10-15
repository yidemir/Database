<?php

use Demir\Database\Connection;

require __DIR__ . '/../vendor/autoload.php';

$pdo = new \PDO('sqlite:database.sqlite');
$connection = new Connection($pdo);

var_dump($pdo === Connection::get()); // true
var_dump(Connection::has('default')); // true

new Connection($pdo, 'another connection name');

var_dump(Connection::get('another connection name') === Connection::get());
