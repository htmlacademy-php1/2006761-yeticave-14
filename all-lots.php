<?php

const LOT_LIMIT = 9; //Кол-во лотов на странице

require_once('boot.php');

$sqlCategories = getCategories($link);
$userName = getSessionName();

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty(trim($_GET['categoryName']))) { //Проверяем, что была отправлена форма и получившаяся строка не пустая.

    $pageContent = include_template('all-lots.php', [
        'sqlCategories' => $sqlCategories,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Лоты по категории',
        'userName' => $userName,
    ]);

    print($layoutContent);
    exit();
}

$currentPage = (int)($_GET['page'] ?? 1);

$offset = LOT_LIMIT * ($currentPage - 1); //Смещение для запроса к БД

$categoryName = $_GET['categoryName'];
$categoryName = mysqli_real_escape_string($link, $categoryName);

$sqlLotCategory = getLotByCategory($link, $categoryName, LOT_LIMIT, $offset);

$countLotFromSearch = getCountLotByCategory($link, $categoryName); //Получаем кол-во найденных лотов

$pagination = createPagination($currentPage, $countLotFromSearch, LOT_LIMIT); //Создаем пагинацию

$pageContent = include_template('all-lots.php', [
    'sqlCategories' => $sqlCategories,
    'categoryName' => $categoryName,
    'sqlLotCategory' => $sqlLotCategory,
    'pagination' => $pagination,
]);

$layoutContent = include_template('layout.php', [
    'categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Лоты по категории',
    'userName' => $userName,
    'sqlCatLot' => $sqlLotCategory,
]);

print($layoutContent);
