<?php
require_once 'helpers.php';

$is_auth = rand(0, 1);
$user_name = 'Anastasya'; // укажите здесь ваше имя
$title = 'YetiCave - Главная';

$connection = mysqli_connect("localhost", "root", "root", "yeticave");
mysqli_set_charset($connection, "utf8");

$sqlCategories = 'SELECT `code`, `title` FROM categories';
$sqlProducts = 'SELECT lots.title as title, `start_price` as price, `image` as url, categories.title as category, `date_exp` as expiration FROM lots '
    . 'JOIN `categories` ON categories.id = `category_id` '
    . 'WHERE `date_exp` > NOW() '
    . 'ORDER BY `date_created`';

function getDataFromDB($connection, $sql) {
    if ($connection) {
        $result = mysqli_query($connection, $sql);

        if ($result) {
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $error = mysqli_error($connection);
            $data = [];
            print("Ошибка MySQL: " . $error);
        }
    } else {
        print('Ошибка подключения: ' . mysqli_connect_error());
    }
    return $data;
}

$categories = getDataFromDB($connection, $sqlCategories);
$products = getDataFromDB($connection, $sqlProducts);


function formatPrice($rawPrice) {
    $actualPrice = ceil(intval($rawPrice));

    if ($actualPrice >= 1000) {
        $actualPrice = number_format($actualPrice, 0, '', ' ');
    }
    return $actualPrice . ' &#8381;';
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
        list($hours, $minutes) = getExpirationDate($product['expiration']);

        $product['hours'] = $hours;
        $product['minutes'] = $minutes;
        $product['isNew'] = $hours < 1;

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
