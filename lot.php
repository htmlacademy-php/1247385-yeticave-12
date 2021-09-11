<?php
require_once 'helpers.php';
require_once 'db.php';

// подготавливаем параметр для запроса в БД
function getIdFromRequest($param) {
    if (isset($param)) {
        $id = intval($param);
    } else {
        http_response_code(404);
    }
    return $id;
}

//  достаем лот из БД
function getLotFromDb($connection) {
    $sqlSelectLot = 'SELECT lots.id as id, lots.title as title, lots.description as description,
       `start_price` as price, `image` as url, `date_exp` as expiration, categories.title as category FROM lots '
            . 'JOIN `categories` ON categories.id = `category_id` '
            . 'WHERE lots.id=' . getIdFromRequest($_GET['id']);

    $result = mysqli_query($connection, $sqlSelectLot);

    if ($result && mysqli_num_rows($result) !== 0) {
        $lot = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        http_response_code(404);
        $error = mysqli_error($connection);
    }

    return $lot;
}

if ($connection) {
    $lot = getLotFromDb($connection);
} else {
    print('Ошибка подключения: ' . mysqli_connect_error());
}

if (http_response_code() === 200) {
    $content = include_template('/lot.php', [
        'categories' => $categories,
        // берем первый и единственный элемент массива
        'lot' => createDetailProducts($lot)[0]
    ]);
} else {
    $content = include_template('/404.php', ['categories' => $categories]);
}

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => $lot[0]['title'],
    'isAuth' => $isAuth,
    'userName' => $userName,
    'navigation' => $navigation,
    'content' => $content,
    'footer' => $footer_content,
]);

print($layout_content);
