<?php
require_once 'helpers.php';
require_once 'db.php';
require_once 'getwinner.php';

$sqlProducts = 'SELECT lots.id as id, lots.title as title, `start_price` as price, `image` as url, categories.title as category, `date_exp` as expiration FROM lots '
    . 'JOIN `categories` ON categories.id = `category_id` '
    . 'WHERE `date_exp` > NOW() '
    . 'ORDER BY `date_created` DESC';
$products = getDataFromDB($connection, $sqlProducts);

// HTML-код блока с сеткой лотов
$gridLots = include_template('/grid-lots.php', [
    'products' => createDetailProducts($products)
]);

// HTML-код блока main
$pageContent = include_template('/main.php', [
    'categories' => $categories,
    'gridLots' => $gridLots
]);

// задаем переменные окружения для передачи в layout
$environment = setEnvironment('YetiCave - Главная', $pageContent, $categories);
$environment['homePage'] = true;
$environment['addContainer'] = true;
$environment['scripts'] = includeScripts();

// окончательный HTML-код
$layoutContent = include_template('/layout.php', $environment);

print($layoutContent);
