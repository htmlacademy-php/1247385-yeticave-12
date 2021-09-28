<?php
/**
 * Проверяет переданную дату на соответствие формату 'ГГГГ-ММ-ДД'
 *
 * Примеры использования:
 * is_date_valid('2019-01-01'); // true
 * is_date_valid('2016-02-29'); // true
 * is_date_valid('2019-04-31'); // false
 * is_date_valid('10.10.2010'); // false
 * is_date_valid('10/10/2010'); // false
 *
 * @param string $date Дата в виде строки
 *
 * @return bool true при совпадении с форматом 'ГГГГ-ММ-ДД', иначе false
 */
function is_date_valid(string $date) : bool {
    $format_to_check = 'Y-m-d';
    $dateTimeObj = date_create_from_format($format_to_check, $date);

    return $dateTimeObj !== false && array_sum(date_get_last_errors()) === 0;
}

/**
 * Создает подготовленное выражение на основе готового SQL запроса и переданных данных
 *
 * @param $link mysqli Ресурс соединения
 * @param $sql string SQL запрос с плейсхолдерами вместо значений
 * @param array $data Данные для вставки на место плейсхолдеров
 *
 * @return mysqli_stmt Подготовленное выражение
 */
function db_get_prepare_stmt($link, $sql, $data = []) {
    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {
        $errorMsg = 'Не удалось инициализировать подготовленное выражение: ' . mysqli_error($link);
        die($errorMsg);
    }

    if ($data) {
        $types = '';
        $stmt_data = [];

        foreach ($data as $value) {
            $type = 's';

            if (is_int($value)) {
                $type = 'i';
            }
            else if (is_string($value)) {
                $type = 's';
            }
            else if (is_double($value)) {
                $type = 'd';
            }

            if ($type) {
                $types .= $type;
                $stmt_data[] = $value;
            }
        }

        $values = array_merge([$stmt, $types], $stmt_data);

        $func = 'mysqli_stmt_bind_param';
        $func(...$values);

        if (mysqli_errno($link) > 0) {
            $errorMsg = 'Не удалось связать подготовленное выражение с параметрами: ' . mysqli_error($link);
            die($errorMsg);
        }
    }

    return $stmt;
}

/**
 * Возвращает корректную форму множественного числа
 * Ограничения: только для целых чисел
 *
 * Пример использования:
 * $remaining_minutes = 5;
 * echo "Я поставил таймер на {$remaining_minutes} " .
 *     get_noun_plural_form(
 *         $remaining_minutes,
 *         'минута',
 *         'минуты',
 *         'минут'
 *     );
 * Результат: "Я поставил таймер на 5 минут"
 *
 * @param int $number Число, по которому вычисляем форму множественного числа
 * @param string $one Форма единственного числа: яблоко, час, минута
 * @param string $two Форма множественного числа для 2, 3, 4: яблока, часа, минуты
 * @param string $many Форма множественного числа для остальных чисел
 *
 * @return string Рассчитанная форма множественнго числа
 */
function get_noun_plural_form (int $number, string $one, string $two, string $many): string
{
    $number = (int) $number;
    $mod10 = $number % 10;
    $mod100 = $number % 100;

    switch (true) {
        case ($mod100 >= 11 && $mod100 <= 20):
            return $many;

        case ($mod10 > 5):
            return $many;

        case ($mod10 === 1):
            return $one;

        case ($mod10 >= 2 && $mod10 <= 4):
            return $two;

        default:
            return $many;
    }
}

/**
 * Подключает шаблон, передает туда данные и возвращает итоговый HTML контент
 * @param string $name Путь к файлу шаблона относительно папки templates
 * @param array $data Ассоциативный массив с данными для шаблона
 *
 * @return string Итоговый HTML
 */
function include_template($name, array $data = []) {
    $name = 'templates/' . $name;
    $result = '';

    if (!is_readable($name)) {
        return $result;
    }

    ob_start();
    extract($data);
    require $name;

    $result = ob_get_clean();

    return $result;
}

/**
 * Подключает скрипты из заданного массива в шаблонах на страницах, где это необходимо,
 * передает туда данные и возвращает итоговый HTML для вставки в шаблон
 *
 * @return string Итоговый HTML
 */
function includeScripts() {
    $scripts = [
        'flatpickr.js',
        'script.js'
    ];

    $scriptTags = '';
    $scriptPath = '../';
    foreach ($scripts as $script) {
        $scriptTags .= "<script src='$scriptPath$script'></script>\n";
    }
    return $scriptTags;
}


