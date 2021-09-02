<?php
$isAuth = rand(0, 1);
$userName = 'Anastasya'; // укажите здесь ваше имя

$scripts = [
    'flatpickr.js',
    'script.js'
];

$extraCss = '<link href="../css/flatpickr.min.css" rel="stylesheet">';

$connection = mysqli_connect("localhost", "root", "root", "yeticave");
mysqli_set_charset($connection, "utf8");

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
        print('Ошибка подключения: ' . mysqli_connect_error());
    }
    return $data;
}

$sqlCategories = 'SELECT categories.id as id, `code`, `title` FROM categories';
$categories = getDataFromDB($connection, $sqlCategories);
