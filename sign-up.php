<?php

require_once 'boot.php';

$sqlCategories = getCategories($link);
$userName = getSessionName();

if (!empty($userName)) {
    errorPage($sqlCategories, $userName);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Получаем значения из формы
    $registration = filter_input_array(
        INPUT_POST,
        ['email' => FILTER_DEFAULT,
        'password' => FILTER_DEFAULT,
        'name' => FILTER_DEFAULT,
        'contacts' => FILTER_DEFAULT],
        true
    );

    $errors = validateFormSignUp($link, $registration);

    //Удаляем все null значения
    $errors = array_filter($errors);

    if (empty($errors)) {
        $registration['password'] = password_hash($registration['password'], PASSWORD_DEFAULT);
        $result = addUser($link, $registration);
        if ($result) {
            header("Location: login.php");
        } else {
            notFoundPage($sqlCategories, $userName);
        }
    }

    $pageContent = include_template(
        'sign-up.php',
        ['sqlCategories' => $sqlCategories,
        'errors' => $errors, ]
    );

    $layoutContent = include_template(
        'layout.php',
        ['categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Страница регистрации', ]
    );

    print($layoutContent);
} else {
    $pageContent = include_template(
        'sign-up.php',
        ['sqlCategories' => $sqlCategories, ]
    );

    $layoutContent = include_template(
        'layout.php',
        ['categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Страница регистрации', ]
    );

    print($layoutContent);
}