/**
 * Форматирует целое число - принимает число и сравнивает его с 1000.
 * Если число меньше 1000 - ничего не делает,
 * если число больше 1000 - отделяет пробелом 3 последние цифры и добавляет знак рубля
 * @param integer $rawPrice Целое число для форматирования
 *
 * @return string Отформатированная сумма со знаком рубля
 */
function formatPrice(int $rawPrice) {
    $actualPrice = ceil($rawPrice);

    if ($actualPrice >= 1000) {
        $actualPrice = number_format($actualPrice, 0, '', ' ');
    }
    return $actualPrice . ' &#8381;';
}

/**
 * Возвращает время, оставшееся до даты из будущего в виде массива
 *
 * Пример вызова:
 * $res = get_dt_range("2019-10-11"); // [09, 29, 33]
 *
 * @param string $date Дата в виде строки в формате ГГГГ-ММ-ДД
 *
 * @return array Массив из трех элементов [часы, минуты, секунды]
 */
function getExpirationDate(string $date) {
    $currentDate = strtotime('now');
    $expiryDate = strtotime($date);

    $diff = $expiryDate - $currentDate;

    $hours = str_pad(floor($diff / 3600), 2, '0', STR_PAD_LEFT);
    $diff = $diff % 3600;

    $minutes = str_pad(floor($diff / 60), 2, "0", STR_PAD_LEFT);
    $seconds = str_pad(floor($diff %  60), 2, "0", STR_PAD_LEFT);

    return [$hours, $minutes, $seconds];
}

/**
 * Дополняет переданный массив лотов информацией о новизне продукта (isNew),
 * и временем, оставшимся до окончания действия лота (hours, minutes, seconds)
 *
 * @param array $products Массив, содержащий информацию о дате окончания лота
 *
 * @return array Дополненный исходный массив
 */
function createDetailProducts(array $products) {
    $detailProducts = [];

    foreach ($products as $product) {
        list($hours, $minutes, $seconds) = getExpirationDate($product['expiration']);

        $product['hours'] = $hours;
        $product['minutes'] = $minutes;
        $product['seconds'] = $seconds;
        $product['isNew'] = $hours < 1;

        $detailProducts[] = $product;
    }
    return $detailProducts;
}

/**
 * Сохраняет для пользователя введенное в поле формы значение
 *
 * @param mixed $name Имя атрибута name формы
 *
 * @return string Сохраненное значение, введенное пользователем в поле с атрибутом $name
 */
function getPostVal($name) {
    return filter_input(INPUT_POST, $name);
}

/**
 * Проверяет, есть ли категория в списке имеющихся категорий
 *
 * @param integer $id ID выбранной пользователем категории
 * @param array $categoriesIds Массив с ID категорий, имеющихся на сайте
 *
 * @return string|null Возвращает текст ошибки, если такой категории нет
 */
function validateCategory($id, $categoriesIds) {
    if (!in_array($id, $categoriesIds)) {
        return 'Выберите категорию из списка';
    }

    return null;
}

/**
 * Проверяет длину значения, введенного пользователем в поля формы на сайте
 *
 * @param string $value Значение, введенное пользователем
 * @param integer $min Требуемое минимальное количество символов
 * @param integer $max Требуемое максимальное количество символов
 *
 * @return string|null Возвращает текст ошибки, если длина поля выходит за заданные ограничения
 */
function validateLength(string $value, int $min, int $max) {
    if ($value) {
        $len = strlen($value);
        if ($len < $min || $len > $max) {
            return "Значение должно быть длиной от $min до $max символов";
        }
    }

    return null;
}

/**
 * Проверяет есть ли пользователь с указанным email в БД
 *
 * @param string $value Значение email, введенное пользователем
 * @param mysqli $connection Ресурс соединения
 *
 * @return string|null Возвращает текст ошибки, если пользователь уже зарегистрирован
 */
function checkEmailExists($value, $connection) {
    $email = mysqli_real_escape_string($connection, $value);
    $sql = "SELECT `id` FROM users WHERE email= '$email'";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0) {
        return "Пользователь с этим email уже зарегистрирован";
    }

    return null;
}

