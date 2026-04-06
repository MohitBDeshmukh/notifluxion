<?php

namespace Notifluxion\LaravelNotify\Scheduling;

use Carbon\Carbon;
use Notifluxion\LaravelNotify\Support\IntervalParser;

class ScheduleBuilder
{
    /**
     * @var \Carbon\Carbon[] 
     */
    protected array $dates = [];

    /**
     * Get the array of calculated concrete dates.
     *
     * @return \Carbon\Carbon[]
     */
    public function getDates(): array
    {
        return $this->dates;
    }

    /**
     * Add exact dates to the schedule array.
     *
     * @param \Carbon\Carbon|\Carbon\Carbon[] $dates
     * @return $this
     */
    public function exact($dates): self
    {
        $dates = is_iterable($dates) ? $dates : [$dates];
        foreach ($dates as $date) {
            $this->dates[] = $date instanceof \DateTimeInterface ? Carbon::instance($date) : Carbon::parse($date);
        }
        return $this;
    }

    /**
     * Compute intervals backwards from a target anchor date.
     *
     * @param \Carbon\Carbon|string $target
     * @param string[]|string $intervals Ex: ['24h', '1h', '15m']
     * @return $this
     */
    public function before($target, $intervals): self
    {
        $anchor = $target instanceof Carbon ? $target : Carbon::parse($target);
        $intervals = is_iterable($intervals) ? $intervals : [$intervals];

        foreach ($intervals as $interval) {
            $this->dates[] = IntervalParser::apply($interval, $anchor, true);
        }

        return $this;
    }

    /**
     * Compute intervals forwards from a target anchor date (or now).
     *
     * @param \Carbon\Carbon|string|null $target
     * @param string[]|string $intervals Ex: ['1d', '3d', '1w']
     * @return $this
     */
    public function after($target, $intervals): self
    {
        $anchor = $target ? ($target instanceof Carbon ? $target : Carbon::parse($target)) : Carbon::now();
        $intervals = is_iterable($intervals) ? $intervals : [$intervals];

        foreach ($intervals as $interval) {
            $this->dates[] = IntervalParser::apply($interval, $anchor, false);
        }

        return $this;
    }
}
