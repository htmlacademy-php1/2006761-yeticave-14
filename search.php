<?php

const LOT_LIMIT = 9; //Кол-во лотов на странице

require_once 'boot.php';

$sqlCategories = getCategories($link);
$userName = getSessionName();

//Проверяем, что была отправлена форма и получившаяся строка не пустая.
if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty(trim($_GET['search']))) {
    $search = '';

    $pageContent = include_template(
        'search.php',
        ['sqlCategories' => $sqlCategories,
        'search' => $search, ]
    );

    $layoutContent = include_template(
        'layout.php',
        ['categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Страница поиска',
        'userName' => $userName, ]
    );

    print($layoutContent);
    exit();
}

$currentPage = (int)($_GET['page'] ?? 1);

//Смещение для запроса к БД
$offset = LOT_LIMIT * ($currentPage - 1);

//Получаем значение поиска от пользователя
$search = trim(filter_input(INPUT_GET, 'search'));
$search = mysqli_real_escape_string($link, $search);

if (empty($searchResult = getLotBySearch($link, $search, LOT_LIMIT, $offset))) {
    $search = 'Ничего не найдено';
}

//Получаем кол-во найденных лотов
$countLotFromSearch = getCountLotBySearch($link, $search);

//Создаем пагинацию
$pagination = createPagination($currentPage, $countLotFromSearch, LOT_LIMIT);
$pageContent = include_template(
    'search.php',
    ['sqlCategories' => $sqlCategories,
    'search' => $search,
    'searchResult'=> $searchResult,
    'pagination' => $pagination, ]
);

$layoutContent = include_template(
    'layout.php',
    ['categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Страница поиска',
    'userName' => $userName, ]
);

print($layoutContent);