/**
 * Проверяет, корректно ли введен email пользователя, и если email корректный,
 * проверяет есть ли пользователь с таким email в БД
 *
 * @param string $value Значение email, введенное пользователем
 * @param mysqli $connection Ресурс соединения
 *
 * @return string|null Возвращает текст ошибки, если введен некорректный email,
 * или если пользователь уже зарегистрирован на сайте
 */
function validateEmailWithDB($value, $connection) {
    $email = filter_var($value, FILTER_VALIDATE_EMAIL);

    if($email) {
        return checkEmailExists($email, $connection);
    } else {
        return "Введите корректный email";
    }

    return null;
}

/**
 * Проверяет, корректно ли введен email пользователя (без проверки на существование в  БД)
 *
 * @param string $value Значение email, введенное пользователем
 *
 * @return string|null Возвращает текст ошибки, если введен некорректный email
 */
function validateEmail($value) {
    $email = filter_var($value, FILTER_VALIDATE_EMAIL);

    if (!$email) {
        return "Введите корректный email";
    }
}

/**
 * Проверяет значение цены, введенной пользователем, на соответствие формату
 *
 * @param float $value Значение, введенное пользователем
 *
 * @return string|null Возвращает текст ошибки, если введенное значение цены меньше или равна нулю
 */
function validatePrice($value) {
    $step = filter_var($value, FILTER_VALIDATE_FLOAT);

    if(!$step || $step <=0) {
        return "Начальная цена должна быть числом больше ноля";
    }

    return null;
}

/**
 * Проверяет значение шага цены, введенного пользователем, на соответствие формату
 *
 * @param integer $value Значение, введенное пользователем
 * @param integer $minRange Требуемое минимальное значение, по умолчанию шаг не может быть меньше 1
 *
 * @return string|null Возвращает текст ошибки, если введенное значение меньше $minRange
 */
function validatePriceStep($value, $minRange = 1) {
    $options = ['options' => ['min_range' => $minRange]];
    $step = filter_var($value, FILTER_VALIDATE_INT, $options);

    if(!$step) {
        return "Введите целое число больше или равно $minRange";
    }

    return null;
}

/**
 * Проверяет что дата, введенная пользователем, корректна, и соответствует формату ГГГГ-ММ-ДД,
 * а также больше текущей даты хотя бы на 1 день
 * @param string $date Значение даты, введенное пользователем
 *
 * @return string|null Возвращает текст ошибки, если дата не соответствует формату
 */
function validateDate($date) {
    $currentDate = strtotime('now');
    $expiryDate = strtotime($date);

    $diff = ($expiryDate - $currentDate) / 86400;

    if(!is_date_valid($date)) {
        return "Введите корректную дату в формате ГГГГ-ММ-ДД";
    }

    if($diff < 1) {
        return "Дата окончания торгов должна быть больше текущей даты, хотя бы на один день";
    }

    return null;
}

/**
 * Проверяет что пользователь загрузил изображение в одном из форматов jpg/jpeg/png
 *
 * @return string|null Возвращает текст ошибки, если изображение не соответствует формату
 */
function validateImg() {
    if (!empty($_FILES['lot-img']['name'])) {
        $mimeTypes = ['image/png', 'image/jpeg'];

        $tmpName = $_FILES['lot-img']['tmp_name'];

        $fileType = mime_content_type($tmpName);

        if (!in_array($fileType, $mimeTypes)) {
            return 'Допустимый формат для изображений - jpg, jpeg, png';
        }
    } else {
        return 'Загрузите изображение';
    }

    return null;
}

/**
 * Сохраняет изображение в папку uploads и возвращает путь до него на сервере
 *
 * @return string Возвращает путь до сохраненного изображения
 */
function getImageUrl() {
    $fileName = $_FILES['lot-img']['name'];
    $tmpName = $_FILES['lot-img']['tmp_name'];
    $filePath = __DIR__ . '/uploads/';
    $fileUrl = '/uploads/' . $fileName;

    move_uploaded_file($tmpName, $filePath . $fileName);

    return $fileUrl;
}

/**
 * Предоставляет доступ к контенту только зарегистрированным пользователям.
 * Проверяет, что есть открытая сессия для пользователя, и если нет, выводит
 * предупреждение о необходимости зарегистрироваться со ссылками на страницу
 * регистрации и входа
 */
function loginRequired() {
    if (empty($_SESSION['user'])) {
        http_response_code(403);
        echo '<h1>Error 403</h1>
                <h2>Страница доступна только зарегистрированным пользователям</h2>
                <p><a href="sign-up.php">Зарегистрироваться</a> или <a href="login.php">Войти</a></p>';
        exit();
    }
}

