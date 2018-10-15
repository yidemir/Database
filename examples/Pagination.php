<?php

use Demir\Database\Pagination;

require __DIR__ . '/../vendor/autoload.php';

$items = [
  '1.foo', '2.bar', '3.baz', '4.qux', '5.mux', '6.foo'
];

$count = count($items);
$page = $_GET['page'] ?? 1;

$pagination = new Pagination($count, $perPage = 2, $page);

[$start, $end] = explode(',', $pagination->getLimit());

// paginated items:
var_dump(
  array_slice($items, $start, $end)
);

// page numbers:
var_dump($pagination->getPages());