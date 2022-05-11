<?php

require_once 'boot.php';

$sqlCategories = getCategories($link);
$userName = getSessionName();
if (empty($userName)) {
    errorPage($sqlCategories, $userName);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //Получаем значения из формы
    $lot = filter_input_array(
        INPUT_POST,
        ['name' => FILTER_DEFAULT,
        'category_id' => FILTER_DEFAULT,
        'description' => FILTER_DEFAULT,
        'start_price' => FILTER_DEFAULT,
        'step_price' => FILTER_DEFAULT,
        'finished_at' => FILTER_DEFAULT ],
        true
    );

    $categoriesId = array_column($sqlCategories, 'id');
    $errors = validateFormAdd($lot, $categoriesId, $_FILES);

    //Удаляем все null значения
    $errors = array_filter($errors);

    if (empty($errors)) {
        if (addLot($link, $lot, $_FILES)) {
            $lotId = mysqli_insert_id($link);
            header("Location: lot.php?ID=" . $lotId);
        } else {
            print("Error MySQL: " . mysqli_error($link));
            exit();
        }
    }

    $pageContent = include_template(
        'add.php',
        ['sqlCategories' => $sqlCategories,
        'errors' => $errors, ]
    );

    $layoutContent = include_template(
        'layout.php',
        ['categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Добавление лота',
        'userName' => $userName, ]
    );

    print($layoutContent);
} else {
    $pageContent = include_template(
        'add.php',
        ['sqlCategories' => $sqlCategories, ]
    );

    $layoutContent = include_template(
        'layout.php',
        ['categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Добавление лота',
        'userName' => $userName, ]
    );

    print($layoutContent);
}
