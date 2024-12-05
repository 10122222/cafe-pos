<?php

use Carbon\Carbon;

if (! function_exists('getCarbonInstancesFromDateString')) {
    /**
     * @return array{0: Carbon, 1: Carbon, 2: string}
     */
    function getCarbonInstancesFromDateString(?string $dateString): array
    {
        $format = 'd/m/Y';

        [$from, $to] = $dateString ? explode(' - ', $dateString) : [now()->format($format), now()->format($format)];

        $from = Carbon::createFromFormat($format, $from) ?? now();
        $to = Carbon::createFromFormat($format, $to) ?? now();

        $diff = $from->diffInDays($to);

        $label = $diff >= 365 ? 'perMonth' : ($diff >= 30 ? 'perWeek' : 'perDay');

        return [$from, $to, $label];
    }
}
