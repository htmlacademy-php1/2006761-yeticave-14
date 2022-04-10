<?php

function priceModify(int $price): string {
    ceil($price);
    if ($price > 1000) {
        $price = number_format($price, 0, '', ' ');
    }
    return (string) $price . ' ₽';
}

function secDifference(string $date): int {
    $date_end = strtotime($date);
    $sec_difference= $date_end - time();

    return $sec_difference;
}

function formatTimer(int $sec_difference): string {
    $hours = floor($sec_difference / 3600);
    $minutes = floor(($sec_difference % 3600) / 60);

    return "{$hours}:{$minutes}";
}

function oneHourTimerFinishing(int $sec_difference): string {
    $one_hour = 60 * 60;
    $is_less_one_hour = $sec_difference <= $one_hour;

    return $is_less_one_hour ? "timer--finishing" : "";
}

?>
