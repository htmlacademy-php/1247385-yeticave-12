<div class="container">
    <section class="lots">
        <h2>Результаты поиска по запросу «<span><?= strip_tags($search); ?></span>»</h2>
        <?= $gridLots ? $gridLots : $errors; ?>
    </section>
    <?php if ($pagesCount > 1): ?>
        <ul class="pagination-list">
            <?php foreach ($pages as $page): ?>
<!--                <li class="pagination-item pagination-item-prev"><a>Назад</a></li>-->
                <li class="pagination-item <?= ($page === $currentPage) ? 'pagination-item-active' : ''; ?>">
                    <a href="/?page=<?= $page; ?>"><?= $page; ?></a>
                </li>
<!--                <li class="pagination-item pagination-item-next"><a href="#">Вперед</a></li>-->
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
