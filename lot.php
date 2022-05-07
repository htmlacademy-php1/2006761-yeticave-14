<?php

require_once('boot.php');

$sqlCategories = getCategories($link);
$userName = getSessionName();
$errors = '';

$lotId = (int)$_GET['ID'];

$sqlCatLot = getCatLotMaxPrice($link, $lotId);
if (checkExistDbVal($sqlCatLot)) {
    $pageContent = include_template('404.php', [
    'sqlCategories' => $sqlCategories,
    'userName' => $userName,
    ]);
    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Страница не найдена',
        'userName' => $userName,
    ]);

    print($layoutContent);
    exit();
}

$sqlBidUser = getBidUser($link, $lotId);

$price = getPrice($sqlBidUser, $sqlCatLot);

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    if (empty($userName)) {     //Проверка, что пользователь залогинен.
        header("Location: login.php");
        exit();
    }
    $userPrice = trim(filter_input(INPUT_POST, 'cost')); // Получаем значение из формы и проверяем что значение целое

    $errors = validateFormLot($userPrice, $price);

    if(empty($errors)) {
       if (!$res = addBid($link, $lotId, $userPrice)) {
           print("Error MySQL: " . $error);
           exit;
       }
       header("Location: /lot.php?ID=".$lotId."");
    }
}
$pageContent = include_template('lot.php', [
    'sqlCategories' => $sqlCategories,
    'sqlCatLot' => $sqlCatLot,
    'sqlBidUser' => $sqlBidUser,
    'userName' => $userName,
    'lotId' => $lotId,
    'price' => $price,
    'errors' => $errors,

]);

$layoutContent = include_template('layout.php', [
    'categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Страница лота',
    'userName' => $userName,
]);

print($layoutContent);
