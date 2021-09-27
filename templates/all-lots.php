<div class="container">
    <section class="lots">
        <h2>Все лоты в категории <span>«<?= strip_tags($category); ?>»</span></h2>
        <?= $gridLots ?? $errors; ?>
    </section>
    <?= $pagination; ?>
</div>




