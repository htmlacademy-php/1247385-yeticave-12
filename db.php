<?php
require_once 'config.php';

session_start();

$connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
mysqli_set_charset($connection, "utf8");

// проверяем, открыта ли сессия
if (!empty($_SESSION['user'])) {
    $isAuth = true;
    $userName = $_SESSION['user']['name'];
    $userId = $_SESSION['user']['id'];
}

// дополнительный CSS, подключается на нужных по верстке страницах
$extraCss = '<link href="../css/flatpickr.min.css" rel="stylesheet">';


/**
 * Показывает ошибку подключения, если ресурс соединения недоступен
 */
function showConnectionError()
{
    print('Ошибка подключения: ' . mysqli_connect_error());
}


/**
 * Достает данные из БД по переданному SQL-запросу
 *
 * @param mysqli $connection Ресурс соединения
 * @param string $sql SQL-запрос на выборку данных из БД
 *
 * @return array Двумерный массив данных, выбранных на основе $sql или пустой массив, если ничего не нашлось
 */
function getDataFromDB($connection, $sql)
{
    if ($connection) {
        $result = mysqli_query($connection, $sql);

        if ($result) {
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
        } else {
            $data = [];
        }
    } else {
        showConnectionError();
    }
    return $data;
}


// массив с категориями, используется на всех страницах
$sqlCategories = 'SELECT categories.id as id, `code`, `title` FROM categories';
$categories = getDataFromDB($connection, $sqlCategories);
