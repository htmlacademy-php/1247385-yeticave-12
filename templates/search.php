<div class="container">
    <section class="lots">
        <h2>Результаты поиска по запросу «<span><?= strip_tags($search); ?></span>»</h2>
        <?= $gridLots ?? $errors; ?>
    </section>
    <?= $pagination; ?>
</div>
