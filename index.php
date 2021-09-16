<?php
require_once 'helpers.php';
require_once 'db.php';

$title = 'YetiCave - Главная';

$sqlProducts = 'SELECT lots.id as id, lots.title as title, `start_price` as price, `image` as url, categories.title as category, `date_exp` as expiration FROM lots '
    . 'JOIN `categories` ON categories.id = `category_id` '
    . 'WHERE `date_exp` > NOW() '
    . 'ORDER BY `date_created`';
$products = getDataFromDB($connection, $sqlProducts);

// HTML-код блока с сеткой лотов
$gridLots = include_template('/grid-lots.php', [
    'products' => createDetailProducts($products)
]);

// HTML-код блока main
$page_content = include_template('/main.php', [
    'categories' => $categories,
    'gridLots' => $gridLots
]);

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => $title,
    'navigation' => $navigation,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'content' => $page_content,
    'footer' => $footer_content,
    'scripts' => includeScripts($scripts),
    'homePage' => true,
    'addContainer' => true
]);

print($layout_content);
