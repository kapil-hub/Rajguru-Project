<?php

if (! function_exists('attendanceMarks')) {

    function attendanceMarks(float $percentage, int $maxMarks): float
    {
        if ($percentage < 67) {
            return 0.00;
        }

        $map = [
            2 => [
                [67, 69.99, 0.40],
                [70, 74.99, 0.80],
                [75, 79.99, 1.20],
                [80, 84.99, 1.60],
                [85, 100,  2.00],
            ],
            4 => [
                [67, 69.99, 0.80],
                [70, 74.99, 1.60],
                [75, 79.99, 2.40],
                [80, 84.99, 3.20],
                [85, 100,  4.00],
            ],
            5 => [
                [67, 69.99, 1.00],
                [70, 74.99, 2.00],
                [75, 79.99, 3.00],
                [80, 84.99, 4.00],
                [85, 100,  5.00],
            ],
            6 => [
                [67, 69.99, 1.20],
                [70, 74.99, 2.40],
                [75, 79.99, 3.60],
                [80, 84.99, 4.80],
                [85, 100,  6.00],
            ],
        ];

        foreach ($map[$maxMarks] ?? [] as [$min, $max, $marks]) {
            if ($percentage >= $min && $percentage <= $max) {
                return $marks;
            }
        }

        return 0.00;
    }
}
