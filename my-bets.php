<?php
require_once 'helpers.php';
require_once 'db.php';

// пробуем поискать все свои ставки
function getMyBetsHistory($connection, $userId) {
    $sql = 'SELECT bets.date_created, `price`, users.name, bets.lot_id,
            lots.title as title, `image` as url, `date_exp` as expiration,
            contact, categories.title as category FROM bets '
        . 'JOIN `lots` ON lots.id = `lot_id` '
        . 'JOIN `categories` ON categories.id = `category_id` '
        . 'JOIN `users` ON users.id = `author_id` '
        . 'WHERE bets.user_id=' . $userId
        . ' ORDER BY bets.date_created DESC ';

    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) !==0) {
        $history = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // выводим дату ставки в человекопонятном формате
        $history = convertHistoryDates($history);

        // выводим ЧЧ:ММ:СС для даты окончания лота
        $history = createDetailProducts($history);
    } else {
        $_SESSION['systemMessage'] = 'Вы еще не делали ставок!';
    }

    return $history;
}

// если ставки были сделаны, проверяем, побеждали ли мы
function searchForWinners($connection, $history, $userId) {
    $historyWithWinners = [];

    foreach ($history as $item) {
        $sql = 'SELECT `user_id`, `lot_id` FROM bets '
            . ' WHERE `lot_id`=' . $item['lot_id']
            . ' ORDER BY id DESC LIMIT 1';

        $result = mysqli_query($connection, $sql);

        if ($result && mysqli_num_rows($result) !== 0) {
            $winnerArray = mysqli_fetch_assoc($result);
            $winner = $winnerArray['user_id'];

            $expiration = date_create($item['expiration']);

            switch ($expiration) {
                case ($expiration <= date_create() && $winner === $userId):
                    $item['winner'] = $winner;
                    $item['state'] = 'win';
                    break;
                case ($expiration <= date_create() && $winner !== $userId):
                    $item['state'] = 'end';
                    break;
            }

            $historyWithWinners[] = $item;
        } else {
            $_SESSION['systemMessage'] = 'Не удалось выбрать данные по вашему запросу';
        }
    }

    return $historyWithWinners;
}

// передаем в шаблон данные в зависимости от результата searchForWinners()
function checkLotWinner($connection, $history, $userId) {
    if ($history) {
        $historyWithWinners = searchForWinners($connection, $history, $userId);
    } else {
        $historyWithWinners = [];
    }

    return $historyWithWinners;
}

if ($connection) {
    $history = getMyBetsHistory($connection, $userId);
    $historyWithWinners = checkLotWinner($connection, $history, $userId);
} else {
    showConnectionError();
}

// HTML-код лота
$page_content = include_template('/my-bets.php', [
    'history' => $historyWithWinners
]);

// HTML-код блока nav в верхней и нижней части сайта
$navigation = include_template('/navigation.php', ['categories' => $categories]);

// HTML-код блока footer
$footer_content = include_template('/footer.php');

// окончательный HTML-код
$layout_content = include_template('/layout.php', [
    'title' => 'Мои ставки',
    'navigation' => $navigation,
    'isAuth' => $isAuth,
    'userName' => $userName,
    'content' => $page_content,
    'footer' => $footer_content,
]);

print($layout_content);
