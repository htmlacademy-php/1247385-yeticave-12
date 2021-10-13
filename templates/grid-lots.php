<ul class="lots__list">
    <!--список из массива с товарами-->
    <?php foreach ($products as $product) : ?>
        <li class="lots__item lot">
            <div class="lot__image">
                <img src="<?= strip_tags($product['url']); ?>" width="350" height="260"
                     alt="<?= strip_tags($product['title']); ?>">
            </div>
            <div class="lot__info">
                <span class="lot__category"><?= strip_tags($product['category']); ?></span>
                <h3 class="lot__title">
                    <a class="text-link" href="lot.php?id=<?= strip_tags($product['id']); ?>">
                        <?= strip_tags($product['title']); ?>
                    </a>
                </h3>
                <div class="lot__state">
                    <div class="lot__rate">
                        <span class="lot__amount">Стартовая цена</span>
                        <span class="lot__cost"><?= strip_tags(formatPrice($product['price'])); ?></span>
                    </div>
                    <div class="lot__timer timer <?= $product['isNew'] ? 'timer--finishing' : '' ?>">
                        <?= $product['hours'] . ':' . $product['minutes'] ?>
                    </div>
                </div>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
