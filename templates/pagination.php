<?php if ($pagesCount > 1): ?>
    <ul class="pagination-list">
        <li class="pagination-item pagination-item-prev">
            <a <?php if($currentPage > 1): ?>
                href="<?= setUrlPath($currentPage - 1) ?>"
            <?php endif; ?>
            >Назад</a>
        </li>
        <?php foreach($pages as $page): ?>
            <li class="pagination-item <?= ($page === $currentPage) ? 'pagination-item-active' : ''; ?>">
                <a href="<?= setUrlPath($page); ?>"><?= $page; ?></a>
            </li>
        <?php endforeach; ?>
        <li class="pagination-item pagination-item-next">
            <a <?php if($currentPage < $pagesCount): ?>
                href="<?= setUrlPath($currentPage + 1) ?>"
            <?php endif; ?>
            >Вперед</a>
        </li>
    </ul>
<?php endif; ?>
