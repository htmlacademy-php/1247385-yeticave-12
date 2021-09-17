<?php
require_once 'config.php';
session_start();

$connection = mysqli_connect($db['host'], $db['user'], $db['password'], $db['database']);
mysqli_set_charset($connection, "utf8");

if (!empty($_SESSION['user'])) {
    $isAuth = true;
    $userName = $_SESSION['user']['name'];
}

$scripts = [
    'flatpickr.js',
    'script.js'
];

$extraCss = '<link href="../css/flatpickr.min.css" rel="stylesheet">';

function showConnectionError() {
    print('Ошибка подключения: ' . mysqli_connect_error());
}

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
        showConnectionError();
    }
    return $data;
}

$sqlCategories = 'SELECT categories.id as id, `code`, `title` FROM categories';
$categories = getDataFromDB($connection, $sqlCategories);
