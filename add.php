<?php
require_once 'helpers.php';
require_once 'db.php';

loginRequired();

$categoriesIds = array_column($categories, 'id');

function validateInputFields($categoriesIds) {
    $required = ['lot-name', 'category', 'message', 'lot-img', 'lot-rate', 'lot-step', 'lot-date'];
    $errors = [];

    $rules = [
        'lot-name' => function($value) {
            return validateLength($value, 10, 200);
        },
        'category' => function($value) use ($categoriesIds) {
            return validateCategory($value, $categoriesIds);
        },
        'message' => function($value) {
            return validateLength($value, 10, 500);
        },
        'lot-rate' => function($value) {
            return validatePrice($value);
        },
        'lot-step' => function($value) {
            return validatePriceStep($value);
        },
        'lot-date' => function($date) {
            return validateDate($date);
        }
    ];

    foreach ($_POST as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }

        if (in_array($key, $required) && empty($value)) {
            $errors[$key] = "Поле $key не может быть пустым";
        }
    }

    if (isset($_FILES['lot-img'])) {
        $errors['lot-img'] = validateImg();
    }

    $errors = array_filter($errors);

    return $errors;
}

function insertLotToDB($connection, $lot) {
    $sql = 'INSERT INTO lots
    (`date_created`, `title`, `category_id`, `description`, `start_price`,
    `step_price`, `date_exp`, `image`, `author_id`)
    VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connection, $sql, $lot);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $lotId = mysqli_insert_id($connection);
        header('Location: lot.php?id=' . $lotId);
    } else {
        $error = mysqli_error($connection);
        print("Ошибка MySQL: " . $error);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateInputFields($categoriesIds);

    if (!empty($errors)) {
        $page_content = include_template('/add.php', [
            'errors' => $errors,
            'categories' => $categories
        ]);
    } else {
        $lot = $_POST;
        $lot['lot-img'] = getImageUrl();

        $lot['author_id'] = $_SESSION['user']['id'];

        insertLotToDB($connection, $lot);
    }
} else {
    $page_content = include_template('/add.php', [
        'categories' => $categories
    ]);
}

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => 'Добавление лота',
    'navigation' => $navigation,
    'content' => $page_content,
    'footer' => $footer_content,
    'scripts' => includeScripts($scripts),
    'extraCss' => $extraCss
]);

print($layout_content);
