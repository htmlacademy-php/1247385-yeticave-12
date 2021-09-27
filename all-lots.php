<?php
require_once 'helpers.php';
require_once 'db.php';

$templateData = [];

// проверяем корректность переданного параметра
function checkRequest($param, $categories, $connection) {
    $categoriesCodes = array_column($categories, 'code');

    if (isset($param) && in_array($param, $categoriesCodes)) {
        $category = mysqli_real_escape_string($connection, $param);
    } else {
        http_response_code(404);
    }

    return $category;
}

// получаем название категории из переданного кода
function getCategoryTitle($categories, $code) {
    foreach ($categories as $item) {
        if ($item['code'] === $code) {
            $categoryTitle = $item['title'];
        }
    }

    return $categoryTitle;
}

function searchForMatches($connection, $categories, $templateData) {
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
        }
    }

    return $templateData;
}


// задаем шаблон для отображения
function setTemplateName() {
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
$page_content = include_template($templateName, $templateData);

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => 'Все лоты',
    'navigation' => $navigation,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'content' => $page_content,
    'footer' => $footer_content
]);

print($layout_content);
