<?php
require_once 'helpers.php';
require_once 'db.php';

$templateData=[];

/**
 * Валидирует поля формы авторизации - проверяет их на заполненность, а также на соответствие заданным условиям,
 * и возвращает текст ошибки в зависимости от нарушенного условия, или null, если валидация прошла успешно
 *
 * @return string|null Текст ошибки, если условия не выполнены, или null, если ошибок не было
 */
function validateInputFields() {
    $required = ['email', 'password'];
    $errors = [];

    $rules = [
        'email' => function($value) {
            return validateEmail($value);
        },
        'password' => function($value) {
            return validateLength($value, 5, 32);
        }
    ];

    foreach ($_POST as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }

        if(in_array($key, $required) && empty($value)) {
            $errors[$key] = "Поле $key не может быть пустым";
        }
    }

    $errors = array_filter($errors);
    return $errors;
}

/**
 * Достает пользователя из БД по email, переданному в $_POST,
 * и возвращает одномерный массив с данными пользователя,
 * или null, если такого пользователя в БД нет
 * @param mysqli $connection Ресурс соединения
 * @param string $value email, введенный пользователем
 *
 * @return array Массив с данными запрашиваемого пользователя, или null, если пользователь не найден в БД
 */
function getUserFromDB($connection, $value) {
    $email = mysqli_real_escape_string($connection, $value);
    $sql = "SELECT * FROM users WHERE email= '$email'";
    $result = mysqli_query($connection, $sql);

    $user = $result ? mysqli_fetch_assoc($result) : null;

    return $user;
}

/**
 * Сверяет хеш пароля, введенного пользователем, с хешем, храняшимся в БД.
 * Если пароли совпали, в сессию записываются все данные пользователя,
 * а сам пользователь перенаправляется на главную страницу.
 * Если пароли не совпали, или пользователь с таким email не найден в БД,
 * возвращает массив, содержащий текст ошибки
 * @param array $user Массив с данными пользователя для проверки пароля
 * @param array $errors Массив с ошибками для актуализации данных если ошибки были
 *
 * @return array Массив с данными об ошибках
 */
function checkUserPassword($user, $errors) {
    if ($user) {
        if (password_verify($_POST['password'], $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: /");
            exit();
        }
        else {
            $errors['password'] = 'Вы ввели неверный пароль';
        }
    } else {
        $errors['email'] = 'Такой пользователь не найден';
    }

    return $errors;
}

/**
 * Проверяет, открыта ли сессия пользователя,
 * и если открыта - выполняет редирект на главную страницу сайта,
 * и завершает выполнение скрипта
 */
function checkSession() {
    if (isset($_SESSION['user'])) {
        header("Location: /");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateInputFields();

    if (empty($errors)) {
        $email = $_POST['email'];

        $user = getUserFromDB($connection, $email);

        $errors = checkUserPassword($user, $errors);
    }

    $templateData['errors'] = $errors;
} else {
    $templateData=[];
    checkSession();
}

// HTML-код формы регистрации
$page_content = include_template('/login.php', $templateData);

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => 'Вход',
    'navigation' => $navigation,
    'content' => $page_content,
    'footer' => $footer_content
]);

print($layout_content);
