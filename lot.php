<?php
require_once 'helpers.php';
require_once 'db.php';

/**
 * Подготавливает параметр для запроса в БД
 * Если параметр передан, он преобразуется к целому числу,
 * а если нет, возвращается код ответа 404
 * @param string $param Параметр, переданный в $_GET
 *
 * @return integer Целое число
 */
function getIdFromRequest($param)
{
    if (isset($param)) {
        $id = intval($param);
    } else {
        http_response_code(404);
    }
    return $id;
}


/**
 * Валидирует ставку - проверяет поле ставки на заполненность, а также на минимальное значение,
 * и возвращает текст ошибки в зависимости от нарушенного условия, или null, если валидация прошла успешно
 * @param string $value Значение ставки, введенное пользователем и переданное в $_POST
 *
 * @return string|null Текст ошибки, если условия не выполнены, или null, если ошибок не было
 */
function validateUserRate($value, $minPrice)
{
    if (empty($value)) {
        $error = 'Поле не может быть пустым';
    } else {
        $error = validatePriceStep($value, $minPrice);
    }

    return $error;
}


/**
 * Достает лот из БД по id, переданному в $_GET, и возвращает одномерный массив с данными лота,
 * или код ответа 404, если такого лота в БД нет
 * @param mysqli $connection Ресурс соединения
 *
 * @return array Массив с данными запрашиваемого лота, или код ответа 404, если лот не найден в БД
 */
function getLotFromDb($connection)
{
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
    }

    return $currentLot;
}

/**
 * Определяет максимальную ставку для текущего лота по таблице ставок,
 * и возвращает ее в случае, если на этот лот ставки были
 * @param mysqli $connection Ресурс соединения
 * @param array $lot Массив с данными лота
 *
 * @return integer Максимальная ставка для лота (если есть)
 */
function getMaxBet($connection, $lot)
{
    $id = $lot['id'];

    $sql = 'SELECT MAX(`price`) as max_bet FROM bets '
        . 'WHERE `lot_id`=' . $id;
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) !== 0) {
        $maxBet = mysqli_fetch_assoc($result);
    }

    return $maxBet['max_bet'];
}

/**
 * Вычислят минимальную ставку для текущего лота из рачета
 * минимальная ставка = текущая цена лота + шаг ставки
 * и возвращает дополненный массив лота, содержащий ее значение
 * @param array $currentLot Массив с данными лота
 *
 * @return array Дополненный минимальной ставкой массив с данными лота
 */
function calculateMinBet($currentLot)
{
    $minBet = $currentLot['currentPrice'] + $currentLot['step'];
    $currentLot['minBet'] = $minBet;

    return $currentLot;
}

/**
 * Обновляет значения ставок -
 * определяет текущее значение цены, сверяясь с таблицей ставок,
 * и если ставки делались, текущее значение цены будет скорректировано.
 * После определения текущей цены это значение используется для вычисления минимального значения ставки
 * @param mysqli $connection Ресурс соединения
 * @param array $lot Массив с данными лота
 *
 * @return array Обновленный массив с данными лота, содержащий текущую цену и минимальную ставку
 */
function updateLotBets($connection, $lot)
{
    $maxBet = getMaxBet($connection, $lot);

    if (!empty($maxBet)) {
        $lot['currentPrice'] = $maxBet;
    } else {
        $lot['currentPrice'] = $lot['price'];
    }

    $lot = calculateMinBet($lot);

    return $lot;
}

/**
 * Записывает ставку пользователя в таблицу ставок bets
 *
 * @param string $rate Значение ставки, переданное в $_POST
 * @param integer $userId Id авторизованного пользователя, сделавшего ставку
 * @param array $lot Массив с данными лота (используется для определения Id лота)
 * @param mysqli $connection Ресурс соединения
 *
 */
function insertBetToDb($rate, $userId, $lot, $connection)
{
    $bet = [
        'price' => intval($rate),
        'user_id' => $userId,
        'lot_id' => $lot['id']
    ];

    $sql = 'INSERT INTO bets
            (`date_created`, `price`, `user_id`, `lot_id`)
            VALUES (NOW(), ?, ?, ?)';

    $stmt = db_get_prepare_stmt($connection, $sql, $bet);
    mysqli_stmt_execute($stmt);
}

/**
 * Ищет в таблице ставок ставки для текущего лота, и если находит,
 * возвращает их в виде массива, дополненного информацией о дате ставки в человекопонятном формате.
 * Если ставок не было, возвращает пустой массив
 * @param mysqli $connection Ресурс соединения
 * @param array $lot Массив с данными лота
 *
 * @return array Массив с данными ставок
 */
function getBetsHistory($connection, $lot)
{
    $sql = 'SELECT bets.date_created, `price`, bets.user_id, bets.lot_id, users.name FROM bets '
        . 'JOIN `users` ON users.id = `user_id` '
        . 'WHERE bets.lot_id=' . $lot['id']
        . ' ORDER BY bets.date_created DESC ';

    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) !== 0) {
        $history = mysqli_fetch_all($result, MYSQLI_ASSOC);

        // выводим дату ставки в человекопонятном формате
        $history = convertHistoryDates($history);
    } else {
        $history = [];
    }

    return $history;
}

/**
 * Определяет, нужно ли показывать форму с полем для ввода ставки.
 * Форма не показывается, если:
 * - срок действия лота истек,
 * - лот создан авторизованным пользователем,
 * - последняя ставка для лота сделана авторизованным пользователем.
 * @param array $lot Массив с данными лота
 * @param array $history Массив с данными ставок для текущего лота
 * @param integer $userId Id авторизованного пользователя
 *
 * @return boolean true если форму можно показать, false если форма не должна отображаться
 */
function checkFormVisibility($lot, $history, $userId)
{
    $isVisible = checkLotDateActual($lot['expiration']);

    if (isset($history[0]['user_id']) && ($userId === $lot['author_id'] || $history[0]['user_id'] === $userId)) {
        $isVisible = false;
    }

    return $isVisible;
}


/**
 * Показывает шаблон в зависимости от наличия лота в БД, ориентируясь на код ответа.
 * Если код ответа 200, генерирует шаблон для отображения лота, и передает ему все нужные для отрисовки данные.
 * Во всех остальных случаях генерирует шаблон для страницы 404
 * @param array $lot Массив с данными лота
 * @param boolean $isAuth true если пользователь авторизован
 * @param string $error Текст ошибок валидации формы ввода ставки
 * @param array $history Массив с данными ставок для текущего лота
 * @param integer $userId Id авторизованного пользователя
 *
 * @return string Итоговый HTML
 */
function setTemplateData($lot, $isAuth, $error, $history, $userId)
{
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

    $error = ''; // ошибки могут возникнуть только при POST

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
$pageContent = setTemplateData($lot, $isAuth, $error, $history, $userId);

// задаем переменные окружения для передачи в layout
$environment = setEnvironment($lot['title'], $pageContent, $categories);

// окончательный HTML-код
$layoutContent = include_template('/layout.php', $environment);

print($layoutContent);
