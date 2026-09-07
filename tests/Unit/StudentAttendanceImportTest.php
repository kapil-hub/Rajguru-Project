<?php

use App\Imports\StudentAttendanceImport;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

test('rejects an attendance template from a different month', function () {
    $import = new StudentAttendanceImport([
        'month' => 8,
        'year' => 2026,
    ]);

    expect(fn () => $import->collection(new Collection([
        ['Exam Roll Number', 'Student Name', 'ATTENDANCE_TEMPLATE:2026-07'],
        ['', '', ''],
    ])))->toThrow(
        ValidationException::class,
        'This file is for July 2026; please upload the August 2026 attendance template.'
    );
});