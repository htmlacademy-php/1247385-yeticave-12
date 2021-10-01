<h1>Поздравляем с победой</h1>
<p>Здравствуйте, <?= strip_tags($winner['name']); ?></p>
<p>Ваша ставка для лота <a href="<?= $serverPath . '/lot.php?id=' . $winner['lot_id']; ?>">
        <?= strip_tags($winner['title']); ?></a> победила.</p>
<p>Перейдите по ссылке <a href="<?= $serverPath . '/my-bets.php' ?>">мои ставки</a>,
    чтобы связаться с автором объявления</p>
<small>Интернет-Аукцион "YetiCave"</small>
