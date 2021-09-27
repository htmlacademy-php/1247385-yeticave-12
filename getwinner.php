<?php
require_once 'db.php';
require_once 'vendor/autoload.php';

function getLotsWithoutWinners($connection) {
    $sql = 'SELECT lots.id as lot_id, lots.title, lots.winner_id, users.name, users.email FROM lots '
        . 'JOIN users ON users.id = `author_id` '
        . 'WHERE `date_exp` <= NOW() AND `winner_id` IS NULL';

    $lotsWithoutWinners = getDataFromDB($connection, $sql);

    return $lotsWithoutWinners;
}

//$lotsWithoutWinners = getLotsWithoutWinners($connection);

function sendEmail() {
    // Create the Transport
    $transport = (new Swift_SmtpTransport('mailtrap.io', 25))
        ->setUsername('keks@phpdemo.ru')
        ->setPassword('htmlacademy')
    ;

    // Create the Mailer using your created Transport
    $mailer = new Swift_Mailer($transport);

    // Create a message
    $message = (new Swift_Message('Wonderful Subject'))
        ->setFrom(['john@doe.com' => 'John Doe'])
        ->setTo(['receiver@domain.org', 'other@domain.org' => 'A name'])
        ->setBody('Here is the message itself')
    ;

    // Send the message
    $result = $mailer->send($message);
}

function determineTheWinner($connection) {
    $lotsWithoutWinners = getLotsWithoutWinners($connection);

    if ($lotsWithoutWinners) {
        foreach ($lotsWithoutWinners as $lot) {
            var_dump($lot);

            // находим последнюю ставку для каждого лота
            $sql = 'SELECT `user_id`, `lot_id` FROM bets '
                . ' WHERE `lot_id`=' . $lot['lot_id']
                . ' ORDER BY id DESC LIMIT 1';

            $result = mysqli_query($connection, $sql);

            if ($result && mysqli_num_rows($result) !==0) {
                // если на лот делали ставки
                $winnerArray = mysqli_fetch_assoc($result);

                $winnerId = intval($winnerArray['user_id']);
                $winnerLot = intval($winnerArray['lot_id']);

            } else {
                // если на лот ставок не было
                $winnerId = 0;
                $winnerLot = intval($lot['lot_id']);
            }

            $sqlUpdateLot = 'UPDATE lots SET winner_id=' . $winnerId . ' WHERE id=' . $winnerLot;
            mysqli_query($connection, $sqlUpdateLot);
        }
    }

    return $lotsWithoutWinners;
}


