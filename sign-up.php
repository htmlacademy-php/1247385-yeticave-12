<?php
require_once 'helpers.php';
require_once 'db.php';

$templateData=[
    'categories' => $categories
];

function validateInputFields($connection) {
    $required = ['email', 'password', 'name', 'message'];
    $errors = [];

    $rules = [
        'email' => function($value) use ($connection) {
            return validateEmail($value, $connection);
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

function createHashPassword($user) {
    $password = password_hash($user['password'], PASSWORD_DEFAULT);

    $user['password'] = $password;

    return $user;
}

function insertUserToDB($connection, $user) {
    $userForDB = createHashPassword($user);

    $sql = 'INSERT INTO users 
    (`date_created`, `email`, `password`, `name`, `contact`)
    VALUES (NOW(), ?, ?, ?, ?)'; 

    $stmt = db_get_prepare_stmt($connection, $sql, $userForDB);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        header('Location: /pages/login.html');
        exit();
    } else {
        $error = mysqli_error($connection);
        print("Ошибка MySQL: " . $error);
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
    $templateData = $templateData;
}

// HTML-код формы регистрации
$page_content = include_template('/sign-up.php', $templateData);

// HTML-код блока footer
$footer_content = include_template('/footer.php', ['categories' => $categories]);

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => 'Регистрация',
    'isAuth' => $isAuth,
    'userName' => $userName,
    'content' => $page_content,
    'footer' => $footer_content
]);

print($layout_content);