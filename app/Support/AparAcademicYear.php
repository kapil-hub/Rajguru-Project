<?php

namespace App\Support;

class AparAcademicYear
{
    public static function current(): string
    {
        $year = now()->year;
        $start = now()->month >= 7 ? $year : $year - 1;

        return $start.'-'.($start + 1);
    }

    public static function options(): array
    {
        $currentStart = now()->month >= 7 ? now()->year : now()->year - 1;
        $lastStart = $currentStart + 1;
        $years = [];

        for ($start = 2018; $start <= $lastStart; $start++) {
            $years[] = $start.'-'.($start + 1);
        }

        return $years;
    }
}
