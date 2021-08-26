<?php
require_once 'helpers.php';
require_once 'functions.php';
require_once 'db.php';

$title = 'YetiCave - Lot Page';

$sqlSelectLot = 'SELECT lots.id as id, `start_price` as price, `image` as url, `date_exp` as expiration,
       categories.title as category FROM lots '
    . 'JOIN `categories` ON categories.id = `category_id` '
    . 'WHERE lots.id' . $id;


function getLotFromDB($connection, $sql) {
    if ($connection) {
        $result = mysqli_query($connection, $sql);

        if ($result) {
            $row = mysqli_fetch_assoc($result);
        } else {
            $error = mysqli_error($connection);
            http_response_code(404);
            print("Ошибка MySQL: " . $error);
        }
    } else {
        print('Ошибка подключения: ' . mysqli_connect_error());
    }
    var_dump($row);
    return $row;
}

$id = intval($_GET['id']);

$location = pathinfo(__FILE__, PATHINFO_BASENAME);
$params = http_build_query($id);
$url = '/' . $location . '?' . $params;
var_dump($url);

if (isset($id)) {
    getLotFromDB($connection, $sqlSelectLot);
} else {
    http_response_code(404);
}


// HTML-код блока main
$page_content = include_template('/lot.php', [
    'categories' => $categories
]);

// HTML-код блока footer
$footer_content = include_template('/footer.php', ['categories' => $categories]);

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => $title,
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'content' => $page_content,
    'footer' => $footer_content,
]);

print($layout_content);

