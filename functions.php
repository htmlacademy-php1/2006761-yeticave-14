<?php

/**
 * Добавялет в конец цены знак рубля
 *
 * @param int $price Цена
 *
 *       return string
 */
function priceModify(int $price): string
{
    ceil($price);
    if ($price > 1000) {
        $price = number_format($price, 0, '', ' ');
    }
    return (string) $price . ' ₽';
}

/**
* Изменяет полученную дату в формат: ЧЧ:ММ
*
* @param string $date Полученная дата
*
* return string
*/
function formatTimer(string $date): string
{
    $dateFinish = strtotime($date);
    $secDifference = $dateFinish - time();
    $hours = floor($secDifference / 3600);
    $minutes = floor(($secDifference % 3600) / 60);

    return "{$hours}:{$minutes}";
}

/**
* Проверяет что полученная дата не истекает в течении часа
*
* @param string $date Полученная дата
*
* return string Возвращает класс 'timer--finishing' или ''
*/
function oneHourTimerFinishing(string $date): string
{
    $dateFinish = strtotime($date);
    $secDifference = $dateFinish - time();
    $oneHour = 60 * 60;
    $isLessOneHour = $secDifference <= $oneHour;

    return $isLessOneHour ? 'timer--finishing' : '';
}

/**
* Получает все категории
*
* @param mysqli $link Ресурс соединения
*
* return array
*/
function getCategories(mysqli $link): array
{
    $sql = 'SELECT * FROM category';
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print('Error MySQL: ' . mysqli_error($link));
    exit();
}

