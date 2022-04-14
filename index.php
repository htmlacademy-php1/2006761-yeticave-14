<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');
require_once('init.php');

if (!$link) {
    $error = mysqli_connect_error();
    print("Îøèáêà MySQL: " . $error);
}
else {
    $sql_category = 'SELECT * FROM category';
    $result = mysqli_query($link, $sql_category);
    if ($result) {
        $category = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    else {
        $error = mysqli_error($link);
        print("Îøèáêà MySQL: " . $error);
        }

    $sql_posters = 'SELECT l.name AS lot_name, start_price, img_url, finished_at, c.name AS cat_name FROM lot AS l
                    JOIN category AS c ON c.id = l.category_id';
    $result = mysqli_query($link, $sql_posters);
    if ($result) {
        $lot = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    else {
        $error = mysqli_error($link);
        print("Îøèáêà MySQL: " . $error);
    }

    $page_content = include_template('main.php', [
        'category' => $category,
        'posters' => $lot,
    ]);

    $layout_content = include_template('layout.php', [
        'category' => $category,
        'content' => $page_content,
        'title' => $title,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
    ]);

    print($layout_content);
}


