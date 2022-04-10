<?php

function priceModify(int $price): string {
    ceil($price);
    if ($price > 1000) {
        $price = number_format($price, 0, '', ' ');
    }
    return (string) $price . ' ₽';
}

function secDifference(string $date): int {
    $dateEnd = strtotime($date);
    $secDifference= $dateEnd - time();

    return $secDifference;
}

function formatTimer(int $secDifference): string {
    $hours = floor($secDifference / 3600);
    $minutes = floor(($secDifference % 3600) / 60);

    return "{$hours}:{$minutes}";
}

function oneHourTimerFinishing(int $secDifference): string {
    $oneHour = 60 * 60;
    $isLessOneHour = $secDifference <= $oneHour;

    return $isLessOneHour ? "timer--finishing" : "";
}

?>
