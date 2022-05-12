<?php

const LOT_LIMIT = 9; //Кол-во лотов на странице

require_once 'boot.php';

$sqlCategories = getCategories($link);
$userName = getSessionName();

//Проверяем, что была отправлена форма и получившаяся строка не пустая.
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty(trim($_GET['categoryName']))) {
    $pageContent = include_template(
        'all-lots.php',
        [
        'sqlCategories' => $sqlCategories,
        ]
    );

    $layoutContent = include_template(
        'layout.php',
        [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Лоты по категории',
        'userName' => $userName,
        ]
    );

    print($layoutContent);
    exit();
}

$currentPage = (int)($_GET['page'] ?? 1);

//Смещение для запроса к БД
$offset = LOT_LIMIT * ($currentPage - 1);

$categoryName = $_GET['categoryName'];
$categoryName = mysqli_real_escape_string($link, $categoryName);

$sqlLotCategory = getLotByCategory($link, $categoryName, LOT_LIMIT, $offset);

//Получаем кол-во найденных лотов
$countLotFromSearch = getCountLotByCategory($link, $categoryName);

//Создаем пагинацию
$pagination = createPagination($currentPage, $countLotFromSearch, LOT_LIMIT);

$pageContent = include_template(
    'all-lots.php',
    [
    'sqlCategories' => $sqlCategories,
    'categoryName' => $categoryName,
    'sqlLotCategory' => $sqlLotCategory,
    'pagination' => $pagination,
    ]
);

$layoutContent = include_template(
    'layout.php',
    [
    'categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Лоты по категории',
    'userName' => $userName,
    'sqlCatLot' => $sqlLotCategory,
    ]
);

print($layoutContent);
