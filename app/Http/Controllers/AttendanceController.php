<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAttendance;
use App\Models\Paper;
use App\Models\Courses;
use App\Models\Student;
use Carbon\Carbon;
use App\Models\AttendanceSetting;
use App\Models\StudentDailyAttendance;
use App\Models\PaperTimetable;
use App\Models\TimetableHeldPool;
use DB;
use App\Exports\AttendanceTemplateExport;
use App\Imports\StudentAttendanceImport;
use Maatwebsite\Excel\Facades\Excel;
class AttendanceController extends Controller
{
    public function index()
        {
            $teacher = auth('teacher')->user();

            $papers = Paper::where('status','Active')->get();
            $courses = Courses::all();     
            $semesters = ['I','II','III','IV','V','VI','VII','VIII']; 
            $sections = ['A', 'B', 'C'];             
            return view('pages.teacher.attendance.index', compact('papers','courses','semesters','sections'));
        }

    public function loadStudents(Request $request)
    {
        $date = Carbon::parse($request->date)->format('Y-m-d');

        $students = Student::whereHas('enrollments', function($q) use ($request) {
            $q->where('course_id', $request->course_id)
            ->where('semester_id', $request->semester_id)
            // ->where('section', $request->section)
            ->where('paper_master_id', $request->paper_id);
        })->get();
       
        $attendance = StudentAttendance::where('paper_master_id', $request->paper_id)
            // ->where('attendance_date', $date)
            ->get()
            ->keyBy('student_user_id');

        return view('pages.teacher.attendance.partials.student-list',
            compact('students', 'attendance', 'date')
        );
    }

    public function storeAttendance(Request $request)
    {
        foreach ($request->attendance as $studentId => $types) {
            foreach (['lecture', 'tute', 'practical'] as $type) {
                if (!isset($types[$type])) {
                    continue;
                }

                $workingDays = (int) ($types[$type]['working'] ?? 0);
                $presentDays = (int) ($types[$type]['present'] ?? 0);

                if ($presentDays > $workingDays) {
                    return back()
                        ->withInput()
                        ->withErrors([
                            'attendance' => ucfirst($type) . ' classes attended cannot be greater than classes held.',
                        ]);
                }
            }

            $data = [
                'teacher_id'      => auth('teacher')->id(),
                'student_id'      => $studentId,
                'paper_master_id' => $request->paper_master_id,
                'course_id'       => $request->course_id,
                'semester_id'     => $request->semester_id,
                'section'         => $request->section,
                'month'           => $request->month,
                'year'            => $request->year,
            ];

            // Lecture
            if (isset($types['lecture'])) {
                $data['lecture_working_days'] = $types['lecture']['working'] ?? null;
                $data['lecture_present_days'] = $types['lecture']['present'] ?? null;
            }

            // Tute
            if (isset($types['tute'])) {
                $data['tute_working_days'] = $types['tute']['working'] ?? null;
                $data['tute_present_days'] = $types['tute']['present'] ?? null;
            }

            // Practical
            if (isset($types['practical'])) {
                $data['practical_working_days'] = $types['practical']['working'] ?? null;
                $data['practical_present_days'] = $types['practical']['present'] ?? null;
            }

            StudentAttendance::updateOrCreate(
                [
                    'student_id'      => $studentId,
                    'paper_master_id' => $request->paper_master_id,
                    'course_id'       => $request->course_id,
                    'semester_id'     => $request->semester_id,
                    'section'         => $request->section,
                    'month'           => $request->month,
                    'year'            => $request->year,
                ],
                $data
            );
        }

        return redirect()
            ->route('teacher.attendance.pending')
            ->with('success', 'Attendance saved successfully');
    }



