<?php

require_once('boot.php');

$sqlCategories = getCategories($link);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    $pageContent = include_template('login.php', [
        'sqlCategories' => $sqlCategories,
    ]);

    $layoutContent = include_template('layout.php', [
        'categories' => $sqlCategories,
        'content' => $pageContent,
        'title' => 'Страница входа',

    ]);

    print($layoutContent);
    exit();
}

$login = filter_input_array(INPUT_POST, [
    'email' => FILTER_DEFAULT,
    'password' => FILTER_DEFAULT
], true);

$user = getUserDb($link, $login['email']);
$errors = validateFormLogin($link, $login, $user);
//Если поля заполнены
if (empty($errors) && $user) {
    if (checkPassword($login, $user)) {
        $_SESSION['user'] = $user;
        header('Location: /');
        exit();
    } else {
        $errors['password'] = 'Вы ввели неверный пароль';
    }
}
        
$pageContent = include_template('login.php', [
    'sqlCategories' => $sqlCategories,
    'errors' => $errors,
]);

$layoutContent = include_template('layout.php', [
    'categories' => $sqlCategories,
    'content' => $pageContent,
    'title' => 'Страница входа',
]);

print($layoutContent);
