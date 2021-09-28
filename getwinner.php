<?php
require_once 'db.php';
require_once 'helpers.php';
require_once 'vendor/autoload.php';

// выбираем все лоты без победителей
function getLotsWithoutWinners($connection) {
    $sql = 'SELECT lots.id FROM lots '
        . 'WHERE `date_exp` <= NOW() AND `winner_id` IS NULL';

    $lotsWithoutWinners = getDataFromDB($connection, $sql);

    return $lotsWithoutWinners;
}

// отправляем победителю письмо с поздравлением
function sendEmailToWinner($winner) {
    // Create the Transport
    $transport = (new Swift_SmtpTransport('smtp.mailtrap.io', 2525))
        ->setUsername('014813d666f33f')
        ->setPassword('cdfc4430cce434')
    ;

    // Create the Mailer using created Transport
    $mailer = new Swift_Mailer($transport);

    // передадим путь сайта в шаблон письма
    $serverPath = $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"];

    // Message's body
    $content = include_template('/email.php', [
        'winner' => $winner,
        'serverPath' => $serverPath
    ]);

    // Create a message
    $message = (new Swift_Message('Ваша ставка победила'))
        ->setFrom(['keks@phpdemo.ru' => 'Интернет-Аукцион "YetiCave"'])
        ->setTo([$winner['email'] => $winner['name']])
        ->setBody($content, 'text/html', 'utf8')
    ;

    // Send the message
    $mailer->send($message);
}

// определяем победителя для найденных лотов
function determineTheWinner($connection) {
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

            if ($result && mysqli_num_rows($result) !==0) {
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
