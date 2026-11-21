<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class TimeSlots
{
    /**
     * Quarter-hour slots of the day keyed by 24h value, labelled in 12h AM/PM.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (range(0, 23) as $hour) {
            foreach ([0, 15, 30, 45] as $minute) {
                $options[sprintf('%02d:%02d', $hour, $minute)] = Carbon::createFromTime($hour, $minute)->format('h:i A');
            }
        }

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_keys(self::options());
    }
}