/**
 * Предоставляет доступ к контенту только НЕзарегистрированным пользователям.
 * Проверяет, что есть открытая сессия для пользователя, и если да, выводит
 * предупреждение что пользователь уже зарегистрирован со ссылкой на страницу
 * выхода из аккаунта
 */
function alreadyRegisteredUser() {
    if (!empty($_SESSION['user'])) {
        http_response_code(403);
        echo '<h1>Error 403</h1>
                <h2>Вы уже зарегистрированы!</h2>
                <p><a href="logout.php">Выйти из аккаунта</a></p>';
        exit();
    }
}

/**
 * Проверяет актуальность даты окончания лота.
 * Если дата окончания лота меньше или равна текущей, срок лота истек, вернется false.
 * Если дата окончания больше текущей, лот действующий, вернется true
 * @param string $date Дата окончания лота
 *
 * @return boolean Возвращает true, если лот действующий, и false если срок лота истек
 */
function checkLotDateActual($date) {
    $currentDate = strtotime('now');
    $expiryDate = strtotime($date);

    $diff = $expiryDate - $currentDate;

    if ($diff <= 0) {
        return false;
    }

    return true;
}

/**
 * Для каждого элемента массива на основании имеющейся даты ставки выводит дату в человекопонятном формате
 * с помощью функции get_noun_plural_form
 * @param array $history Массив с данными сделанных ставок
 *
 * @return array Возвращает исходный массив, дополненный датой в человекопонятном формате
 */
function convertHistoryDates(array $history) {
    $detailHistory = [];

    foreach ($history as $unit) {

        $currentDate = strtotime('now');
        $expiryDate = strtotime($unit['date_created']);

        $diff = $currentDate - $expiryDate;

        $hours = floor($diff / 3600);
        $minutes = floor(($diff % 3600) / 60);

        switch ($hours) {
            case ($hours < 0):
                $unit['detailDate'] = $minutes . ' ' . get_noun_plural_form($minutes,
                        'минута', 'минуты', 'минут') . ' назад';
                break;
            case ($hours >= 1 && $hours < 24):
                $unit['detailDate'] = $hours . ' ' . get_noun_plural_form($hours,
                        'час', 'часа', 'часов') . ' '
                        . $minutes . ' ' . get_noun_plural_form($minutes,
                        'минута', 'минуты', 'минут') . ' назад';
                break;
            case ($hours >= 24 && $hours < 48):
                $unit['detailDate'] = date_format(date_create($unit['date_created']), 'вчера в H:i');
                break;
            default:
                $unit['detailDate'] = date_format(date_create($unit['date_created']), 'd.m.Y в H:i');
        }

        $detailHistory[] = $unit;
    }

    return $detailHistory;
}

/**
 * Используется для пагинации. Помогает построить корректный url,
 * содержащий имя исполняемого скрипта и переданный в $_GET параметр 'page' - номер страницы
 * @param string $value Номер страницы
 *
 * @return string Возвращает корректный URL, используемый для перехода на нужную страницу
 */
function setUrlPath($value) {
    $params = $_GET;
    $page = intval($value);
    $params['page'] = $page;

    return $_SERVER['SCRIPT_NAME'] . '?' . http_build_query($params);
}

/**
 * Создает пагинацию для переданного массива с лотами.
 * Показывает 9 лотов на каждой странице (по ТЗ)
 *
 * @param array $lots Массив с лотами
 *
 * @return array Возвращает массив из лотов, разбитых с учетом пагинации,
 * и шаблон с номерами страниц и оформлением с уже переданными данными для отрисовки
 */
function createPagination($lots) {
    $itemsCount = count($lots); // количество найденных в БД лотов

    $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 9; // сколько лотов будет показано на странице
    $offset = ($currentPage - 1) * $limit;

    $pagesCount = intval(ceil($itemsCount / $limit)); // сколько будет страниц
    $pages = range(1, $pagesCount);

    $products = array_slice($lots, $offset, $limit, true);

    $templateData['products'] = $products;

    // HTML-код блока с пагинацией
    $pagination = include_template('/pagination.php', [
        'pagesCount' => $pagesCount,
        'pages' => $pages,
        'currentPage' => $currentPage
    ]);
    $templateData['pagination'] = $pagination;

    return $templateData;
}
