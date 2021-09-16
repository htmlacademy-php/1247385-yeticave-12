<?php
require_once 'helpers.php';
require_once 'db.php';

//function getLotsCountFromDB($connection) {
//    $sql = 'SELECT COUNT(*) as count FROM lots';
//    $result = mysqli_query($connection, $sql);
//    $itemsCount = mysqli_fetch_assoc($result)['count'];
//
//    return $itemsCount;
//}

function getDataForPagination() {
    $currentPage = $_GET['page'] ?? 1;
    $pageItems = 1;
    $offset = ($currentPage - 1) * $pageItems;

    return [$pageItems, $offset, $currentPage];
}

function createPagination($itemsCount, $pageItems) {
//    $currentPage = $_GET['page'] ?? 1;
//    $pageItems = 9;

//    $itemsCount = getLotsCountFromDB($connection);
//    var_dump($itemsCount);

    $pagesCount = ceil($itemsCount / $pageItems);
//    $offset = ($currentPage - 1) * $pageItems;

    $pages = range(1, $pagesCount);

    var_dump($itemsCount);
    return [$pages, $pagesCount];
}

function searchForMatches($connection, $search, $templateData) {
//    $sql = 'SELECT lots.id as id, lots.title as title, lots.description as description,
//       `start_price` as price, `image` as url, categories.title as category, `date_exp` as expiration FROM lots '
//        . 'JOIN `categories` ON categories.id = `category_id` '
//        . 'WHERE `date_exp` > NOW() AND MATCH(lots.title, description) AGAINST(?) '
//        . 'ORDER BY `date_created` DESC '
//        . 'LIMIT ? OFFSET ?';

    $sql = 'SELECT lots.id as id, lots.title as title, lots.description as description,
       `start_price` as price, `image` as url, categories.title as category, `date_exp` as expiration FROM lots '
        . 'JOIN `categories` ON categories.id = `category_id` '
        . 'WHERE `date_exp` > NOW() AND MATCH(lots.title, description) AGAINST(?) '
        . 'ORDER BY `date_created` DESC ';

    $sqlPagination = $sql . 'LIMIT ? OFFSET ?';

    list($pageItems, $offset, $currentPage) = getDataForPagination();
    $dataForQuery = [$search, $pageItems, $offset];

//    $stmt = db_get_prepare_stmt($connection, $sql, $dataForQuery);
    $stmt = db_get_prepare_stmt($connection, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    var_dump(mysqli_num_rows($result));

    if ($result && mysqli_num_rows($result) !== 0) {
        $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $gridLots = include_template('/grid-lots.php', ['products' => $lots]);
        $templateData['gridLots'] = $gridLots;

        $itemsCount = mysqli_num_rows($result);
        var_dump($itemsCount);
        list($pages, $pagesCount) = createPagination($itemsCount, $pageItems);

        $templateData['pagesCount'] = $pagesCount;
        $templateData['pages'] = $pages;
        $templateData['currentPage'] = $currentPage;
    } else {
        $templateData['errors'] = 'Ничего не найдено по вашему запросу';
    }

    return $templateData;
}

if ($connection) {
    $search = trim($_GET['search']) ?? '';

    $templateData['search'] = $search;

    if ($search && mb_strlen($search) >= 3) {
        $templateData = searchForMatches($connection, $search, $templateData);
    } else {
        $templateData['errors'] = 'Минимальная длина слова для поиска - 3 символа';
    }
} else {
    showConnectionError();
}


// HTML-код блока main
$page_content = include_template('/search.php', $templateData);

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => 'Результаты поиска',
    'navigation' => $navigation,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'content' => $page_content,
    'footer' => $footer_content
]);

print($layout_content);
