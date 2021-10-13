<?php
require_once 'helpers.php';
require_once 'db.php';

/**
 * Подготавливает поисковый запрос, введенный пользователем, для поиска в БД
 * в логическом режиме полнотекстового поиска -
 * добавляет плейсхолдер * в конце каждого слова, чтобы искать не только по
 * строгому соответствию, но и по совпадениям
 * @param string $enteredSearchText Поисковый запрос, введенный пользователем
 *
 * @return string Поисковый запрос, дополненный символом * для каждого слова
 */
function prepareSearchQuery($enteredSearchText)
{
    $searchWords = explode(' ', $enteredSearchText);

    $search = '';

    foreach ($searchWords as $word) {
        $search .= $word . '* ';
    }

    $search = trim($search);

    return $search;
}

/**
 * Выбирает в БД лоты, соответствующие поисковому запросу, переданному в $_GET,
 * и если находит такие лоты, возвращает массив с их данными для отрисовки в шаблоне.
 * Если по поисковому запросу в БД ничего не нашлось, возвращает массив, содержащий сообщение об отсутствии лотов
 *
 * @param mysqli $connection Ресурс соединения
 * @param string $search Поисковый запрос от пользователя
 * @param array $templateData Массив для записи в шаблон результата поиска лотов
 *
 * @return array Массив с данными для отрисовки лотов по выбранной категории,
 * или пустой массив с сообщением что лотов не найдено
 */
function searchForMatches($connection, $search, $templateData)
{
    $sql = 'SELECT lots.id as id, lots.title as title, lots.description as description,
       `start_price` as price, `image` as url, categories.title as category, `date_exp` as expiration FROM lots '
        . 'JOIN `categories` ON categories.id = `category_id` '
        . 'WHERE `date_exp` > NOW() AND MATCH(lots.title, description) AGAINST(? IN BOOLEAN MODE) '
        . 'ORDER BY `date_created` DESC ';

    $stmt = db_get_prepare_stmt($connection, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) !== 0) {
        $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $templateData = createPagination($lots);

        $gridLots = include_template('/grid-lots.php', [
            'products' => createDetailProducts($templateData['products'])
        ]);
        $templateData['gridLots'] = $gridLots;
    } else {
        $templateData['errors'] = 'Ничего не найдено по вашему запросу';
    }

    return $templateData;
}

if ($connection) {
    $enteredSearchText = trim($_GET['search']) ?? '';

    $search = prepareSearchQuery($enteredSearchText);

    $templateData['search'] = $enteredSearchText;

    if ($search && mb_strlen($search) >= 3) {
        $templateData += searchForMatches($connection, $search, $templateData);
    } else {
        $templateData['errors'] = 'Минимальная длина слова для поиска - 3 символа';
    }
} else {
    showConnectionError();
}


// HTML-код блока main
$pageContent = include_template('/search.php', $templateData);

// задаем переменные окружения для передачи в layout
$environment = setEnvironment('Результаты поиска', $pageContent, $categories);
$environment['searchText'] = $enteredSearchText;

// окончательный HTML-код
$layoutContent = include_template('/layout.php', $environment);

print($layoutContent);
