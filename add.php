<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');
require_once('init.php');

$sqlCategories = getCategories($link);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Получаем значения из формы
    $lot = filter_input_array(INPUT_POST, [
        'name' => FILTER_DEFAULT,
        'category_id' => FILTER_DEFAULT,
        'description' => FILTER_DEFAULT,
        'start_price' => FILTER_DEFAULT,
        'step_price' => FILTER_DEFAULT,
        'finished_at' => FILTER_DEFAULT
    ], true);

    $categoriesId = array_column($sqlCategories, 'id');
    $errors = validateFormLot($lot, $categoriesId, $_FILES);

    //Удаляем все null значения
    $errors = array_filter($errors);

    if (empty($errors)) {
        $result = addLot($link, $lot, $_FILES);
        if ($result) {
            $lotId = mysqli_insert_id($link);
            header("Location: lot.php?ID=" . $lotId);
        } else {
            print(include_template('404.php', [
            ]));
            exit();
        }
    }

    $pageContent = include_template('add.php', [
        'sqlCategories' => $sqlCategories,
        'errors' => $errors,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => $title,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
    ]);

    print($layoutContent);

} else {
    $pageContent = include_template('add.php', [
        'sqlCategories' => $sqlCategories,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => $title,
        'user_name' => $user_name,
        'is_auth' => $is_auth,
    ]);

    print($layoutContent);
}


