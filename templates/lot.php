<section class="lot-item container">
    <h2><?= $lot['title']; ?></h2>
    <div class="lot-item__content">
        <div class="lot-item__left">
            <div class="lot-item__image">
                <img src="<?= $lot['url']; ?>" width="730" height="548" alt="<?= $lot['title']; ?>">
            </div>
            <p class="lot-item__category">Категория: <span><?= $lot['category']; ?></span></p>
            <p class="lot-item__description"><?= $lot['description']; ?></p>
        </div>
        <div class="lot-item__right">
            <?php if ($isAuth): ?>
                <div class="lot-item__state">
                    <div class="lot-item__timer timer <?= $lot['isNew'] ? 'timer--finishing' : '' ?>">
                        <?= $lot['hours'] . ':' . $lot['minutes'] ?>
                    </div>
                    <div class="lot-item__cost-state">
                        <div class="lot-item__rate">
                            <span class="lot-item__amount">Текущая цена</span>
                            <span class="lot-item__cost"><?= $currentPrice; ?></span>
                        </div>
                        <div class="lot-item__min-cost">
                            Мин. ставка <span><?= formatPrice($lot['minBet']); ?></span>
                        </div>
                    </div>
                    <?php if ($isVisible): ?>
                        <form class="lot-item__form" action="lot.php?id=<?= $lot['id']; ?>" method="post"
                              autocomplete="off">
                            <p class="lot-item__form-item <?= !empty($error) ? 'form__item--invalid' : ''; ?>">
                                <label for="cost">Ваша ставка</label>
                                <input id="cost" type="text" name="cost"
                                       placeholder="<?= formatPrice($lot['minBet']); ?>"
                                       value="<?= $error ? getPostVal('cost') : ''; ?>">
                                <span class="form__error"><?= $error; ?></span>
                            </p>
                            <button type="submit" class="button">Сделать ставку</button>
                        </form>
                    <?php endif; ?>
                </div>
                <div class="history">
                    <h3>История ставок (<span><?= $history ? count($history) : 0; ?></span>)</h3>
                    <?php if ($history): ?>
                        <table class="history__list">
                            <?php foreach ($history as $bet): ?>
                                <tr class="history__item">
                                    <td class="history__name"><?= strip_tags($bet['name']); ?></td>
                                    <td class="history__price"><?= strip_tags(formatPrice($bet['price'])); ?></td>
                                    <td class="history__time"><?= $bet['detailDate']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </table>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
