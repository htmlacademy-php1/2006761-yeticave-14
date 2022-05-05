<?php

require_once('boot.php');

$sqlCategories = getCategories($link);
$sqlPosters = getPosters($link);
$userName = getSessionName();

if ($_SERVER['REQUEST_METHOD'] !== 'GET' || empty(trim($_GET['search']))) { //Проверяем, что была отправлена форма и получившаяся строка не пустая.

    $search = '';

    $pageContent = include_template('search.php', [
        'sqlCategories' => $sqlCategories,
        'search' => $search,

    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Страница поиска',
        'userName' => $userName,
    ]);

    print($layoutContent);
    exit();
}

$currentPage = (int)($_GET['page'] ?? 1);

const LOT_LIMIT = 9; //Кол-во лотов на странице
$offset = LOT_LIMIT * ($currentPage - 1); //Смещение для запроса к БД

$search = trim(filter_input(INPUT_GET, 'search')); //Получаем значение поиска от пользователя
$search = mysqli_real_escape_string($link, $search);

if (empty($searchResult = getLotBySearch($link, $search, LOT_LIMIT, $offset))) {
    $search = 'Ничего не найдено';
}

$countLotFromSearch = getCountLotBySearch($link, $search); //Получаем кол-во найденных лотов

$pagination = createPagination($currentPage, $countLotFromSearch, LOT_LIMIT); //Создаем пагинацию

$pageContent = include_template('search.php', [
    'sqlCategories' => $sqlCategories,
    'posters' => $sqlPosters,
    'search' => $search,
    'searchResult'=> $searchResult,
    'pagination' => $pagination,
]);

$layoutContent = include_template('layout.php', [
    'categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Страница поиска',
    'userName' => $userName,
]);

print($layoutContent);
