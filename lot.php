<?php
require_once 'helpers.php';
require_once 'functions.php';
require_once 'db.php';


if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    if ($connection) {
        $sqlSelectLot = 'SELECT lots.id as id, lots.title as title, lots.description as description,
       `start_price` as price, `image` as url, `date_exp` as expiration, categories.title as category FROM lots '
            . 'JOIN `categories` ON categories.id = `category_id` '
            . 'WHERE lots.id=' . $id;

        $result = mysqli_query($connection, $sqlSelectLot);

        if ($result && mysqli_num_rows($result) !== 0) {
            // $row = mysqli_fetch_assoc($result);
            // буду лучше брать массив из одного элемента, чтобы использовать готовую функцию createDetailProducts
            $lot = mysqli_fetch_all($result, MYSQLI_ASSOC);
            $content = include_template('/lot.php', [
                'categories' => $categories,
                // берем первый и единственный элемент массива
                'lot' => createDetailProducts($lot)[0]
            ]);
        } else {
            http_response_code(404);
            $error = mysqli_error($connection);
            $content = include_template('/404.php', ['categories' => $categories]);
        }
    } else {
        print('Ошибка подключения: ' . mysqli_connect_error());
    }
} else {
    http_response_code(404);
    $content = include_template('/404.php', ['categories' => $categories]);
}


// HTML-код блока footer
$footer_content = include_template('/footer.php', ['categories' => $categories]);

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => $lot[0]['title'],
    'is_auth' => $is_auth,
    'user_name' => $user_name,
    'content' => $content,
    'footer' => $footer_content,
]);

print($layout_content);

