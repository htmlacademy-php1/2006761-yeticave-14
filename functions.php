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
function checkExistDbVal(mixed $checkingItem): void {
  if (empty($checkingItem) || $checkingItem ===null) {
    print(include_template('404.php', [
    ]));
    die();
  }
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

function getCatLot(mysqli $link, int $lotId): mixed {

    $sql = 'SELECT l.name as lot_name, l.description, l.img_url, l.finished_at, l.start_price, l.step_price,
                  c.name as cat_name,
                  b.price as max_price
                  FROM lot AS l
                  JOIN category AS c ON l.category_id = c.id
                  LEFT JOIN bid AS b ON b.lot_id = l.id
                  WHERE l.finished_at > NOW() AND l.id = ?
                  ORDER BY max_price DESC LIMIT 1';
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

function getBidUser(mysqli $link, int $lot_id): array {

    $sql = 'SELECT b.price, b.created_at, u.name AS user_name FROM bid AS b
            JOIN user AS u ON b.user_id = u.id
            WHERE b.lot_id = ?';
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $lot_id);
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

function addLot(mysqli $link, array $lot): bool {
    $lot['finished_at'] = date("Y-m-d H:i:s", strtotime($lot['finished_at']));
    $lot['img_url'] = uploadFile();
    $lot['user_id'] = $_SESSION['user']['id'];

    $sql = 'INSERT INTO lot
            (name, category_id, description, start_price, step_price, finished_at, img_url, user_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)';

    $stmt = db_get_prepare_stmt($link, $sql, $lot);
   
    return mysqli_stmt_execute($stmt);
}

function uploadFile(): string {
    $tmpName = $_FILES['img_url']['tmp_name'];
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

function validateFormLot(array $lot, array $categoriesId): array {
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

    $errors['img_url'] = validateImg();

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

function validateImg(): string {

    if (empty($_FILES['img_url']['name'])) {
        return 'Загрузите картинку';
    }

    $tmpName = $_FILES['img_url']['tmp_name'];
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

function getUserDb(mysqli $link, string $email): mixed {
	$sql = "SELECT * FROM user WHERE email = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

    return $result && !empty($user) ? $user : null;
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

function checkSessionName(): string {
    return !empty($_SESSION) ? $_SESSION['user']['name'] : '';
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

function checkPassword(array $login, array $user): string {
    if (password_verify($login['password'], $user['password'])) {
			$_SESSION['user'] = $user;
            header('Location: /');
            exit();
	} 
	return 'Вы ввели неверный пароль';
}
?>
