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
        $studentIdIndex = count($groupRow) - 1;

        $templateMarker = collect([$groupRow, $typeRow])
            ->flatten()
            ->first(fn ($value) => is_string($value) && str_starts_with(trim($value), 'ATTENDANCE_TEMPLATE:'));

        preg_match('/^ATTENDANCE_TEMPLATE:(\d{4})-(\d{2})$/', trim((string) $templateMarker), $markerParts);
        $templateYear = isset($markerParts[1]) ? (int) $markerParts[1] : null;
        $templateMonth = isset($markerParts[2]) ? (int) $markerParts[2] : null;

        if ($templateMonth === null || $templateYear === null
            || (int) $templateMonth !== (int) $this->meta['month']
            || (int) $templateYear !== (int) $this->meta['year']) {
            $expectedMonth = date('F Y', mktime(0, 0, 0, (int) $this->meta['month'], 1, (int) $this->meta['year']));
            $uploadedMonth = ($templateMonth !== null && $templateYear !== null)
                ? date('F Y', mktime(0, 0, 0, $templateMonth, 1, $templateYear))
                : 'an older or invalid template';

            throw ValidationException::withMessages([
                'attendance' => "Invalid attendance template. This file is for {$uploadedMonth}; please upload the {$expectedMonth} attendance template.",
            ]);
        }

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
                                " classes attended ({$p}) cannot be greater than classes held ({$wd})."
                        ]);
                    }
                }

            // ---------------- SAVE DATA ----------------

            $updateData = [
                'teacher_id' => $this->meta['teacher_id'],
            ];

            if (!empty($map['lecture']['wd'])) {
                $updateData['lecture_working_days'] = $row[$map['lecture']['wd']] ?? null;
            }

            if (!empty($map['lecture']['p'])) {
                $updateData['lecture_present_days'] = $row[$map['lecture']['p']] ?? null;
            }

            if (!empty($map['tutorial']['wd'])) {
                $updateData['tute_working_days'] = $row[$map['tutorial']['wd']] ?? null;
            }

            if (!empty($map['tutorial']['p'])) {
                $updateData['tute_present_days'] = $row[$map['tutorial']['p']] ?? null;
            }

            if (!empty($map['practical']['wd'])) {
                $updateData['practical_working_days'] = $row[$map['practical']['wd']] ?? null;
            }

            if (!empty($map['practical']['p'])) {
                $updateData['practical_present_days'] = $row[$map['practical']['p']] ?? null;
            }

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
                $updateData
            );
        }
    }
}
