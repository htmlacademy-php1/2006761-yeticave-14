<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');
require_once('init.php');

    $sqlCategories = getCategories($link);

    $sqlPosters = getPosters($link);

    $pageContent = include_template('main.php', [
        'categories' => $sqlCategories,
        'posters' => $sqlPosters,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => $title,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
    ]);

    print($layoutContent);
