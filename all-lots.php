<?php
require_once 'helpers.php';
require_once 'db.php';

$templateData = [];

/**
 * Проверяет корректность переданного параметра категории -
 * есть ли код категории, переданный в $_GET в списке категорий сайта.
 * Если категория существует, возвращает ее значение, защищенное от SQL-инъекций,
 * для последующего запроса в БД.
 * Если категория не существует - возвращает код ответа 404
 *
 * @param string $param Код категории, переданный в $_GET
 * @param array $categories Массив с категориями
 * @param mysqli $connection Ресурс соединения
 *
 * @return string Безопасное значение кода категории для запроса в БД
 */
function checkRequest($param, $categories, $connection)
{
    $categoriesCodes = array_column($categories, 'code');

    if (isset($param) && in_array($param, $categoriesCodes)) {
        $category = mysqli_real_escape_string($connection, $param);
    } else {
        http_response_code(404);
    }

    return $category;
}


/**
 * Ищет в массиве категорий название категории в человекопонятном виде
 * по переданному символьному коду категории
 *
 * @param array $categories Массив с категориями
 * @param string $code Символьный код категории
 *
 * @return string Название категории в человекопонятном виде для вывода в шаблоне
 */
function getCategoryTitle($categories, $code)
{
    foreach ($categories as $item) {
        if (isset($item['code']) && $item['code'] === $code) {
            $categoryTitle = $item['title'];
        }
    }

    return $categoryTitle;
}

/**
 * Выбирает в БД лоты, соответствующие категории, переданной в $_GET,
 * и если находит такие лоты, возвращает массив с их данными для отрисовки в шаблоне.
 * Если в заданной категории нет лотов, возвращает массив, содержащий сообщение об отсутствии лотов
 *
 * @param mysqli $connection Ресурс соединения
 * @param array $categories Массив с категориями
 * @param array $templateData Массив для записи результата поиска лотов
 *
 * @return array Массив с данными для отрисовки лотов по выбранной категории,
 * или пустой массив с сообщением что лотов не найдено
 */
function searchForMatches($connection, $categories, $templateData)
{
    $category = checkRequest($_GET['category'], $categories, $connection);

    $sql = 'SELECT lots.id as id, lots.title as title, lots.description as description,
       `start_price` as price, `image` as url, categories.code as code,
       categories.title as category, `date_exp` as expiration FROM lots '
        . 'JOIN `categories` ON categories.id = `category_id` '
        . 'WHERE `date_exp` > NOW() AND code="' . $category . '" '
        . 'ORDER BY `date_created` DESC ';

    if ($category) {
        $templateData['category'] = getCategoryTitle($categories, $category);
        $lots = getDataFromDB($connection, $sql);

        if ($lots) {
            $templateData += createPagination($lots);

            // HTML-код блока с сеткой лотов
            $gridLots = include_template('/grid-lots.php', [
                'products' => createDetailProducts($templateData['products'])
            ]);
            $templateData['gridLots'] = $gridLots;
        } else {
            $templateData['errors'] = 'Ничего не найдено по вашему запросу';
            $templateData['pagination'] = '';
        }
    }

    return $templateData;
}

/**
 * В зависимости от полученного кода ответа сервера определяет шаблон для отображения на странице.
 * Если код ответа 200, показывается шаблон для отрисовки лотов,
 * во всех остальных случаях отрисовывается шаблон для страницы 404
 *
 * @return string Имя шаблона для отображения на странице
 */
function setTemplateName()
{
    if (http_response_code() === 200) {
        $templateName = '/all-lots.php';
    } else {
        $templateName = '/404.php';
    }

    return $templateName;
}

if ($connection) {
    $templateData = searchForMatches($connection, $categories, $templateData);
    $templateName = setTemplateName();
} else {
    showConnectionError();
}

// HTML-код блока main
$pageContent = include_template($templateName, $templateData);

// задаем переменные окружения для передачи в layout
$environment = setEnvironment('Все лоты', $pageContent, $categories);

// окончательный HTML-код
$layoutContent = include_template('/layout.php', $environment);

print($layoutContent);
