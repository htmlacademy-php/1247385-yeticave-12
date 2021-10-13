<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
require_once 'db.php';
require_once 'helpers.php';
require_once 'vendor/autoload.php';

/**
 * Выбирает все лоты без победителей
 * с датой окончания действия лота меньше или равной текущей дате
 * и возвращает массив, состоящий из id найденных лотов
 * @param mysqli $connection Ресурс соединения
 *
 * @return array Массив с данными лотов без победителей
 */
function getLotsWithoutWinners($connection)
{
    $sql = 'SELECT lots.id FROM lots '
        . 'WHERE `date_exp` <= NOW() AND `winner_id` IS NULL';

    $lotsWithoutWinners = getDataFromDB($connection, $sql);

    return $lotsWithoutWinners;
}

/**
 * Отправляет победителю письмо с поздравлением
 * @param array $winner Массив, содержащий информацию о лоте, и победителе
 *
 */
function sendEmailToWinner($winner)
{
    // Create the Transport
    $transport = (new Swift_SmtpTransport('smtp.mailtrap.io', 2525))
        ->setUsername('014813d666f33f')
        ->setPassword('cdfc4430cce434');


    // Create the Mailer using created Transport
    $mailer = new Swift_Mailer($transport);

    // передадим путь сайта в шаблон письма
    $serverPath = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER["SERVER_NAME"];
    var_dump($serverPath);

    // Message's body
    $content = include_template('/email.php', [
        'winner' => $winner,
        'serverPath' => $serverPath
    ]);

    // Create a message
    $message = (new Swift_Message('Ваша ставка победила'))
        ->setFrom(['keks@phpdemo.ru' => 'Интернет-Аукцион "YetiCave"'])
        ->setTo([$winner['email'] => $winner['name']])
        ->setBody($content, 'text/html', 'utf8');

    // Send the message
    $mailer->send($message);
}


/**
 * Определяет победителя для каждого из найденных лотов,
 * и в случае если на лот были сделаны ставки, отправляет победителю письмо с поздравлением,
 * и обновляет лот в БД, добавляя ему winner_id - id пользователя, сделавшего последнюю ставку.
 * Если ставок на лот не было, лот в БД все равно обновляется, чтобы потом не искать его в БД повторно -
 * ему задается winner_id = 0.
 * @param mysqli $connection Ресурс соединения
 *
 * @return array Массив с данными лотов без победителей
 */
function determineTheWinner($connection)
{
    $lotsWithoutWinners = getLotsWithoutWinners($connection);

    if ($lotsWithoutWinners) {
        foreach ($lotsWithoutWinners as $lot) {
            // находим последнюю ставку для каждого лота
            $sql = 'SELECT lots.title, users.name, users.email,
                `user_id`, bets.lot_id FROM bets '
                . ' JOIN users ON users.id = `user_id`'
                . ' JOIN lots ON lots.id = bets.lot_id '
                . ' WHERE bets.lot_id=' . $lot['id']
                . ' ORDER BY bets.id DESC LIMIT 1';

            $result = mysqli_query($connection, $sql);

            if ($result && mysqli_num_rows($result) !== 0) {
                // если на лот делали ставки
                $winner = mysqli_fetch_assoc($result);

                $winnerId = intval($winner['user_id']);
                $winnerLot = intval($winner['lot_id']);

                sendEmailToWinner($winner);
            } else {
                // если на лот ставок не было, задаем winner_id=0 чтобы не искать его больше
                $winnerId = 0;
                $winnerLot = intval($lot['id']);
            }

            $sqlUpdateLot = 'UPDATE lots SET winner_id=' . $winnerId . ' WHERE id=' . $winnerLot;
            mysqli_query($connection, $sqlUpdateLot);
        }
    }
}

// вызываем функцию для определения победителей
determineTheWinner($connection);