    public function pendingList(Request $request) {
        $teacherId = auth('teacher')->id();

        $attendanceSettings = AttendanceSetting::where('status', 1)->get();
        $monthOptions = $this->configuredMonthOptions($attendanceSettings);
        $allowedMonthKeys = collect($monthOptions)
            ->reject(fn ($option) => $option['disabled'])
            ->pluck('value')
            ->all();
        $currentMonthKey = now()->format('Y-m');
        $latestAllowedMonthKey = collect($allowedMonthKeys)->sort()->last();
        $defaultMonthKey = in_array($currentMonthKey, $allowedMonthKeys, true)
            ? $currentMonthKey
            : ($latestAllowedMonthKey ?? $currentMonthKey);
        $selectedMonthKey = in_array($request->month_year, $allowedMonthKeys, true)
            ? $request->month_year
            : $defaultMonthKey;
        [$selectedYear, $selectedMonth] = array_map('intval', explode('-', $selectedMonthKey));

        $timetableGroups = PaperTimetable::with(['course', 'paper'])
            ->where('teacher_id', $teacherId)
            ->get()
            ->groupBy(fn ($slot) => implode('|', [
                $slot->course_id,
                $slot->semester,
                $slot->paper_id,
                $this->batchIdentifier($slot),
            ]));

        $assignments = collect();

        foreach ($timetableGroups as $slots) {
            $firstSlot = $slots->first();
            $students = $this->studentsForSlot($firstSlot);

            if ($students->isEmpty()) {
                continue;
            }

            $groupedStudents = $students->groupBy(function ($student) {
                $section = strtoupper(trim((string) ($student->academic->section ?? '')));

                return $section !== '' ? $section : 'A';
            });

            foreach ($groupedStudents as $section => $sectionStudents) {
                $pool = TimetableHeldPool::firstOrCreate(
                    [
                        'teacher_id' => $teacherId,
                        'course_id' => $firstSlot->course_id,
                        'semester_id' => $firstSlot->semester,
                        'paper_master_id' => $firstSlot->paper_id,
                        'section' => $section,
                        'batch_identifier' => $this->batchIdentifier($firstSlot),
                        'month' => $selectedMonth,
                        'year' => $selectedYear,
                    ],
                    [
                        'lecture_held' => 0,
                        'tute_held' => 0,
                        'practical_held' => 0,
                        'student_ids' => $sectionStudents->pluck('id')->values()->all(),
                        'marked_slots' => [],
                    ]
                );

                $studentIds = $sectionStudents->pluck('id')->values()->all();
                $existingStudentIds = is_array($pool->student_ids)
                    ? $pool->student_ids
                    : (json_decode($pool->student_ids ?? '[]', true) ?? []);
                $mergedStudentIds = array_values(array_unique(array_merge($existingStudentIds, $studentIds)));

                if ($mergedStudentIds !== $existingStudentIds) {
                    $pool->student_ids = $mergedStudentIds;
                    $pool->save();
                }

                $pool->loadMissing(['course', 'semester', 'paperMaster']);
                $pool->has_lecture_slot = $slots->contains(fn ($slot) => (bool) $slot->is_lecture);
                $pool->has_tute_slot = $slots->contains(fn ($slot) => (bool) $slot->is_tutorial);
                $pool->has_practical_slot = $slots->contains(fn ($slot) => (bool) $slot->is_practical);

                $assignments->push($pool);
            }
        }

        $assignments = $assignments
            ->sortBy([
                ['course.name', 'asc'],
                ['semester_id', 'asc'],
                ['section', 'asc'],
                ['paperMaster.name', 'asc'],
            ])
            ->values();

        $isLocked = [1,2,3,4];

        return view('pages.teacher.attendance.pending', compact(
            'assignments',
            'attendanceSettings',
            'isLocked',
            'monthOptions',
            'selectedMonthKey',
            'selectedMonth',
            'selectedYear'
        ));
    }

    public function fillAttendance($assignmentId, $month, $year)
    {
        if ($this->isFutureMonth((int) $month, (int) $year)) {
            return redirect()
                ->route('teacher.attendance.pending')
                ->with('error', 'Future month attendance cannot be filled.');
        }

        $assignment = \App\Models\TimetableHeldPool::findOrFail($assignmentId);
        $slots = PaperTimetable::where('teacher_id', $assignment->teacher_id)
            ->where('course_id', $assignment->course_id)
            ->where('semester', $assignment->semester_id)
            ->where('paper_id', $assignment->paper_master_id)
            ->get()
            ->filter(fn ($slot) => $this->batchIdentifier($slot) === (string) ($assignment->batch_identifier ?? ''));

        $assignment->has_lecture_slot = $slots->contains(fn ($slot) => (bool) $slot->is_lecture);
        $assignment->has_tute_slot = $slots->contains(fn ($slot) => (bool) $slot->is_tutorial);
        $assignment->has_practical_slot = $slots->contains(fn ($slot) => (bool) $slot->is_practical);

        $studentIds = is_array($assignment->student_ids) 
            ? $assignment->student_ids 
            : (json_decode($assignment->student_ids ?? '[]', true) ?? []);

        $students = Student::with('academic')
            ->whereIn('id', $studentIds)
            ->orderBy('name')
            ->get();
      
        $oldAttendences = StudentAttendance::where(
                [
                    'paper_master_id' => $assignment->paper_master_id,
                    'course_id'       => $assignment->course_id,
                    'semester_id'     => $assignment->semester_id,
                    'month'=>$month,
                    'year'=>$year
                ]
            )->get()->keyBy('student_id');

        return view(
            'pages.teacher.attendance.fill',
            compact('assignment', 'students', 'month', 'year','oldAttendences')
        );
    }

