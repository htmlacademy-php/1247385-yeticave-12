<?php
require_once 'config.php';

session_start();

$connection = mysqli_connect(HOST, USER, PASSWORD, DATABASE);
mysqli_set_charset($connection, "utf8");


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

/**
 * Проверяет, авторизован ли пользователь на сайте,
 * и если да - возвращает массив с id и именем пользователя и флагом авторизации.
 * Если пользователь не авторизован возвращает массив с пустыми данными пользователя,
 * и флагом авторизации в значении false
 *
 * @return array $userId и $userName авторизованного пользователя, флаг авторизации $isAuth
 */
function checkAuthUser() {
    // проверяем, открыта ли сессия
    if (!empty($_SESSION['user'])) {
        $isAuth = true;
        $userName = $_SESSION['user']['name'];
        $userId = $_SESSION['user']['id'];
    } else {
        $isAuth = false;
        $userName = '';
        $userId = '';
    }

    return [$isAuth, $userName, $userId];
}

/**
 * Используется для задания переменных среды и последующего отображения в шаблонах
 * @param string $title Заголовок страницы
 * @param string $content HTML-шаблон с основной частью страницы
 * @param array $categories Массив категорий, используемых на сайте
 * @param boolean $isExtraCss Флаг, показывающий, нужно ли подключать дополнительный CSS на странице,
 * по умолчанию в значении false (подключение не требуется)
 *
 * @return array Массив с данными для передачи в шаблон
 */
function setEnvironment($title, $content, $categories, $isExtraCss = false) {
    // проверяем, если ли авторизованный пользователь
    list($isAuth, $userName, $userId) = checkAuthUser();

    // дополнительный CSS, подключается на нужных по верстке страницах
    $extraCss = '<link href="../css/flatpickr.min.css" rel="stylesheet">';

    // задаем переменные среды
    $environment = [
        'homePage' => false,
        'addContainer' => false,
        'scripts' => false,
        'searchText' => '',
        // HTML-код блока nav в верхней и нижней части сайта
        'navigation' => include_template('/navigation.php', ['categories' => $categories]),
        // HTML-код блока footer
        'footer' => include_template('/footer.php'),
        'isAuth' => $isAuth,
        'userName' => $userName,
        'userId' => $userId,
        'extraCss' => $isExtraCss ? $extraCss : false,
        'title' => $title,
        'content' => $content
    ];

    return $environment;
}

// массив с категориями, используется на всех страницах
$sqlCategories = 'SELECT categories.id as id, `code`, `title` FROM categories';
$categories = getDataFromDB($connection, $sqlCategories);

// проверяем, если ли авторизованный пользователь
list($isAuth, $userName, $userId) = checkAuthUser();
