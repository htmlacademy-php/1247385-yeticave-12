<?php
require_once 'helpers.php';
require_once 'db.php';

// подготавливаем параметр для запроса в БД
function getIdFromRequest($param) {
    if (isset($param)) {
        $id = intval($param);
    } else {
        http_response_code(404);
    }
    return $id;
}

// валидируем ставку
function validateUserRate($value, $minPrice) {
    if (empty($value)) {
        $error = 'Поле не может быть пустым';
    } else {
        $error = validatePriceStep($value, $minPrice);
    }

    return $error;
}

//  достаем лот из БД
function getLotFromDb($connection) {
    $sqlSelectLot = 'SELECT lots.id as id, lots.title as title, lots.description as description,
       `start_price` as price, `image` as url, `date_exp` as expiration,
       `step_price` as step, `author_id`, categories.title as category FROM lots '
            . 'JOIN `categories` ON categories.id = `category_id` '
            . 'WHERE lots.id=' . getIdFromRequest($_GET['id']);

    $result = mysqli_query($connection, $sqlSelectLot);

    if ($result && mysqli_num_rows($result) !== 0) {
        $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
        $lotDetails = createDetailProducts($lots);

        // берем первый и единственный элемент массива с лотами
        $currentLot = $lotDetails[0];
    } else {
        http_response_code(404);
        showQueryError($connection);
    }

    return $currentLot;
}

function getMaxBet($connection, $lot) {
    $id = $lot['id'];

    $sql = 'SELECT MAX(`price`) as max_bet FROM bets '
        . 'WHERE `lot_id`=' . $id;
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) !== 0) {
        $maxBet = mysqli_fetch_assoc($result);
    }

    return $maxBet['max_bet'];
}


function calculateMinBet($currentLot) {
    $minBet = $currentLot['currentPrice'] + $currentLot['step'];
    $currentLot['minBet'] = $minBet;

    return $currentLot;
}

function updateLotBets($connection, $lot) {
    $maxBet = getMaxBet($connection, $lot);

    if (!empty($maxBet)) {
        $lot['currentPrice'] = $maxBet;
    } else {
        $lot['currentPrice'] = $lot['price'];
    }

    $lot = calculateMinBet($lot);

    return $lot;
}

function insertBetToDb($rate, $userId, $lot, $connection) {
    $bet = [
        'price' => intval($rate),
        'user_id' => $userId,
        'lot_id' => $lot['id']
    ];

    $sql = 'INSERT INTO bets
            (`date_created`, `price`, `user_id`, `lot_id`)
            VALUES (NOW(), ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connection, $sql, $bet);
    $result = mysqli_stmt_execute($stmt);

    if(!$result) {
        showQueryError($connection);
    }
}

function getBetsHistory($connection, $lot) {
    $sql = 'SELECT bets.date_created, `price`, bets.user_id, bets.lot_id, users.name FROM bets '
        . 'JOIN `users` ON users.id = `user_id` '
        . 'WHERE bets.lot_id=' . $lot['id']
        . ' ORDER BY bets.date_created DESC ';

    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) !==0) {
        $history = mysqli_fetch_all($result, MYSQLI_ASSOC);

        $history = convertHistoryDates($history);
    } else {
        showQueryError($connection);
    }

    return $history;
}

function checkFormVisibility($lot, $history, $userId) {
    $isVisible = checkLotDateActual($lot['expiration']);

    if ($userId === $lot['author_id'] || $history[0]['user_id'] === $userId) {
        $isVisible = false;
    }
    return $isVisible;
}

// показываем шаблон в зависимости от наличия лота в БД
function setTemplateData($lot, $isAuth, $error, $history, $isVisible, $userId) {
    if (http_response_code() === 200) {
        $content = include_template('/lot.php', [
            'lot' => $lot,
            'currentPrice' => formatPrice($lot['currentPrice']),
            'isAuth' => $isAuth,
            'error' => $error,
            'history' => $history,
            'isVisible' => checkFormVisibility($lot, $history, $userId)
        ]);
    } else {
        $content = include_template('/404.php');
    }

    return $content;
}

if ($connection) {
    $lotFromDB = getLotFromDb($connection);

    $lot = updateLotBets($connection, $lotFromDB);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $rate = $_POST['cost'];

        $error = validateUserRate($rate, $lot['minBet']);

        if (!$error && $isAuth) {
            insertBetToDb($rate, $userId, $lot, $connection);
            $lot = updateLotBets($connection, $lot);
        }
    }
    $history = getBetsHistory($connection, $lot);

} else {
    showConnectionError();
}

// HTML-код лота
$page_content = setTemplateData($lot, $isAuth, $error, $history, $isVisible, $userId);

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => $lot['title'],
    'navigation' => $navigation,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'content' => $page_content,
    'footer' => $footer_content,
]);

print($layout_content);