    private function configuredMonthOptions($attendanceSettings): array
    {
        $options = [];
        $currentYear = now()->year;

        foreach ($attendanceSettings as $setting) {
            $sessionStartYear = (int) substr((string) $setting->academic_session, 0, 4);
            $year = $sessionStartYear > 0 ? $sessionStartYear : $currentYear;
            $startMonth = (int) $setting->start_month;
            $endMonth = (int) $setting->end_month;
            $months = $startMonth <= $endMonth
                ? range($startMonth, $endMonth)
                : array_merge(range($startMonth, 12), range(1, $endMonth));

            foreach ($months as $month) {
                $monthYear = $month >= $startMonth ? $year : $year + 1;
                $key = sprintf('%04d-%02d', $monthYear, $month);
                $options[$key] = [
                    'value' => $key,
                    'label' => Carbon::createFromDate($monthYear, $month, 1)->format('F Y'),
                    'disabled' => $this->isFutureMonth($month, $monthYear),
                ];
            }
        }

        if (empty($options)) {
            $key = now()->format('Y-m');
            $options[$key] = [
                'value' => $key,
                'label' => now()->format('F Y'),
                'disabled' => false,
            ];
        }

        ksort($options);

        return array_values($options);
    }

    private function isFutureMonth(int $month, int $year): bool
    {
        return Carbon::createFromDate($year, $month, 1)->startOfMonth()
            ->greaterThan(now()->startOfMonth());
    }

    private function studentsForSlot(PaperTimetable $slot)
    {
        $paperId = $slot->paper_id;
        $courseId = $slot->course_id;

        $studentsQuery = Student::with('academic')->where(function ($q) use ($paperId, $courseId) {
            $q->whereHas('papers', function ($p) use ($paperId) {
                    $p->where('paper_master_id', $paperId)
                        ->whereHas('paper', function ($pm) {
                            $pm->whereIn('paper_type', ['DSC', 'DSE']);
                        })
                        ->where('is_backlog', 0);
                })
                ->whereHas('academic', function ($a) use ($courseId) {
                    $a->where('course_id', $courseId);
                });

            $q->orWhereHas('papers', function ($p) use ($paperId) {
                $p->where('paper_master_id', $paperId)
                    ->whereHas('paper', function ($pm) {
                        $pm->whereNotIn('paper_type', ['DSC', 'DSE']);
                    });
            });
        });

        if ($this->hasSlotBatches($slot)) {
            $batches = $this->slotBatches($slot);
            $studentsQuery->whereHas('papers', function ($p) use ($paperId, $batches) {
                $p->where('paper_master_id', $paperId)
                    ->whereIn(DB::raw('UPPER(TRIM(batch))'), $batches);
            });
        }

        return $studentsQuery->get();
    }

    private function batchIdentifier(PaperTimetable $slot): string
    {
        if (!$this->hasSlotBatches($slot)) {
            return '';
        }

        $batches = $this->slotBatches($slot);
        sort($batches, SORT_NATURAL | SORT_FLAG_CASE);

        return implode(',', $batches);
    }

    private function slotBatches(PaperTimetable $slot): array
    {
        if (blank($slot->batches)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            fn ($batch) => strtoupper(trim($batch)),
            explode(',', $slot->batches)
        ))));
    }

    private function hasSlotBatches(PaperTimetable $slot): bool
    {
        return !empty($this->slotBatches($slot));
    }

