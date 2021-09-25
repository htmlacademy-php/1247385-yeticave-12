<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title><?= $title; ?></title>
<link href="../css/normalize.min.css" rel="stylesheet">
<link href="../css/style.css" rel="stylesheet">
<?= $extraCss; ?>
</head>
<body>
<div class="page-wrapper">
    <header class="main-header">
        <div class="main-header__container container">
            <h1 class="visually-hidden">YetiCave</h1>
            <a class="main-header__logo" <?= $homePage ? "" : "href=/"?>>
                <img src="../img/logo.svg" width="160" height="39" alt="Логотип компании YetiCave">
            </a>
            <form class="main-header__search" method="get" action="search.php" autocomplete="off">
                <input type="search" name="search" placeholder="Поиск лота" value="<?= strip_tags($searchText); ?>">
                <input class="main-header__search-btn" type="submit" name="find" value="Найти">
            </form>
            <a class="main-header__add-lot button" href="add.php">Добавить лот</a>

            <nav class="user-menu">
            <!-- код для показа меню и данных пользователя -->
                <?php if ($isAuth): ?>
                    <div class="user-menu__logged">
                        <p><?= $userName; ?></p>
                        <a class="user-menu__bets" href="my-bets.php">Мои ставки</a>
                        <a class="user-menu__logout" href="/logout.php">Выход</a>
                    </div>
                <?php else: ?>
                    <ul class="user-menu__list">
                        <li class="user-menu__item">
                            <a href="/sign-up.php">Регистрация</a>
                        </li>
                        <li class="user-menu__item">
                            <a href="/login.php">Вход</a>
                        </li>
                    </ul>
                <?php endif; ?>

            </nav>
        </div>
    </header>

    <main <?= $addContainer ? 'class=container' : ''; ?>>
        <?= $homePage ? false : $navigation; ?>
        <?= $content; ?>
    </main>
</div>

<footer class="main-footer">
    <?= $navigation; ?>
    <?= $footer; ?>
</footer>

<?= $scripts; ?>

</body>
</html>
