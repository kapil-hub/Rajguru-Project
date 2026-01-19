<?php

if (!function_exists('lectureMarksBreakup')) {
    function lectureMarksBreakup(int $lectures): array
    {
        $total = $lectures * 40;

        $theory = round($total * 0.75);
        $ia     = round($total * 0.25);

        return [
            'total'  => $total,
            'theory' => $theory,
            'ia'     => $ia,
            'ia_breakup' => iaBreakup($ia),
        ];
    }
}

if (!function_exists('tutorialMarksBreakup')) {
    function tutorialMarksBreakup(int $tutorials): array
    {
        $total = $tutorials * 40;

        // $theory = round($total * 0.75);
        // $ia     = round($total * 0.25);

        return [
            'ca'  => $total
        ];
    }
}


if (!function_exists('practicalMarksBreakup')) {
    function practicalMarksBreakup(int $practicals): array
    {
        $total = $practicals * 40;

        return [
            'ca' => round($total * 0.25),
            'written_exam' => round($total * 0.50),
            'viva_voce' => round($total * 0.25),
        ];
    }
}



if (!function_exists('iaBreakup')) {
    function iaBreakup(int $iaMarks): array
    {
        return [
            'class_test' => round($iaMarks * 0.40),
            'assignment' => round($iaMarks * 0.40),
            'attendance' => round($iaMarks * 0.20),
        ];
    }
}
