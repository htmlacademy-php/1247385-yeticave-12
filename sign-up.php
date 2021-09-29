<?php
require_once 'helpers.php';
require_once 'db.php';

alreadyRegisteredUser();

/**
 * Валидирует данные для регистрации пользователя -
 * проверяет все поля на заполненность, а также на соответствие заданным условиям,
 * и возвращает текст ошибки в зависимости от нарушенного условия, или null, если валидация прошла успешно
 * @param mysqli $connection Ресурс соединения
 *
 * @return array Массив с ошибками, если условия не выполнены, или пустой массив, если ошибок не было
 */
function validateInputFields($connection) {
    $required = ['email', 'password', 'name', 'message'];
    $errors = [];

    $rules = [
        'email' => function($value) use ($connection) {
            return validateEmailWithDB($value, $connection);
        },
        'password' => function($value) {
            return validateLength($value, 5, 32);
        },
        'name' => function($value) {
            return validateLength($value, 4, 128);
        },
        'message' => function($value) {
            return validateLength($value, 10, 255);
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

    $errors = array_filter($errors);

    return $errors;
}

/**
 * Принимает массив с данными пользователя,
 * хеширует его пароль для хранения в БД, и возвращает обратно
 * @param array $user Массив с данными пользователя
 *
 * @return array Обновленный массив с данными пользователя, содержащий хеш его пароля
 */
function createHashPassword($user) {
    $password = password_hash($user['password'], PASSWORD_DEFAULT);

    $user['password'] = $password;

    return $user;
}

/**
 * Записывает пользователя в БД, и если запись прошла успешно -
 * выполняет его переадресацию на страницу login для входа на сайт
 * @param mysqli $connection Ресурс соединения
 * @param array $user Массив с данными пользователя для записи в БД
 *
 */
function insertUserToDB($connection, $user) {
    $userForDB = createHashPassword($user);

    $sql = 'INSERT INTO users
    (`date_created`, `email`, `password`, `name`, `contact`)
    VALUES (NOW(), ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connection, $sql, $userForDB);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        header('Location: /login.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateInputFields($connection);

    if (!empty($errors)) {
        $templateData['errors'] = $errors;
    } else {
        $user = $_POST;
        insertUserToDB($connection, $user);
    }
} else {
    $templateData = [];
}

// HTML-код формы регистрации
$page_content = include_template('/sign-up.php', $templateData);

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => 'Регистрация',
    'navigation' => $navigation,
    'content' => $page_content,
    'footer' => $footer_content
]);

print($layout_content);
