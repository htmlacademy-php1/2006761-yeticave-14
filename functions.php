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

    return $isLessOneHour ? "timer--finishing" : "";
}

function checkExistDbVal(array $checkingItem) {
  if (empty($checkingItem)) {
    $error = include_template('404.php', [
    ]);
    print($error);
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

function getCatLot(mysqli $link, int $lotId): array {

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
        $arr = mysqli_fetch_assoc($result);
        if ($arr===NULL) {
            $error = include_template('404.php', [
            ]);
            print($error);
            die();
        }
        return $arr;
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
?>
