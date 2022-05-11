<?php

const DSN = 'smtp://a01042aa5c584b:b117676541b9f6@smtp.mailtrap.io:2525';

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

require_once 'boot.php';

$transport = Transport::fromDsn(DSN);

$sqlLotList = getLotWithoutWinner($link);

// Проверка на истечение срока размещения лотов
if (!empty($sqlLotList)) {
    // Список пользователей победителей по последним ставкам
    foreach ($sqlLotList as $value) {
        $sqlLastBid = getLastBid($link, $value['lot_id']);
    }

    $sqlLastBid = array_filter($sqlLastBid);

    foreach ($sqlLastBid as $value) {
        updateWinner($link, $value['user_id'], $value['lot_id']);
    }

    $mailer = new Mailer($transport);
    $message = new Email();
    $message->subject("Ваша ставка победила");
    $message->from("keks@phpdemo.ru");

    $winners = getWinner($link);

    // Отправка победителям на email письмо с поздравлением
    foreach ($winners as $value) {
        $message->to($value['email']);
        $msgContent = include_template('email.php', ['winner' => $value]);
        $message->html($msgContent);
        $mailer->send($message);
    }
}
