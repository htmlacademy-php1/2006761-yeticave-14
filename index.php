<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');
require_once('init.php');

if (!$link) {
    $error = mysqli_connect_error();
    print('Error MySQL: ' . $error);
} else {
    mysqli_set_charset($link, "utf8");
    $sqlCategories = 'SELECT * FROM category';
    $result = mysqli_query($link, $sqlCategories);
    if ($result) {
        $categories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print('Error MySQL: ' . $error);
           }

    $sqlPosters = 'SELECT l.id AS id, l.name AS lot_name, start_price, img_url, finished_at, c.name AS cat_name FROM lot AS l
                   JOIN category AS c ON c.id = l.category_id
                   WHERE l.finished_at > NOW()
                   ORDER BY l.created_at DESC';
    $result = mysqli_query($link, $sqlPosters);
    if ($result) {
        $lots = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
    }

    $pageContent = include_template('main.php', [
        'categories' => $categories,
        'posters' => $lots,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $categories,
        'content' => $pageContent,
        'title' => $title,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
    ]);

    print($layoutContent);
}
