<section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
        <?php foreach ($history as $bet): ?>
            <tr class="rates__item rates__item--<?= $bet['state']; ?>">
                <td class="rates__info">
                    <div class="rates__img">
                        <img src="<?= $bet['url']; ?>" width="54" height="40" alt="<?= $bet['title']; ?>">
                    </div>
                    <h3 class="rates__title"><a href="lot.html"><?= $bet['title']; ?></a></h3>
                </td>
                <td class="rates__category">
                    <?= $bet['category']; ?>
                </td>
                <td class="rates__timer">
                    <div class="timer <?= $bet['isNew'] ? 'timer--finishing' : ''; ?>"><?= $bet['hours'] . ':' . $bet['minutes'] . ':' . $bet['seconds'];; ?></div>
                </td>
                <td class="rates__price">
                    <?= formatPrice($bet['price']); ?>
                </td>
                <td class="rates__time">
                    <?= $bet['detailDate']; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
