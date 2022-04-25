<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');
require_once('init.php');

$sqlCategories = getCategories($link);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    //Получаем значения из формы
    $registration = filter_input_array(INPUT_POST, ['email' => FILTER_DEFAULT, 'password' => FILTER_DEFAULT,
    'name' => FILTER_DEFAULT, 'contacts' => FILTER_DEFAULT], true);

    $errors = validateFormSignUp($link, $registration);

    //Удаляем все null значения
    $errors = array_filter($errors);

    if (empty($errors)) {
        $registration['password'] = password_hash($password, PASSWORD_DEFAULT);
        $result = addUser($link, $registration);
        if ($result) {
            header("Location: templates/login.php");
        } else {
            print(include_template('404.php', [
            ]));
            exit();
        }
    }

    $pageContent = include_template('sign-up.php', [
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
    $pageContent = include_template('sign-up.php', [
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
