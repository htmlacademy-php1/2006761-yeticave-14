<?php

require_once('boot.php');
require_once('getwinner.php');

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
