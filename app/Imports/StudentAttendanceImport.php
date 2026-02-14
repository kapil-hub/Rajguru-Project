<?php

namespace App\Imports;

use App\Models\StudentAttendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Validation\ValidationException;

class StudentAttendanceImport implements ToCollection
{
    protected array $meta;

    public function __construct(array $meta)
    {
        $this->meta = $meta;
    }

    public function collection(Collection $rows)
    {
        $groupRow = $rows[0] ?? [];
        $typeRow  = $rows[1] ?? [];

        $map = [];
        $currentGroup = null;

        foreach ($groupRow as $index => $groupName) {

            if (!empty($groupName)) {
                $currentGroup = strtolower(trim($groupName));
            }

            $subType = strtolower(trim($typeRow[$index] ?? ''));

            if (in_array($subType, ['wd', 'classes held', 'working days'])) {
                $map[$currentGroup]['wd'] = $index;
            }

            if (in_array($subType, ['p', 'classes attended', 'present'])) {
                $map[$currentGroup]['p'] = $index;
            }
        }

        $studentIdIndex = count($groupRow) - 1;

        foreach ($rows->skip(2) as $rowNumber => $row) {

            $excelRow = $rowNumber + 3;
            $studentId = $row[$studentIdIndex] ?? null;

            if (!$studentId) continue;

            // ---------------- VALIDATION ----------------

            $studentName = trim($row[1] ?? 'Unknown Student');

                foreach (['lecture', 'tutorial', 'practical'] as $type) {

                    $wdIndex = $map[$type]['wd'] ?? null;
                    $pIndex  = $map[$type]['p'] ?? null;

                    if ($wdIndex === null || $pIndex === null) {
                        continue;
                    }

                    $wd = (int) ($row[$wdIndex] ?? 0);
                    $p  = (int) ($row[$pIndex] ?? 0);

                    if ($p > $wd) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'attendance' =>
                                'Student "' . $studentName . '": ' .
                                ucfirst($type) .
                                " present days ({$p}) cannot be greater than working days ({$wd})."
                        ]);
                    }
                }

            // ---------------- SAVE DATA ----------------

            StudentAttendance::updateOrCreate(
                [
                    'student_id'      => $studentId,
                    'paper_master_id' => $this->meta['paper_master_id'],
                    'course_id'       => $this->meta['course_id'],
                    'semester_id'     => $this->meta['semester_id'],
                    'section'         => $this->meta['section'],
                    'month'           => $this->meta['month'],
                    'year'            => $this->meta['year'],
                ],
                [
                    'teacher_id' => $this->meta['teacher_id'],

                    'lecture_working_days'   => $map['lecture']['wd'] ?? null
                        ? $row[$map['lecture']['wd']] ?? null : null,

                    'lecture_present_days'   => $map['lecture']['p'] ?? null
                        ? $row[$map['lecture']['p']] ?? null : null,

                    'tute_working_days'      => $map['tutorial']['wd'] ?? null
                        ? $row[$map['tutorial']['wd']] ?? null : null,

                    'tute_present_days'      => $map['tutorial']['p'] ?? null
                        ? $row[$map['tutorial']['p']] ?? null : null,

                    'practical_working_days' => $map['practical']['wd'] ?? null
                        ? $row[$map['practical']['wd']] ?? null : null,

                    'practical_present_days' => $map['practical']['p'] ?? null
                        ? $row[$map['practical']['p']] ?? null : null,
                ]
            );
        }
    }
}
