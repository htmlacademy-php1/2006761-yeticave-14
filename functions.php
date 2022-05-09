<?php

function priceModify(int $price): string {
    ceil($price);
    if ($price > 1000) {
        $price = number_format($price, 0, '', ' ');
    }
    return (string) $price . ' ₽';
}

function formatTimer(string $date): string {
    $dateFinish = strtotime($date);
    $secDifference = $dateFinish - time();
    $hours = floor($secDifference / 3600);
    $minutes = floor(($secDifference % 3600) / 60);

    return "{$hours}:{$minutes}";
}

function oneHourTimerFinishing(string $date): string {
    $dateFinish = strtotime($date);
    $secDifference = $dateFinish - time();
    $oneHour = 60 * 60;
    $isLessOneHour = $secDifference <= $oneHour;

    return $isLessOneHour ? 'timer--finishing' : '';
}
//Проверяет наличие значения из БД
function checkExistDbVal(mixed $checkingItem): bool {
  return empty($checkingItem) || $checkingItem ===null;
}

function minPrice(int $curPrice, int $stepPrice): int {
    $minPrice = $curPrice + $stepPrice;
    return $curPrice;
}

function getCategories(mysqli $link): array {

    $sql = 'SELECT * FROM category';
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print('Error MySQL: ' . $error);
    }
}
 
function getPosters(mysqli $link): array {
    $sql = 'SELECT l.id AS id, l.name AS lot_name, start_price, img_url, finished_at, c.name AS cat_name FROM lot AS l
            JOIN category AS c ON c.id = l.category_id
            WHERE l.finished_at > NOW()
            ORDER BY l.created_at DESC';
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
    }
}

function getCatLotMaxPrice(mysqli $link, int $lotId): mixed {

    $sql = 'SELECT l.name as lot_name, l.description, l.img_url, l.finished_at, l.start_price, l.step_price,
                   c.name as cat_name,
                   b.price
            FROM lot AS l
            JOIN category AS c ON l.category_id = c.id
            LEFT JOIN bid AS b ON b.lot_id = l.id
            WHERE l.finished_at > NOW() AND l.id = ?
            ORDER BY price DESC';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lotId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_assoc($result);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
    }
}

function getBidUser(mysqli $link, int $lotId): array {

    $sql = 'SELECT b.lot_id, b.price, b.created_at, u.name AS user_name FROM bid AS b
            JOIN user AS u ON b.user_id = u.id
            WHERE b.lot_id = ?';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lotId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
    }
}

function getPostVal(mixed $val): ?string {
    return $_POST[$val] ?? '';
}

