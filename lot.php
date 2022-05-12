<?php

require_once 'boot.php';

$sqlCategories = getCategories($link);
$userName = getSessionName();
$userId = getSessionUserId();
$errors = '';
$lotId = (int)$_GET['ID'];
$sqlCatLot = getCatLotMaxPrice($link, $lotId);
$sqlPosters = getPosters($link);
$sqlBidUserByLotId = null;
$price = null;

//Если лота не существует
if (empty($sqlCatLot) || $sqlCatLot ===null) {
    notFoundPage($sqlCategories, $userName);
}

//Если лот участвует в торгах
if ($checkActiveLot = checkActiveLot($sqlPosters, $lotId)) {
    $sqlBidUserByLotId = getBidUser($link, $lotId);
    $price = getPrice($sqlBidUserByLotId, $sqlCatLot);
}
$checkAddLot = checkAddLot($link, $lotId, $userName, $userId);

//Добавление новой ставки
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Проверка, что пользователь залогинен.
    if (empty($userName)) {
        header("Location: login.php");
        exit();
    }
    // Получаем значение из формы и проверяем что значение целое
    $userPrice = trim(filter_input(INPUT_POST, 'cost'));

    $errors = validateFormLot($userPrice, $price);

    if (empty($errors)) {
        if (!$res = addBid($link, $lotId, $userPrice)) {
            print("Error MySQL: " . $error);
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
