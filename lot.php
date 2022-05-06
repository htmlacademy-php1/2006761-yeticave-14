<?php

require_once('boot.php');

$userName = getSessionName();
$lotId = (int)($_GET['ID']);

$sqlCategories = getCategories($link);

$sqlCatLot = getCatLot($link, $lotId);
checkExistDbVal($sqlCatLot);

$sqlBidUser = getBidUser($link, $lotId);

$pageContent = include_template('lot.php', [
    'sqlCategories' => $sqlCategories,
    'sqlCatLot' => $sqlCatLot,
    'sqlBidUser' => $sqlBidUser,
    'userName' => $userName,

]);

$layoutContent = include_template('layout.php', [
    'categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Страница лота',
    'userName' => $userName,
]);

print($layoutContent);
