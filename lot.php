<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');
require_once('init.php');

$lotId = intval($_GET['ID']);

$sqlCategories = getCategories($link);

$sqlCatLot = getCatLot($link, $lotId);
checkExistDbVal($sqlCatLot);

$sqlBidUser = getBidUser($link, $lotId);

$pageContent = include_template('lot.php', [
    'sqlCategories' => $sqlCategories,
    'sqlCatLot' => $sqlCatLot,
    'sqlBidUser' => $sqlBidUser,
]);

$layoutContent = include_template('layout.php', [
    'categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => $title,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
]);

print($layoutContent);
