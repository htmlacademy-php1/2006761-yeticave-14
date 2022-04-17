<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');
require_once('init.php');

$lot_id = intval($_GET['ID']);

if (!$link) {
    $error = mysqli_connect_error();
    print('Error MySQL: ' . $error);
} else {
    mysqli_set_charset($link, "utf8");
    $sqlCategories = 'SELECT * FROM category';
    $result = mysqli_query($link, $sqlCategories);
    if ($result) {
        $ArrCategories = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print('Error MySQL: ' . $error);
    }

    $sqlCatLot = 'SELECT l.name as lot_name, l.description, l.img_url, l.finished_at, l.start_price, l.step_price,
                  c.name as cat_name,
                  MAX(b.price) as max_price
                  FROM lot AS l
                  JOIN category AS c
                  ON l.category_id = c.id
                  LEFT JOIN bid AS b
                  ON b.lot_id = l.id
                  WHERE
                  l.finished_at > NOW() AND l.id = '.$lot_id.'
                  GROUP BY b.lot_id';
    $result = mysqli_query($link, $sqlCatLot);
    if ($result) {
        $ArrCatLot = mysqli_fetch_assoc($result);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
    }
    checkExistDbVal($ArrCatLot);

    $sqlBidUser = 'SELECT b.price, b.created_at, u.name AS user_name FROM bid AS b
                JOIN user AS u ON b.user_id = u.id
                WHERE b.lot_id = ' .$lot_id;
    $result = mysqli_query($link, $sqlBidUser);
    if ($result) {
        $ArrBidUser = mysqli_fetch_all($result, MYSQLI_ASSOC);
    } else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
    }

    $pageContent = include_template('lot.php', [
        'ArrCategories' => $ArrCategories,
        'ArrCatLot' => $ArrCatLot,
        'ArrBidUser' => $ArrBidUser,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $ArrCategories,
        'content' => $pageContent,
        'title' => $title,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
    ]);

    print($layoutContent);
}
