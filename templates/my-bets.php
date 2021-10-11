<section class="rates container">
    <h2>Мои ставки</h2>
    <table class="rates__list">
        <?php foreach ($history as $bet) : ?>
            <tr class="rates__item rates__item--<?= $bet['state']; ?>">
                <td class="rates__info">
                    <div class="rates__img">
                        <img src="<?= strip_tags($bet['url']); ?>" width="54" height="40"
                             alt="<?= strip_tags($bet['title']); ?>">
                    </div>
                    <div>
                        <h3 class="rates__title">
                            <a href="lot.php?id=<?= strip_tags($bet['lot_id']); ?>">
                                <?= strip_tags($bet['title']); ?>
                            </a>
                        </h3>
                        <?php if ($bet['state'] === 'win') : ?>
                            <p><?= strip_tags($bet['contact']); ?></p>
                        <?php endif; ?>
                    </div>
                </td>
                <td class="rates__category">
                    <?= strip_tags($bet['category']); ?>
                </td>
                <td class="rates__timer">
                <?php switch ($bet['state']) :
                    case 'win': ?>
                        <div class="timer timer--win">Ставка выиграла</div>
                    <?php break; ?>
                    <?php case 'end': ?>
                        <div class="timer timer--end">Торги окончены</div>
                    <?php break; ?>
                    <?php default: ?>
                        <div class="timer <?= $bet['isNew'] ? 'timer--finishing' : ''; ?>">
                            <?= $bet['hours'] . ':' . $bet['minutes'] . ':' . $bet['seconds']; ?>
                        </div>
                    <?php break; ?>
                <?php endswitch; ?>
                </td>
                <td class="rates__price">
                    <?= strip_tags($bet['step']); ?>
                </td>
                <td class="rates__time">
                    <?= strip_tags($bet['detailDate']); ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</section>
