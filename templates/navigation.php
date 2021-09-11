<nav class="nav">
    <ul class="nav__list container">
        <!--список из массива категорий-->
        <?php foreach ($categories as $category): ?>
            <li class="nav__item">
                <a href="all-lots.html"><?= strip_tags($category['title']); ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>