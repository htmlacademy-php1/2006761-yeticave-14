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

    $sqlCatLot = 'SELECT l.name AS lot_name, start_price, step_price, description, img_url, finished_at, c.name AS cat_name FROM lot AS l
                   JOIN category AS c ON c.id = l.category_id
                   WHERE l.id = ' .$lot_id;
    $result = mysqli_query($link, $sqlCatLot);
    if ($result) {
        $ArrCatLot = mysqli_fetch_assoc($result);
    }
    else {
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
    }
    else {
        $error = mysqli_error($link);
        print("Error MySQL: " . $error);
    }

    $minPrice = $ArrCatLot['start_price'] + $ArrCatLot['step_price'];

    $pageContent = include_template('lot.php', [
        'ArrCategories' => $ArrCategories,
        'ArrCatLot' => $ArrCatLot,
        'ArrBidUser' => $ArrBidUser,
        'minPrice' => $minPrice,
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
