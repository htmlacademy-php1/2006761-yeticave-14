<?php

function priceModify(int $price): string {
    ceil($price);
    if ($price > 1000) {
        $price = number_format($price, 0, '', ' ');
    }
    return (string) $price . ' ₽';
}

function formatTimer(string $date): string {
    $dateFinish = strtotime($date);
    $secDifference = $dateFinish - time();
    $hours = floor($secDifference / 3600);
    $minutes = floor(($secDifference % 3600) / 60);

    return "{$hours}:{$minutes}";
}

function oneHourTimerFinishing(string $date): string {
    $dateFinish = strtotime($date);
    $secDifference = $dateFinish - time();
    $oneHour = 60 * 60;
    $isLessOneHour = $secDifference <= $oneHour;

    return $isLessOneHour ? "timer--finishing" : "";
}

?>