/**
* Получает все лоты
*
* @param mysqli $link Ресурс соединения
*
* return array
*/
function getPosters(mysqli $link): array
{
    $sql = 'SELECT l.id AS id, l.name AS lot_name, start_price, img_url, finished_at, c.name AS cat_name FROM lot AS l
            JOIN category AS c ON c.id = l.category_id
            WHERE l.finished_at > NOW()
            ORDER BY l.created_at DESC';
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } 
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
* Получает максимальную цену ставки текущего пользователя
*
* @param mysqli $link Ресурс соединения
* @param int $lotId Лот текущего пользователя
*
* return array Если нет ставок то false
*/
function getCatLotMaxPrice(mysqli $link, int $lotId): mixed
{
    $sql = 'SELECT l.name as lot_name, l.description, l.img_url, l.finished_at, l.start_price, l.step_price,
                   c.name as cat_name, c.symbol_code,
                   b.price
            FROM lot AS l
            JOIN category AS c ON l.category_id = c.id
            LEFT JOIN bid AS b ON b.lot_id = l.id
            WHERE l.id = ?
            ORDER BY price DESC';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lotId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit;
}
/**
* Получает информацию о ставках текущего пользователя
*
* @param mysqli $link Ресурс соединения
* @param int $lotId Лот текущего пользователя
*
* return array
*/
function getBidUser(mysqli $link, int $lotId): array
{
    $sql = 'SELECT b.lot_id, b.price, b.created_at, u.name AS user_name FROM bid AS b
            JOIN user AS u ON b.user_id = u.id
            WHERE b.lot_id = ?';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lotId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
 * Функция получает значение из POST-запроса для сохранения введённых значений в полях формы
 *
 * @param mixed $val Значение
 *
 * @return string
 */
function getPostVal(mixed $val): ?string
{
    return $_POST[$val] ?? '';
}

/**
 * Добавляет значения о лоте пользователя в БД
 *
 * @param mysqli $link Ресурс соединения
 * @param array $lot Данные из формы
 * @param array $files Файл пользователя
 *
 * @return bool Положительное состояние выполнения запроса
 */
function addLot(mysqli $link, array $lot, array $files): bool
{
    $lot['finished_at'] = date("Y-m-d H:i:s", strtotime($lot['finished_at']));
    $lot['img_url'] = uploadFile($files);
    $lot['user_id'] = $_SESSION['user']['id'];

    $sql = 'INSERT INTO lot
            (name, category_id, description, start_price, step_price, finished_at, img_url, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($link, $sql, $lot);
   
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        return $result;
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();

}

/**
 * Переименовывает файл и перемещает в папку uploads
 *
 * @param array $files Файл
 *
 * @return string Путь к файлу
 */
function uploadFile(array $files): string
{
    $tmpName = $files['img_url']['tmp_name'];
    $fileType = mime_content_type($tmpName);

    switch ($fileType) {
        case 'image/png':
            $extension = '.png';
            break;
        case 'image/jpeg':
            $extension = '.jpeg';
            break;
    }
    $fileName = uniqid() . $extension;
    move_uploaded_file($tmpName, 'uploads/' . $fileName);

    return 'uploads/' . $fileName;
}

/**
 * Проверяет поля name, category_id, description, start_price, step_price, finished_at из формы добавления лота
 *
 * @param array $lot Массив лотов
 * @param array $categoriesId Массив категорий
 * @param array $files Файл пользователя
 *
 * @return array Ошибки
 */
function validateFormAdd(array $lot, array $categoriesId, array $files): array
{
    $requiredFields = ['name', 'category_id', 'description', 'start_price', 'step_price', 'finished_at'];

    $rules = [
        'category_id' => function ($ids) use ($categoriesId) {
            return validateCategory($ids, $categoriesId);
        },
        'start_price' => function ($startPrice) {
            return validateValue($startPrice);
        },
        'finished_at' => function ($finishedAt) {
            return validateFinishedAt($finishedAt);
        },
        'step_price' => function ($stepPrice) {
            return validateValue($stepPrice);
        }
    ];

    $errors = [];

    //Проходим по полученным значения из формы и применяем к ним функции валидации
    foreach ($lot as $key => $value) {
        if (isset($rules[$key])) {
            $rule = $rules[$key];
            $errors[$key] = $rule($value);
        }
        //Входит ли поле к списку заполнения
        if (in_array($key, $requiredFields) && empty($value)) {
            //Есть ли уже ошибки в данном массиве
            $errors[$key] = empty($errors[$key]) ? 'Поле надо заполнить' : $errors[$key] ;
        }
    }

    $errors['img_url'] = validateImg($files);

    return $errors;
}

/**
 * Проверяет значение на существование категории из полученного массива
 *
 * @param $ids string Значение
 * @param $allowedList array Массив из категорий
 *
 * @return string Ошибка или null
 */
function validateCategory(string $ids, array $allowedList): ?string
{
    if (!in_array($ids, $allowedList)) {
        return 'Указана несуществующая категория';
    }
    return null;
}

/**
 * Проверяет, на то что значение должно быть больше нуля
 *
 * @param $value string Значение
 *
 * @return string Ошибка или null
 */
function validateValue(string $value): ?string
{
    $value = intval($value);
    if ($value <= 0) {
        return 'Значение должно быть больше нуля';
    }
    return null;
}

/**
 * Проверяет полученное значение на соответствие формата даты
 *
 * @param $finishedAt string Дата
 *
 * @return string Ошибки или null
 */
function validateFinishedAt(string $finishedAt): ?string
{
    if (!is_date_valid($finishedAt)) {
        return 'Значение должно быть датой в формате «ГГГГ-ММ-ДД»';
    }
    $time = getDateRange($finishedAt, 'now');
    if ($time[0] < 24) {
        return 'Дата должна быть больше текущей даты, хотя бы на один день.';
    }
    return null;
}

/**
 * Получает массив, где первый элемент — целое количество часов до даты, а второй — остаток в минутах.
 *
 * @param string $finishedAt Дата окончания
 * @param string $now Текущая дата.
 *
 * @return array
 */
function getDateRange(string $finishedAt, string $now): array
{
    $finishedAt = date_create($finishedAt);
    $now = date_create($now);

    if ($finishedAt <= $now) {
        return [0, 0];
    }
    $diff = date_diff($now, $finishedAt);

    $days = $diff->days;
    $hours = $diff->h;
    $minutes = $diff->i;

    if ($days > 0) {
        $hours = $days * 24 + $hours;
        return [$hours, $minutes];
    }

    return [$hours, $minutes];
}

/**
 * Проверяет полученный файл на соответствие расширению изображения
 *
 * @param $files array Файл
 *
 * @return string Ошибки
 */
function validateImg(array $files): string
{
    if (empty($files['img_url']['name'])) {
        return 'Загрузите картинку';
    }

    $tmpName = $files['img_url']['tmp_name'];
    $fileType = mime_content_type($tmpName);

    if ($fileType !== 'image/png' && $fileType !== 'image/jpeg') {
        return 'Загрузите картинку в формате png или jpeg';
    }

    return '';
}

/**
 * Добавляет нового пользователя в БД
 *
 * @param $link mysqli Ресурс соединения
 * @param $registration array Данные из формы
 *
 * @return bool Положительное состояние выполнения запроса
 */
function addUser(mysqli $link, array $registration): bool
{
    $sql = 'INSERT INTO user (email, password, name, contacts) VALUES (?, ?, ?, ?)';
    $stmt = db_get_prepare_stmt($link, $sql, $registration);
    $result = mysqli_stmt_execute($stmt);
    if ($result) {
        return true;
    }
    print("Error: Запрос не выполнен" . mysqli_error($link));
    exit();
}

/**
 * Проверяет email на корректность и не занят ли он 
 *
 * @param $link mysqli Ресурс соединения
 * @param $registration array Данные из формы
 *
 * @return string Ошибки
 */
function validateEmail(mysqli $link, array $registration): string
{
    $email = mysqli_real_escape_string($link, $registration['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Некорректный e-mail';
    }

    $sql = "SELECT email FROM user WHERE email = '" . $email . "'";
    $result = mysqli_query($link, $sql);
    if ($result) {
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return !empty($result) ? 'Данный e-mail занят' : '';
    }
    print("Error MySQL: " . mysqli_error($link));
    die();
}

/**
 * Проверяет форму на ошибки полей email, password, name, contacts
 *
 * @param $link mysqli Ресурс соединения
 * @param $registration array Данные из формы
 *
 * @return array Ошибки
 */
function validateFormSignUp(mysqli $link, array $registration): array
{
    $requiredFields = ['email', 'password', 'name', 'contacts'];

    $errors = [];
    foreach ($registration as $key => $value) {
        if (in_array($key, $requiredFields) && empty($value)) {
            $errors[$key] = 'Поле надо заполнить';
        } elseif ($key === 'email') {
            $errors['email'] = validateEmail($link, $registration);
        }
    }
    return $errors;
}

/**
 * Получает данные о пользователе из БД
 *
 * @param $link mysqli Ресурс соединения
 * @param $email string Текущий email
 *
 * @return array
 */
function getUserDb(mysqli $link, string $email): array
{
    $sql = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

    return $result && !empty($user) ? $user : [];
}

/**
 * Проверяет форму на ошибки полей email и password
 *
 * @param $login array Данные из формы
 * @param $user mixed Данные о пользователе
 *
 * @return array Ошибки
 */
function validateFormLogin(array $login, mixed $user): array
{
    $requiredFields = ['email', 'password'];

    $errors = [];
    foreach ($login as $key => $value) {
        if (in_array($key, $requiredFields) && empty($value)) {
            $errors[$key] = 'Поле надо заполнить';
        }
    }

    if (!$user && empty($errors['email'])) {
        $errors['email'] = 'Такой пользователь не найден';
    }

    return $errors;
}

/**
 * Получает имя авторизованного пользователя из $_SESSION;
 *
 * @return string
 */
function getSessionName(): string
{
    return $_SESSION['user']['name'] ?? '';
}

/**
 * Получает id авторизованного пользователя из $_SESSION;
 *
 * @return string
 */
function getSessionUserId(): string
{
    return $_SESSION['user']['id'] ?? '';
}

/**
 * Собирает страницу с 403 ошибкой
 *
 * @param array $sqlCategories Категории из БД
 * @param string $userName Текущий пользователь
 *
 * @return void
 */
function errorPage(array $sqlCategories, string $userName): void
{
    $pageContent = include_template('403.php', ['sqlCategories' => $sqlCategories,]);

    $layoutContent = include_template(
        'layout.php',
        ['content' => $pageContent,
        'categories' => $sqlCategories,
        'title' => 'Доступ запрещен',
        'userName' => $userName, ]
    );

    print($layoutContent);
    exit();
}

/**
 * Собирает страницу с 404 ошибкой
 *
 * @param array $sqlCategories Категории из БД
 * @param string $userName Текущий пользователь
 *
 * @return void
 */
function notFoundPage(array $sqlCategories, string $userName): void
{
    $pageContent = include_template(
        '404.php',
        ['sqlCategories' => $sqlCategories,
         'userName' => $userName, ]
    );

    $layoutContent = include_template(
        'layout.php',
        ['content' => $pageContent,
        'categories' => $sqlCategories,
        'title' => 'Страница не найдена',
        'userName' => $userName, ]
    );

    print($layoutContent);
    exit();
}

/**
 * Проверяет совпадение пароля из БД
 *
 * @param array $login Данные с паролем из БД
 * @param array $user Данные с паролем от пользователя
 *
 * @return bool
 */
function checkPassword(array $login, array $user): bool
{
    return password_verify($login['password'], $user['password']);
}

/**
 * Полнотекстовый поиск по полям title, description с ограничением по количеству элементов
 *
 * @param mysqli $link Ресурс соединения
 * @param string $search Текст из поиска
 * @param int $limit Количества лотов на странице
 * @param int $offset Отступ
 *
 * @return array
 */
function getLotBySearch(mysqli $link, string $search, int $limit, int $offset): array
{
    $sql = 'SELECT l.id, l.name AS lot_name, l.description, l.start_price, l.img_url, l.finished_at, c.name AS cat_name
            FROM lot l
            JOIN category c ON l.category_id = c.id
            WHERE  MATCH(l.name, l.description) AGAINST(? IN BOOLEAN MODE) AND l.finished_at > NOW() ORDER BY l.created_at LIMIT ? OFFSET ?';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $search, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();

}

/**
 * Количество лотов найденное полнотекстовым поиском
 *
 * @param mysqli $link Ресурс соединения
 * @param string $search Текст из поиска
 *
 * @return int
 */
function getCountLotBySearch(mysqli $link, string $search): int
{
    $sql = 'SELECT l.id, l.name AS lot_name, l.description, l.start_price, l.img_url, l.finished_at, c.name AS cat_name
            FROM lot l
            JOIN category c ON l.category_id = c.id
            WHERE  MATCH(l.name, l.description) AGAINST(? IN BOOLEAN MODE)';

    $stmt = db_get_prepare_stmt($link, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        return count(mysqli_fetch_all($result, MYSQLI_ASSOC));
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
 * Получает массив переменных для пагинации
 *
 * @param int $current Текущая страница
 * @param int $countLot Количество лотов
 * @param int $limit Количество лотов на одной странице
 *
 * @return int
 */
function createPagination(int $current, int $countLot, int $limit): array
{
    $countPage = (int)ceil($countLot/$limit); //Получаем кол-во страниц
    $pages = range(1, $countPage); //Создаём массив страниц

    $prev = ($current > 1) ? $current - 1 : $current;
    $next = ($current < $countPage) ? $current + 1 : $current;

    return ['prevPage' => $prev,
          'nextPage' => $next,
          'countPage' => $countPage,
          'pages' => $pages,
          'currentPage' => $current,
          'lotLimit' => $limit
         ];
}

/**
 * Получает актуальную цену, минимальную цену, исходя из сделанных ставок
 *
 * @param array $sqlBidUser Ставки текущего пользователя
 * @param array $sqlCatLot Информация о лоте
 *
 * @return array
 */
function getPrice(array $sqlBidUser, array $sqlCatLot): array
{
    $currentPrice = empty($sqlBidUser) ? $sqlCatLot['start_price'] : $sqlCatLot['price'];
    $minBid = $currentPrice + $sqlCatLot['step_price'];

    return ['currentPrice' => $currentPrice,
           'minBid' => $minBid
          ];
}

/**
 * Проверяет на ошибки форму стартовой цены
 *
 * @param string $userPrice Цена введенная пользователем
 * @param array $price Минимально допустимая цена
 *
 * @return array Ошибки
 */
function validateFormLot(string $userPrice, array $price): string
{
    if (empty($userPrice)) { //Если пустое поле
        return 'Введите ставку';
    }

    if ((filter_var($userPrice, FILTER_VALIDATE_INT)) <= 0) { //Если не целое, не положительное число
        return 'Введите целое положительное число';
    }

    if ($userPrice < $price['minBid']) { //Если меньше минимальной ставки
        return 'Должно быть не менее '.$price['minBid'].'';
    }
    return '';
}

/**
 * Добавляет ставку в БД
 *
 * @param mysqli $link Ресурс соединения
 * @param int $lotId Текущий лот
 * @param int $userPrice Цена ставки
 *
 * @return bool Успешность выполнения запроса
 */
function addBid(mysqli $link, int $lotId, int $userPrice): bool
{
    $data = ['user_id' => $_SESSION['user']['id'],
             'lot_id' => $lotId,
             'price' => $userPrice,
             'created_at' => date("Y-m-d H:i:s")
            ];

    $sql = 'INSERT INTO bid
            (user_id,lot_id, price, created_at)
            VALUES (?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($link, $sql, $data);

    return mysqli_stmt_execute($stmt);
}

/**
 * Получить все ставки с неистекшим сроком
 *
 * @param mysqli $link Ресурс соединения
 *
 * @return array|null
 */
function getActiveBid(mysqli $link): array|null
{
    $userId = $_SESSION['user']['id'];

    $sql = 'SELECT  l.id AS lot_id, l.name AS lot_name, l.img_url, l.finished_at,
                    b.user_id, MAX(b.price) AS price, b.created_at,
		            c.name AS cat_name
            FROM bid b
            JOIN lot l ON l.id = b.lot_id
            JOIN user u ON u.id = b.user_id
            JOIN category c ON c.id = l.category_id
            GROUP BY b.lot_id, b.user_id, b.created_at, l.winner_id
            HAVING b.user_id = ? AND finished_at > NOW() AND l.winner_id IS NULL
            ORDER BY created_at DESC';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
 * Получить все ставки c неистекшим сроком
 *
 * @param mysqli $link Ресурс соединения
 *
 * @return array
 */
function getFinishedBid(mysqli $link): array
{
    $userId = $_SESSION['user']['id'];

    $sql = 'SELECT  l.id AS lot_id, l.name AS lot_name, l.img_url, l.finished_at,
                    b.user_id, MAX(b.price) AS price, b.created_at,
		            c.name AS cat_name
            FROM bid b
            JOIN lot l ON l.id = b.lot_id
            JOIN user u ON u.id = b.user_id
            JOIN category c ON c.id = l.category_id
            GROUP BY b.lot_id, b.user_id, l.winner_id, b.created_at
            HAVING b.user_id = ? AND l.winner_id != ? AND l.finished_at < NOW()
            ORDER BY l.finished_at DESC';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
 * Получить все выйгранные ставки текущего пользователя
 *
 * @param mysqli $link Ресурс соединения
 *
 * @return array
 */
function getWinnerBid(mysqli $link): array
{
    $userId = $_SESSION['user']['id'];

    $sql = 'SELECT  l.id AS lot_id, l.name AS lot_name, l.img_url, l.finished_at,
                    b.user_id, MAX(b.price) AS price, b.created_at,
		            c.name AS cat_name,
                    u.contacts
            FROM bid b
            JOIN lot l ON l.id = b.lot_id
            JOIN user u ON u.id = b.user_id
            JOIN category c ON c.id = l.category_id
            GROUP BY b.lot_id, b.user_id, l.winner_id, b.created_at
            HAVING b.user_id = ? AND l.winner_id = ?
            ORDER BY l.finished_at DESC';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
 * Получить ставки с истекшим сроком размещения, у которых нет победителя
 *
 * @param mysqli $link Ресурс соединения
 *
 * @return array
 */
function getLotWithoutWinner(mysqli $link): array
{
    $sql = 'SELECT id AS lot_id, name AS lot_name, winner_id
            FROM lot
            WHERE winner_id IS NULL AND finished_at <= NOW()';
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error: Запрос не выполнен" . mysqli_error($link));
    exit();
}

/**
 * Получить последнюю ставку по текущему лоту
 *
 * @param mysqli $link Ресурс соединения
 * @param int $lotId Текущий лот
 *
 * @return array
 */
function getLastBid(mysqli $link, int $lotId): array
{
    $sql = 'SELECT u.id AS user_id, u.name AS user_name,
                   b.price AS max_price, b.lot_id AS lot_id
            FROM bid b
            JOIN user u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.price DESC LIMIT 1';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lotId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error: Запрос не выполнен" . mysqli_error($link));
    exit();
}

/**
 * Записать победителя в БД
 *
 * @param mysqli $link Ресурс соединения
 * @param int $userId Победитель
 * @param int $lotId Выйгранный лот
 *
 * @return array
 */
function updateWinner(mysqli $link, int $userId, int $lotId): void
{
    $sql = 'UPDATE lot SET winner_id = ? WHERE id = ?';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'ii', $userId, $lotId);
    $result = mysqli_stmt_execute($stmt);
    if (!$result) {
        print("Error: Запрос не выполнен" . mysqli_error($link));
        exit();
    }
}

/**
 * Получить все выйгранные ставки без победителей
 *
 * @param mysqli $link Ресурс соединения
 *
 * @return array
 */
function getWinner(mysqli $link): array
{
    $sql = 'SELECT l.id AS lot_id, l.name AS lot_name, l.winner_id,
                   u.name, u.email
            FROM lot l
            JOIN bid b ON l.winner_id = b.user_id
            JOIN user u ON b.user_id = u.id
            WHERE winner_id IS NOT NULL
            GROUP BY l.id';
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error: Запрос не выполнен" . mysqli_error($link));
    exit();
}

/**
 * Получает разницу во времени в человекочитаемом виде
 *
 * @param array $sqlBid Массив данных о ставках
 *
 * @return array
 */
function getTime(array $sqlBid): array
{
    foreach ($sqlBid as $value => $key) {
        $time = time() - strtotime($sqlBid[$value]['created_at']);
        switch ($time) {
            case ($time < 3600):
                $time = floor($time / 60);
                $minuteWord = get_noun_plural_form($time, 'минута', 'минуты', 'минут');
                $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
                break;
            case ($time < 86400):
                $time = floor($time / 3600);
                $minuteWord = get_noun_plural_form($time, 'час', 'часа', 'часов');
                $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
                break;
            case ($time < 2592000):
                $time = floor($time / 86400);
                $minuteWord = get_noun_plural_form($time, 'день', 'дня', 'дней');
                $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
                break;
            case ($time < 5184000):
                $time = floor($time / 2592000);
                $minuteWord = get_noun_plural_form($time, 'месяц', 'месяца', 'месяцев');
                $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
                break;
            default:
                $time = floor($time / 31104000);
                $minuteWord = get_noun_plural_form($time, 'год', 'года', 'лет');
                $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
        }
    }

    return $sqlBid;
}

/**
 * Получает информацию о лотах по названию категории
 *
 * @param mysqli $link Ресурс соединения
 * @param string $categoryName Название категории
 * @param int $limit Ограничение количества лотов на странице
 * @param int $offset Отступ
 *
 * @return array
 */
function getLotByCategory(mysqli $link, string $categoryName, int $limit, int $offset): array
{
    $sql = 'SELECT l.id AS id, l.name AS lot_name, l.start_price, l.img_url, l.finished_at,
		           c.name AS cat_name, c.symbol_code
            FROM lot l
            JOIN category c ON c.id = l.category_id
            WHERE l.finished_at > NOW() AND c.symbol_code = ?
            ORDER BY l.created_at DESC LIMIT ? OFFSET ?';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'sii', $categoryName, $limit, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    print("Error: Запрос не выполнен" . mysqli_error($link));
    exit();
}

/**
 * Получает количество лотов по названию категории
 *
 * @param mysqli $link Ресурс соединения
 * @param string $categoryName Название категории
 *
 * @return array
 */
function getCountLotByCategory(mysqli $link, string $categoryName): int
{
    $sql = 'SELECT l.id AS id, l.name AS lot_name, l.start_price, l.img_url, l.finished_at,
		           c.name AS cat_name, c.symbol_code
            FROM lot l
            JOIN category c ON c.id = l.category_id
            WHERE l.finished_at > NOW() AND c.symbol_code = ?
            ORDER BY l.created_at DESC';

    $stmt = db_get_prepare_stmt($link, $sql, [$categoryName]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return count(mysqli_fetch_all($result, MYSQLI_ASSOC));
    }
    print("Error: Запрос не выполнен" . mysqli_error($link));
    exit();
}

/**
 * Получает ID пользователя по текущему лоту
 *
 * @param mysqli $link Ресурс соединения
 * @param int $lotId Текущий лот
 *
 * @return array|null
 */
function getLotByLotId(mysqli $link, int $lotId): array|null
{
    $sql = 'SELECT user_id FROM lot WHERE id = ?';

    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lotId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
 * Получает ID пользователя по последней сделанной ставке
 *
 * @param mysqli $link Ресурс соединения
 * @param int $lotId Текущий лот
 *
 * @return array|null
 */
function getlastBidUserById(mysqli $link, int $lotId): array|null
{
    $sql = 'SELECT u.id AS user_id
            FROM bid b
            JOIN user u ON b.user_id = u.id
            WHERE b.lot_id = ?
            ORDER BY b.created_at DESC';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lotId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_assoc($result);
    }
    print("Error MySQL: " . mysqli_error($link));
    exit();
}

/**
 * Проверяем что пользователь авторизован, лот не создан текущим пользователем, последняя ставка сделана не текущим пользователем
 *
 * @param mysqli $link Ресурс соединения
 * @param int $lotId Текущий лот
 * @param string $userName Имя текущий пользователя
 * @param string $userId ID текущего пользователя
 *
 * @return bool
 */
function checkAddLot(mysqli $link, int $lotId, string $userName, string $userId): bool
{
    $userId = (int)($userId);

    $sqlBidUserByUserId = getlastBidUserById($link, $lotId);
    $sqlLotByLotId = getLotByLotId($link, $lotId);

    //Проверяем существует ли последняя ставка
    $lastBid = isset($sqlBidUserByUserId) ? $sqlBidUserByUserId['user_id'] : false;
    //срок размещения лота истёк
    //Проверяем пользователь авторизован, лот не создан текущим пользователем, последняя ставка сделана не текущим пользователем
    return (!empty($userName) && $lastBid !== $userId) && $sqlLotByLotId['user_id'] !== $userId;
}

/**
 * Проверяем есть ли лот участвующий в торгах
 *
 * @param array $sqlPosters Все лоты
 * @param int $lotId Текущий лот
 *
 * @return bool
 */
function checkActiveLot(array $sqlPosters, int $lotId): bool
{
    $lotId = (string)$lotId;
    foreach ($sqlPosters as $value => $key) {
       if ($lotId === $key['id']) {
           return true;
       }
    }
    return false;
}

