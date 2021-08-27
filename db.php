<?php
$is_auth = rand(0, 1);
$user_name = 'Anastasya'; // укажите здесь ваше имя

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

$sqlCategories = 'SELECT `code`, `title` FROM categories';
$categories = getDataFromDB($connection, $sqlCategories);

