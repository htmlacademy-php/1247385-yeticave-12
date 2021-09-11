<?php
require_once 'helpers.php';
require_once 'db.php';

$templateData=[];

function validateInputFields() {
    $required = ['email', 'password'];
    $errors = [];

    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $errors['email'] = "Введите корректный email";
    }

    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $errors[$field] = "Поле $field не может быть пустым";
        }
    }

    $errors = array_filter($errors);

    return $errors;
}

function getUserFromDB($connection, $value) {
    $email = mysqli_real_escape_string($connection, $value);
    $sql = "SELECT * FROM users WHERE email= '$email'";
    $result = mysqli_query($connection, $sql);
    
    $user = $result ? mysqli_fetch_assoc($result) : null;

    return $user;
}

function checkSession() {
    if (isset($_SESSION['user'])) {
        header("Location: /");
        exit();
    }
}

function verifyPassword($formPassword, $user) {
    if (password_verify($formPassword, $user['password'])) {
        $_SESSION['user'] = $user;
    }
    else {
        $_SESSION['errors'] = 'Вы ввели неверный пароль';
    }

    return $_SESSION;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = validateInputFields();

    $email = $_POST['email'];
    $user = getUserFromDB($connection, $email);

    switch ($errors) {
        case (!empty($errors)):
            $templateData['errors'] = $errors;
            break;
        case (empty($errors) and !$user):
            $errors['email'] = 'Такой пользователь не найден';
            var_dump($errors);
            break;
        case (empty($errors) and $user):
            password_verify($_POST['password'], $user['password']) ? $_SESSION['user'] = $user : $errors['password'] = 'Вы ввели неверный пароль';
            var_dump($errors);
            break;
        default:
            header("Location: /");
            exit();
    }

    // if (empty($errors) and $user) {
    //     if (password_verify($_POST['password'], $user['password'])) {
	// 		$_SESSION['user'] = $user;
	// 	}
	// 	else {
	// 		$errors['password'] = 'Вы ввели неверный пароль';
	// 	}

    // } else {
    //     $errors['email'] = 'Такой пользователь не найден';
    // }

    // if (!empty($errors)) {
    //     $templateData['errors'] = $errors;
    // } else {
    //     header("Location: /");
	// 	exit();
    // }

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
    'isAuth' => $isAuth,
    'userName' => $userName,
    'navigation' => $navigation,
    'content' => $page_content,
    'footer' => $footer_content
]);

print($layout_content);