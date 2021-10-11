<?php
require_once 'helpers.php';
require_once 'db.php';

loginRequired();

/**
 * Валидирует форму добавления лота - проверяет поля на заполненность, а также на заданные по каждому полю условия,
 * и возвращает текст ошибок в зависимости от нарушенных условий, или null, если валидация прошла успешно
 * @param array $categories Массив с имеющимися в БД категориями
 *
 * @return string|null Текст ошибок, если условия не выполнены, или null, если ошибок не было
 */
function validateInputFields(array $categories)
{
    $categoriesIds = array_column($categories, 'id');

    $required = ['lot-name', 'category', 'message', 'lot-img', 'lot-rate', 'lot-step', 'lot-date'];
    $errors = [];

    $rules = [
        'lot-name' => function ($value) {
            return validateLength($value, 10, 200);
        },
        'category' => function ($value) use ($categoriesIds) {
            return validateCategory($value, $categoriesIds);
        },
        'message' => function ($value) {
            return validateLength($value, 10, 500);
        },
        'lot-rate' => function ($value) {
            return validatePrice($value);
        },
        'lot-step' => function ($value) {
            return validatePriceStep($value);
        },
        'lot-date' => function ($date) {
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

/**
 * Записывает лот в таблицу lots, и осуществляет перенаправление пользователя
 * на страницу созданного лота в случае успешного добавления в БД
 *
 * @param mysqli $connection Ресурс соединения
 * @param array $lot Массив с данными лота, введенными пользователем в форме добавления лота
 *
 */
function insertLotToDB($connection, $lot)
{
    $sql = 'INSERT INTO lots
    (`date_created`, `title`, `category_id`, `description`, `start_price`,
    `step_price`, `date_exp`, `image`, `author_id`)
    VALUES (NOW(), ?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connection, $sql, $lot);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $lotId = mysqli_insert_id($connection);
        header('Location: lot.php?id=' . $lotId);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateInputFields($categories);

    if (!empty($errors)) {
        $pageContent = include_template('/add.php', [
            'errors' => $errors,
            'categories' => $categories
        ]);
    } else {
        $lot = $_POST;
        $lot['lot-img'] = getImageUrl();

        $lot['author_id'] = $userId;

        insertLotToDB($connection, $lot);
    }
} else {
    $pageContent = include_template('/add.php', [
        'categories' => $categories
    ]);
}

// задаем переменные окружения для передачи в layout
$environment = setEnvironment('Добавление лота', $pageContent, $categories, true);
$environment['scripts'] = includeScripts();

// окончательный HTML-код
$layoutContent = include_template('/layout.php', $environment);

print($layoutContent);
