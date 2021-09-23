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

function includeScripts(array $scripts) {
    $scriptTags = '';
    $scriptPath = '../';
    foreach ($scripts as $script) {
        $scriptTags .= "<script src='$scriptPath$script'></script>\n";
    }
    return $scriptTags;
}

function formatPrice(int $rawPrice) {
    $actualPrice = ceil($rawPrice);

    if ($actualPrice >= 1000) {
        $actualPrice = number_format($actualPrice, 0, '', ' ');
    }
    return $actualPrice . ' &#8381;';
}

function getExpirationDate($date) {
    $currentDate = strtotime('now');
    $expiryDate = strtotime($date);

    $diff = $expiryDate - $currentDate;

    $hours = str_pad(floor($diff / 3600), 2, '0', STR_PAD_LEFT);
    $diff = $diff % 3600;

    $minutes = str_pad(floor($diff / 60), 2, "0", STR_PAD_LEFT);
    $seconds = str_pad(floor($diff %  60), 2, "0", STR_PAD_LEFT);

    return [$hours, $minutes, $seconds];
}

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

function getPostVal($name) {
    return filter_input(INPUT_POST, $name);
}

function validateCategory($id, $categoriesIds) {
    if (!in_array($id, $categoriesIds)) {
        return 'Выберите категорию из списка';
    }

    return null;
}

function validateLength($value, $min, $max) {
    if ($value) {
        $len = strlen($value);
        if ($len < $min || $len > $max) {
            return "Значение должно быть длиной от $min до $max символов";
        }
    }

    return null;
}

function checkEmailExists($value, $connection) {
    $email = mysqli_real_escape_string($connection, $value);
    $sql = "SELECT `id` FROM users WHERE email= '$email'";
    $result = mysqli_query($connection, $sql);

    if (mysqli_num_rows($result) > 0) {
        return "Пользователь с этим email уже зарегистрирован";
    }

    return null;
}

function validateEmailWithDB($value, $connection) {
    $email = filter_var($value, FILTER_VALIDATE_EMAIL);

    if($email) {
        return checkEmailExists($email, $connection);
    } else {
        return "Введите корректный email";
    }

    return null;
}

function validateEmail($value) {
    $email = filter_var($value, FILTER_VALIDATE_EMAIL);

    if (!$email) {
        return "Введите корректный email";
    }
}

function validatePrice($value) {
    $step = filter_var($value, FILTER_VALIDATE_FLOAT);

    if(!$step || $step <=0) {
        return "Начальная цена должна быть числом больше ноля";
    }

    return null;
}

function validatePriceStep($value, $minRange = 1) {
    $options = ['options' => ['min_range' => $minRange]];
    $step = filter_var($value, FILTER_VALIDATE_INT, $options);

    if(!$step) {
        return "Введите целое число больше или равно $minRange";
    }

    return null;
}

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

function getImageUrl() {
    $fileName = $_FILES['lot-img']['name'];
    $tmpName = $_FILES['lot-img']['tmp_name'];
    $filePath = __DIR__ . '/uploads/';
    $fileUrl = '/uploads/' . $fileName;

    move_uploaded_file($tmpName, $filePath . $fileName);

    return $fileUrl;
}

function loginRequired() {
    if (empty($_SESSION['user'])) {
        http_response_code(403);
        echo '<h1>Error 403</h1>
                <h2>Страница доступна только зарегистрированным пользователям</h2>
                <p><a href="sign-up.php">Зарегистрироваться</a> или <a href="login.php">Войти</a></p>';
        exit();
    }
}

function alreadyRegisteredUser() {
    if (!empty($_SESSION['user'])) {
        http_response_code(403);
        echo '<h1>Error 403</h1>
                <h2>Вы уже зарегистрированы!</h2>
                <p><a href="logout.php">Выйти из аккаунта</a></p>';
        exit();
    }
}

function checkLotDateActual($date) {
    $currentDate = strtotime('now');
    $expiryDate = strtotime($date);

    $diff = $expiryDate - $currentDate;

    if ($diff <= 0) {
        return false;
    }

    return true;
}

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