public function history()
    {
        $teacherId = auth('teacher')->id();
        $attendanceSettings = AttendanceSetting::where('status', 1)
                ->get()->last();
        if($attendanceSettings->attendance_type == 'daily'){
            $records = StudentDailyAttendance::with('paper.course')->where('teacher_id', $teacherId)
            ->select('paper_master_id',
                'course_id',
                'semester_id',
                'section',
                DB::raw('MONTH(attendance_date) as month'),
                DB::raw('YEAR(attendance_date) as year'),
                )
                ->groupBy(
                'paper_master_id',
                'course_id',
                'semester_id',
                'section',
                'month',
                'year'
                )
                ->latest('year')
                ->latest('month')
                ->get();
        }else{
            $records = StudentAttendance::with('paper.course')->where('teacher_id', $teacherId)
                ->select(
                    'paper_master_id',
                    'course_id',
                    'semester_id',
                    'section',
                    'month',
                    'year'
                )
                ->groupBy(
                    'paper_master_id',
                    'course_id',
                    'semester_id',
                    'section',
                    'month',
                    'year'
                )
                ->latest('year')
                ->latest('month')
                ->get();
        }
        // echo "<pre>";print_r($records->toArray());die;
        return view('pages.teacher.attendance.history', compact('records'));
    }

    public function show($paperId, $month, $year)
    {
        $teacherId = auth('teacher')->id();
         $attendanceSettings = AttendanceSetting::where('status', 1)
                ->get()->last();
        if($attendanceSettings->attendance_type == 'daily'){

            $students = \App\Models\Student::whereHas('dailyAttendances', function ($q) use ($teacherId, $paperId, $month, $year) {
                $q->where('teacher_id', $teacherId)
                ->where('paper_master_id', $paperId)
                ->whereMonth('attendance_date', $month)
                ->whereYear('attendance_date', $year);
            })->get();

            $records = [];

            foreach ($students as $student) {
                $attendances = $student->dailyAttendances()
                    ->where('teacher_id', $teacherId)
                    ->where('paper_master_id', $paperId)
                    ->whereMonth('attendance_date', $month)
                    ->whereYear('attendance_date', $year)
                    ->get();

                $lecture_working_days   = $attendances->where('lecture', 1)->count();
                $lecture_present_days   = $attendances->where('lecture', 1)->count();
                $tute_working_days      = $attendances->where('tute', 1)->count();
                $tute_present_days      = $attendances->where('tute', 1)->count();
                $practical_working_days = $attendances->where('practical', 1)->count();
                $practical_present_days = $attendances->where('practical', 1)->count();

                $records[] = (object)[
                    'student' => $student,
                    'lecture_working_days' => $lecture_working_days,
                    'lecture_present_days' => $lecture_present_days,
                    'tute_working_days' => $tute_working_days,
                    'tute_present_days' => $tute_present_days,
                    'practical_working_days' => $practical_working_days,
                    'practical_present_days' => $practical_present_days,
                ];

            }
            return view('pages.teacher.attendance.history_show', compact('records', 'month', 'year'));

        }else{
            $records = StudentAttendance::where([
                'teacher_id' => $teacherId,
                'paper_master_id' => $paperId,
                'month' => $month,
                'year' => $year,
            ])->with('student')->get();

            return view('pages.teacher.attendance.history_show', compact(
                'records', 'month', 'year'
            ));
        }
    }


    public function downloadTemplate(Request $request, $month, $year)
    {
        $students = json_decode($request->student_obj, true);
        $request->validate([
            'template_month' => 'required|integer|between:1,12',
            'template_year' => 'required|integer|min:2000|max:2100',
        ]);

        $templateMonth = (int) $request->input('template_month');
        $templateYear = (int) $request->input('template_year');

        return Excel::download(
            new AttendanceTemplateExport(
                students: $students,
                lectureWD: isset($request->lecture_days) ? $request->lecture_days : "hidden",
                tuteWD: isset($request->tute_days) ? $request->tute_days : "hidden",
                practicalWD: isset($request->practical_days) ? $request->practical_days :"hidden",
                month: $templateMonth,
                year: $templateYear
            ),
            sprintf('attendance_template_%04d_%02d.xlsx', $templateYear, $templateMonth)
        );
    }

public function import(Request $request)
    {
        $request->validate([
            'file'            => 'required|mimes:xlsx',
            'paper_master_id' => 'required',
            'course_id'       => 'required',
            'semester_id'     => 'required',
            // 'section'         => 'required',
            'month'           => 'required',
            'year'            => 'required',
        ]);

        Excel::import(
            new StudentAttendanceImport(
                meta: [
                    'teacher_id'      => auth()->id(),
                    'paper_master_id' => $request->paper_master_id,
                    'course_id'       => $request->course_id,
                    'semester_id'     => $request->semester_id,
                    'section'         => $request->section ?? 'A',
                    'month'           => $request->month,
                    'year'            => $request->year,
                ]
            ),
            $request->file('file')
        );

        return back()->with('success', 'Attendance imported successfully');
    }

}
