<?php

use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

require_once 'boot.php';

$dsn = 'smtp://a01042aa5c584b:b117676541b9f6@smtp.mailtrap.io:2525';
$transport = Transport::fromDsn($dsn);

$sqlLotList = getLotWithoutWinner($link);

$sqlLastBid = [];

foreach ($sqlLotList as $value) {
    $sqlLastBid[] = getLastBid($link, $value['lot_id']);
}
$sqlLastBid = array_filter($sqlLastBid);


foreach ($sqlLastBid as $value) {

    updateWinner($link, $value['user_id'], $value['lot_id']);
}

$winners = getWinner($link);

$mailer = new Mailer($transport);
$message = new Email();
$message->subject("Ваша ставка победила");
$message->from("keks@phpdemo.ru");

foreach ($winners as $value) {
    $message->to($value['email']);
    $msgContent = include_template('email.php', ['winner' => $value]);
    $message->html($msgContent);
    try {
        $mailer->send($message);
    } catch (Exception $e) {
        echo $e->getMessage();
    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
        echo $e->getMessage();
    }
}
