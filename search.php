<?php
require_once 'helpers.php';
require_once 'db.php';

function setUrlPath($value) {
    $params = $_GET;
    $page = intval($value);
    $params['page'] = $page;

    return $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($params);
}

function prepareSearchQuery($enteredSearchText) {
    $searchWords = explode(' ', $enteredSearchText);

    $search = '';

    foreach ($searchWords as $word) {
        $search .= $word . '* ';
    }

    $search = trim($search);

    return $search;
}

function createPagination($lots) {
    $itemsCount = count($lots); // количество найденных в БД лотов

    $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 9; // сколько лотов будет показано на странице
    $offset = ($currentPage - 1) * $limit;

    $pagesCount = intval(ceil($itemsCount / $limit)); // сколько будет страниц
    $pages = range(1, $pagesCount);

    $products = array_slice($lots, $offset, $limit, true);

    $templateData['products'] = $products;
    $templateData['pagesCount'] = $pagesCount;
    $templateData['pages'] = $pages;
    $templateData['currentPage'] = $currentPage;

    return $templateData;
}

function searchForMatches($connection, $search, $templateData) {
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

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footerContent = include_template('/footer.php');

// окончательный HTML-код
$layoutContent = include_template('/layout.php', [
    'title' => 'Результаты поиска',
    'navigation' => $navigation,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'content' => $pageContent,
    'footer' => $footerContent
]);

print($layoutContent);

