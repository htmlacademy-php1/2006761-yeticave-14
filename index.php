<?php
require_once('helpers.php');
require_once('functions.php');
require_once('data.php');

$page_content = include_template('main.php', [
    'categories' => $categories,
    'posters' => $posters,
]);

$layout_content = include_template('layout.php', [
    'categories' => $categories,
    'content' => $page_content,
    'title' => $title,
    'user_name' => $user_name,
    'is_auth' => $is_auth,
]);

print($layout_content);
