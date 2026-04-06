<?php

namespace Notifluxion\LaravelNotify\Support;

use Carbon\Carbon;
use InvalidArgumentException;

class IntervalParser
{
    /**
     * Parse a human-readable interval string (e.g., "24h", "15m", "1d", "2w")
     * into a Carbon interval and apply it to a base Carbon date.
     *
     * @param string $interval Ex: "15m", "24h", "2d", "1w"
     * @param \Carbon\Carbon|null $baseDate Base date to apply the interval to
     * @param bool $subtract Whether to subtract or add the interval
     * @return \Carbon\Carbon
     */
    public static function apply(string $interval, ?Carbon $baseDate = null, bool $subtract = false): Carbon
    {
        $date = $baseDate ? $baseDate->copy() : now();
        $interval = strtolower(trim($interval));

        if (!preg_match('/^(\d+)([mhdwy])$/', $interval, $matches)) {
            throw new InvalidArgumentException("Invalid interval format: {$interval}. Supported formats: 15m, 24h, 2d, 1w, 1y");
        }

        $value = (int) $matches[1];
        $unit = $matches[2];

        switch ($unit) {
            case 'm':
                $subtract ? $date->subMinutes($value) : $date->addMinutes($value);
                break;
            case 'h':
                $subtract ? $date->subHours($value) : $date->addHours($value);
                break;
            case 'd':
                $subtract ? $date->subDays($value) : $date->addDays($value);
                break;
            case 'w':
                $subtract ? $date->subWeeks($value) : $date->addWeeks($value);
                break;
            case 'y':
                $subtract ? $date->subYears($value) : $date->addYears($value);
                break;
        }

        return $date;
    }
}
