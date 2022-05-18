<?php

require_once 'boot.php';

$sqlCategories = getCategories($link);
$userName = getSessionName();
$errors = '';
$lotId = (int)$_GET['ID'];
$sqlCatLot = getCatLotMaxPrice($link, $lotId);
$sqlPosters = getPosters($link);

//Если лота не существует
if (empty($sqlCatLot) || $sqlCatLot === null) {
    notFoundPage($sqlCategories, $userName);
}

$checkActiveLot = checkActiveLot($sqlPosters, $lotId);
$sqlBidUserByLotId = getBidUser($link, $lotId);
$sqlBidUserByLotId = getTime($sqlBidUserByLotId);
$price = getPrice($sqlBidUserByLotId, $sqlCatLot);

$checkAddLot = checkAddLot($link, $lotId, $userName);

//Добавление новой ставки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Проверка, что пользователь залогинен.
    if (empty($userName)) {
        header("Location: login.php");
        exit();
    }
    // Получаем значение из формы и проверяем что значение целое

    $userPrice = (int)trim(filter_input(INPUT_POST, 'cost', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    $errors = validateFormLot($userPrice, $price);

    if (empty($errors)) {
        if (!addBid($link, $lotId, $userPrice)) {
            print("Error MySQL: " . mysqli_error($link));
            exit;
        }
        header("Location: /lot.php?ID={$lotId}");
    }
}

$pageContent = include_template(
    'lot.php',
    [
        'sqlCategories' => $sqlCategories,
        'sqlCatLot' => $sqlCatLot,
        'sqlBidUser' => $sqlBidUserByLotId,
        'userName' => $userName,
        'lotId' => $lotId,
        'price' => $price,
        'errors' => $errors,
        'checkAddLot' => $checkAddLot,
        'checkActiveLot' => $checkActiveLot,
    ]
);

$layoutContent = include_template(
    'layout.php',
    [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Страница лота',
        'userName' => $userName,
        'sqlCatLot' => $sqlCatLot,
    ]
);

print($layoutContent);
