<?php

require_once 'boot.php';

$sqlCategories = getCategories($link);
$userName = getSessionName();

//Если не авторизован, то доступ запрещен
if (empty($userName)) {
    errorPage($sqlCategories, $userName);
}

$sqlActiveBid = getActiveBid($link); //актуальные ставки
$sqlActiveBid = getTime($sqlActiveBid);


$sqlFinishedBid = getFinishedBid($link); //торги закончены проигрышем
$sqlFinishedBid = getTime($sqlFinishedBid);

$sqlWinnerBid = getWinnerBid($link); //торги закончены победой
$sqlWinnerBid = getTime($sqlWinnerBid);



$pageContent = include_template(
    'my-bets.php',
    ['sqlCategories' => $sqlCategories,
    'sqlActiveBid' => $sqlActiveBid,
    'sqlFinishedBid' => $sqlFinishedBid,
    'sqlWinnerBid' => $sqlWinnerBid, ]
);

$layoutContent = include_template(
    'layout.php',
    ['categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Мои ставки',
    'userName' => $userName, ]
);

print($layoutContent);
