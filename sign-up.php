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
        [
            'email' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'password' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'name' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
            'contacts' => FILTER_SANITIZE_FULL_SPECIAL_CHARS
        ],
        true
    );

    $errors = validateFormSignUp($link, $registration);

    //Удаляем все null значения
    $errors = array_filter($errors);

    if (empty($errors)) {
        $registration['password'] = (password_hash($registration['password'], PASSWORD_DEFAULT));
        $result = addUser($link, $registration);
        if ($result) {
            header("Location: login.php");
        } else {
            notFoundPage($sqlCategories, $userName);
        }
    }

    $pageContent = include_template(
        'sign-up.php',
        [
            'sqlCategories' => $sqlCategories,
            'errors' => $errors,
        ]
    );

    $layoutContent = include_template(
        'layout.php',
        [
            'categories' => $sqlCategories,
            'content' => $pageContent,
            'title' => 'Страница регистрации',
        ]
    );

    print($layoutContent);
} else {
    $pageContent = include_template(
        'sign-up.php',
        [
            'sqlCategories' => $sqlCategories,
        ]
    );

    $layoutContent = include_template(
        'layout.php',
        [
            'categories' => $sqlCategories,
            'content' => $pageContent,
            'title' => 'Страница регистрации',
        ]
    );

    print($layoutContent);
}
