<?php
require_once('helpers.php');
$is_auth = rand(0, 1);
$user_name = 'Anastasya'; // укажите здесь ваше имя

$title = 'YetiCave - Главная';

$categories = ['Доски и лыжи', 'Крепления', 'Ботинки', 'Одежда', 'Инструменты', 'Разное'];
$products = [
    [
        'title' => '2014 Rossignol District Snowboard',
        'category' => 'Доски и лыжи',
        'price' => 10999,
        'url' => 'img/lot-1.jpg',
        'expiration' => '2021-08-22 22:51'
    ],
    [
        'title' => 'DC Ply Mens 2016/2017 Snowboard',
        'category' => 'Доски и лыжи',
        'price' => 159999,
        'url' => 'img/lot-2.jpg',
        'expiration' => '2021-08-22'
    ],
    [
        'title' => 'Крепления Union Contact Pro 2015 года размер L/XL',
        'category' => 'Крепления',
        'price' => 8000,
        'url' => 'img/lot-3.jpg',
        'expiration' => '2021-08-23'
    ],
    [
        'title' => 'Ботинки для сноуборда DC Mutiny Charocal',
        'category' => 'Ботинки',
        'price' => 10999,
        'url' => 'img/lot-4.jpg',
        'expiration' => '2021-08-24'
    ],
    [
        'title' => 'Куртка для сноуборда DC Mutiny Charocal',
        'category' => 'Одежда',
        'price' => 7500,
        'url' => 'img/lot-5.jpg',
        'expiration' => '2021-08-25'
    ],
    [
        'title' => 'Маска Oakley Canopy',
        'category' => 'Разное',
        'price' => 5400,
        'url' => 'img/lot-6.jpg',
        'expiration' => '2021-08-26'
    ],
];

function formatPrice($rawPrice) {
    if (is_int($rawPrice) || is_float($rawPrice)) {
        $actualPrice = ceil(intval($rawPrice));

        if ($actualPrice >= 1000) {
            $actualPrice = number_format($actualPrice, 0, '', ' ' );
        }
        return $actualPrice.' &#8381;';
    }
}

function getExpirationDate($date) {
    $currentDate = strtotime('now');
    $expiryDate = strtotime($date);

    $diff = $expiryDate - $currentDate;

    $hours = str_pad(floor($diff / 3600), 2, '0', STR_PAD_LEFT);
    $minutes = str_pad(floor(($diff % 3600) / 60), 2, "0", STR_PAD_LEFT);

    return [$hours, $minutes];
}

function createDetailProducts(array $products) {
    $detailProducts = [];

    foreach ($products as $product) {
        $hoursMinutes = getExpirationDate($product['expiration']);

        $product['hours'] = $hoursMinutes[0];
        $product['minutes'] = $hoursMinutes[1];
        $product['isNew'] = $hoursMinutes[0] < 1;

        $detailProducts[] = $product;
    }
    return $detailProducts;
}

// HTML-код блока main
$page_content = include_template('/main.php', [
    'categories' => $categories,
    'products' => createDetailProducts($products)
]);

// HTML-код блока footer
$footer_content = include_template('/footer.php', ['categories' => $categories]);

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => $title,
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'content' => $page_content,
    'footer' => $footer_content
]);

print($layout_content);
