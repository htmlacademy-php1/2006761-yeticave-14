<?php

require_once('boot.php');

$sqlLotList = getLotWithoutWinner($link);

foreach ($sqlLotList as $value) {
    $sqlLastBid = getLastBid($link, $value['lot_id']);
}
if (!empty($sqlLastBid)) {
    updateWinner($link, $sqlLastBid['user_id'], $sqlLastBid['lot_id']);
}

    $sqlCategories = getCategories($link);

    $sqlPosters = getPosters($link);

    $userName = getSessionName();

    $pageContent = include_template('main.php', [
        'categories' => $sqlCategories,
        'posters' => $sqlPosters,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Главная страница',
        'userName' => $userName,
    ]);

    print($layoutContent);