function addLot(mysqli $link, array $lot, array $files): bool {
    $lot['finished_at'] = date("Y-m-d H:i:s", strtotime($lot['finished_at']));
    $lot['img_url'] = uploadFile($files);
    $lot['user_id'] = $_SESSION['user']['id'];

    $sql = 'INSERT INTO lot
            (name, category_id, description, start_price, step_price, finished_at, img_url, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($link, $sql, $lot);
   
    return mysqli_stmt_execute($stmt);
}

function uploadFile(array $files): string {
    $tmpName = $files['img_url']['tmp_name'];
    $fileType = mime_content_type($tmpName);

    switch($fileType) {
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

function validateFormAdd(array $lot, array $categoriesId, $files): array {
    $requiredFields = ['name', 'category_id', 'description', 'start_price', 'step_price', 'finished_at'];

    $rules = [
        'category_id' => function ($id) use ($categoriesId) {
            return validateCategory($id, $categoriesId);
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
        if (in_array($key, $requiredFields) and empty($value)) {
            $errors[$key] = 'Поле надо заполнить';
        }
    }

    $errors['img_url'] = validateImg($files);

    return $errors;
}

function validateCategory(string $id, array $allowed_list): ?string {
    if (!in_array($id, $allowed_list)) {
        return 'Указана несуществующая категория';
    }
    return null;
}

function validateValue(string $value): ?string {
    $value = intval($value);
    if ($value <= 0) {
        return 'Значение должно быть больше нуля';
    }
    return null;
}

function validateFinishedAt(string $finishedAt): ?string {
    if (!is_date_valid($finishedAt)) {
        return 'Значение должно быть датой в формате «ГГГГ-ММ-ДД»';
    }
    $time = getDateRange($finishedAt, 'now');
    if ($time[0] < 24) {
        return 'Дата должна быть больше текущей даты, хотя бы на один день.';
    }
    return null;
}

function getDateRange(string $finishedAt, string $now): array {
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

function validateImg(array $files): string {

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

function addUser(mysqli $link, array $registration): bool {
    $sql = 'INSERT INTO user (email, password, name, contacts) VALUES (?, ?, ?, ?)';
    $stmt = db_get_prepare_stmt($link, $sql, $registration);
    return mysqli_stmt_execute($stmt);
}

function validateEmail(mysqli $link, array $registration): string {
    $email = mysqli_real_escape_string($link, $registration['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         return 'Некорректный e-mail';
    }

    $sql = "SELECT email FROM user WHERE email = '" . $email . "'";
    $result = mysqli_query($link, $sql);
    if ($result) {
        $result = mysqli_fetch_all($result, MYSQLI_ASSOC);
        return !empty($result) ? 'Данный e-mail занят' : '';
    } else {
        print("Error MySQL: " . mysqli_error($link));
        die();
    }
}

function validateFormSignUp(mysqli $link, array $registration): array {
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

function getUserDb(mysqli $link, string $email): array {
	$sql = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

    return $result && !empty($user) ? $user : [];
}

function validateFormLogin(mysqli $link, array $login, mixed $user): array {
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

function getSessionName(): string {
    return $_SESSION['user']['name'] ?? '';
}

function errorPage(array $sqlCategories, string $userName): void{
    $pageContent = include_template('403.php', ['sqlCategories' => $sqlCategories,]);

    $layoutContent = include_template('layout.php', [
        'content' => $pageContent,
        'categories' => $sqlCategories,
        'title' => 'Доступ запрещен',
        'userName' => $userName,
    ]);

    print($layoutContent);
    exit();
}

function notFoundPage(array $sqlCategories, string $userName): void{
    $pageContent = include_template('404.php', ['sqlCategories' => $sqlCategories,]);

    $layoutContent = include_template('layout.php', [
        'content' => $pageContent,
        'categories' => $sqlCategories,
        'title' => 'Страница не найдена',
        'userName' => $userName,
    ]);

    print($layoutContent);
    exit();
}

function checkPassword(array $login, array $user): bool {
    return password_verify($login['password'], $user['password']);
}

function getLotBySearch(mysqli $link, string $search, int $limit, int $offset): array {
    $sql = "SELECT l.id, l.name AS lot_name, l.description, l.start_price, l.img_url, l.finished_at, c.name AS cat_name
            FROM lot l
            JOIN category c ON l.category_id = c.id
            WHERE  MATCH(l.name, l.description) AGAINST(? IN BOOLEAN MODE) AND l.finished_at > NOW() ORDER BY l.created_at LIMIT ".$limit." OFFSET ".$offset."";

    $stmt = db_get_prepare_stmt($link, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

function getCountLotBySearch(mysqli $link, string $search): int {

    $sql = 'SELECT l.id, l.name AS lot_name, l.description, l.start_price, l.img_url, l.finished_at, c.name AS cat_name
            FROM lot l
            JOIN category c ON l.category_id = c.id
            WHERE  MATCH(l.name, l.description) AGAINST(? IN BOOLEAN MODE)';

    $stmt = db_get_prepare_stmt($link, $sql, [$search]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return count(mysqli_fetch_all($result, MYSQLI_ASSOC));
}

function createPagination(int $current, int $countLot, int $limit): array {
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

function getPrice(array $sqlBidUser, array $sqlCatLot): array {
   $currentPrice = empty($sqlBidUser) ? $sqlCatLot['start_price'] : $sqlCatLot['price'];
   $minBid = $currentPrice + $sqlCatLot['step_price'];

   return ['currentPrice' => $currentPrice,
           'minBid' => $minBid
          ];
}

function validateFormLot(string $userPrice, array $price): string {

    if (empty($userPrice)) { //Если пустое поле
        return 'Введите ставку';
    }

    if ((filter_var($userPrice, FILTER_VALIDATE_INT)) <= 0) { //Если не целое, не положительное число
      return 'Введите целое положительное число';
    }

    if ($userPrice <= $price['minBid']) { //Если меньше минимальной ставки
        return "Должно быть не менее ".$price['minBid']."";
    }
    return '';
}

function addBid(mysqli $link, int $lotId, int $userPrice): bool {

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

function getActiveBid(mysqli $link): array|null {
    $userId = $_SESSION['user']['id'];

    $sql = "SELECT  l.id AS lot_id, l.name AS lot_name, l.img_url, l.finished_at,
                    b.user_id, MAX(b.price) AS price, b.created_at,
		            c.name AS cat_name
            FROM bid b
            JOIN lot l ON l.id = b.lot_id
            JOIN user u ON u.id = b.user_id
            JOIN category c ON c.id = l.category_id
            GROUP BY b.lot_id, b.user_id, b.created_at, l.winner_id
            HAVING b.user_id = ".$userId." AND finished_at > NOW() AND l.winner_id IS NULL
            ORDER BY created_at DESC";
    $result = mysqli_query($link, $sql);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
        exit();
    }
}

function getFinishedBid(mysqli $link): array {
    $userId = $_SESSION['user']['id'];

    $sql = "SELECT  l.id AS lot_id, l.name AS lot_name, l.img_url, l.finished_at,
                    b.user_id, MAX(b.price) AS price, b.created_at,
		            c.name AS cat_name
            FROM bid b
            JOIN lot l ON l.id = b.lot_id
            JOIN user u ON u.id = b.user_id
            JOIN category c ON c.id = l.category_id
            GROUP BY b.lot_id, b.user_id, l.winner_id, b.created_at
            HAVING b.user_id = ".$userId." AND l.winner_id != ".$userId."
            ORDER BY l.finished_at DESC";
    $result = mysqli_query($link, $sql);

    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
        exit();
    }
}

function getWinnerBid(mysqli $link): array {
    $userId = $_SESSION['user']['id'];

    $sql = "SELECT  l.id AS lot_id, l.name AS lot_name, l.img_url, l.finished_at,
                    b.user_id, MAX(b.price) AS price, b.created_at,
		            c.name AS cat_name,
                    u.contacts
            FROM bid b
            JOIN lot l ON l.id = b.lot_id
            JOIN user u ON u.id = b.user_id
            JOIN category c ON c.id = l.category_id
            GROUP BY b.lot_id, b.user_id, l.winner_id, b.created_at
            HAVING b.user_id = ".$userId." AND l.winner_id = ".$userId."
            ORDER BY l.finished_at DESC";
    $result = mysqli_query($link, $sql);

    if ($result) {
         return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
        exit();
    }
}

function getLotWithoutWinner(mysqli $link): array {
    $sql = 'SELECT id AS lot_id, name AS lot_name, winner_id
            FROM lot
            WHERE winner_id IS NULL AND finished_at <= NOW()';
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        print("Error: Запрос не выполнен" . mysqli_error($link));
        exit();
    }
}

function getLastBid(mysqli $link, int $lotId): array|bool|null {
    $sql = "SELECT u.id as user_id, u.name as user_name, u.email,
       b.price as max_price, b.lot_id as lot_id, l.name
            FROM bid b
            JOIN lot l ON b.lot_id = l.id
            JOIN user u ON b.user_id = u.id WHERE b.lot_id = ".$lotId." ORDER BY b.price DESC LIMIT 1";
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        print("Error: Запрос не выполнен" . mysqli_error($link));
        exit();
    }
}

function updateWinner(mysqli $link, int $userId, int $lotId): mysqli_result|bool {
    $sql = "UPDATE lot SET winner_id = ".$userId." WHERE id =".$lotId."";
    return mysqli_query($link, $sql);
}

function getWinner(mysqli $link): bool|array {
    $sql = "SELECT l.id AS lot_id, l.name AS lot_name, l.winner_id, u.name, u.email
            FROM lot l
            JOIN bid b ON l.winner_id = b.user_id
            JOIN user u ON b.user_id = u.id
            WHERE winner_id IS NOT NULL
            GROUP BY l.id";
    $result = mysqli_query($link, $sql);
    if ($result) {
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        print("Error: Запрос не выполнен" . mysqli_error($link));
        exit();
    }
}

function getTime(array $sqlBid): array {

    foreach ($sqlBid as $value => $key) {
        $time = time() - strtotime($sqlBid[$value]['created_at']);
        if ($time < 3600 ) {
            $time = floor($time / 60);
            $minuteWord = get_noun_plural_form($time, 'минута', 'минуты', 'минут');
            $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
        } elseif ($time < 86400) {
            $time = floor($time / 3600);
            $minuteWord = get_noun_plural_form($time, 'час', 'часа', 'часов');
            $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
        } elseif ($time < 2592000) {
            $time = floor($time / 86400);
            $minuteWord = get_noun_plural_form($time, 'день', 'дня', 'дней');
            $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
        } elseif ($time < 5184000){
            $time = floor($time / 2592000);
            $minuteWord = get_noun_plural_form($time, 'месяц', 'месяца', 'месяцев');
            $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
        } else {
            $time = floor($time / 31104000);
            $minuteWord = get_noun_plural_form($time, 'год', 'года', 'лет');
            $sqlBid[$value]['time'] = "{$time} {$minuteWord} назад";
        }
    }

   return $sqlBid;
}

?>
