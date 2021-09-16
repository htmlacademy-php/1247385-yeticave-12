<?php
require_once 'helpers.php';
require_once 'db.php';

function searchForMatches($connection, $search, $templateData) {
    $sql = 'SELECT lots.id as id, lots.title as title, lots.description as description,
       `start_price` as price, `image` as url, categories.title as category, `date_exp` as expiration FROM lots '
        . 'JOIN `categories` ON categories.id = `category_id` '
        . 'WHERE `date_exp` > NOW() AND MATCH(lots.title, description) AGAINST(?) '
        . 'ORDER BY `date_created` DESC';

    $stmt = db_get_prepare_stmt($connection, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result && mysqli_num_rows($result) !== 0) {
        $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $gridLots = include_template('/grid-lots.php', ['products' => $lots]);
        $templateData['gridLots'] = $gridLots;
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
