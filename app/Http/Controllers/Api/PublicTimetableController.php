<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaperTimetable;
use App\Models\Departments;
use App\Models\Courses;
use App\Models\Paper;
use App\Models\Room;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicTimetableController extends Controller
{
    /**
     * Widget Configuration
     */
    public function config()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'college_name' => 'School of Open Learning',
                'theme' => '#4F46E5',
                'days' => [
                    'Monday',
                    'Tuesday',
                    'Wednesday',
                    'Thursday',
                    'Friday',
                    'Saturday'
                ]
            ]
        ]);
    }

    /**
     * Load all filter values
     */
    public function filters()
    {

        $filters = Cache::remember('public_timetable_filters', 3600, function () {

            return [

                'departments' => Departments::orderBy('name')
                    ->select('id', 'name')
                    ->get(),

                'courses' => Courses::orderBy('name')
                    ->select('id', 'name', 'dept_id')
                    ->get(),

                'papers' => Paper::orderBy('name')
                    ->select('id', 'name', 'code')
                    ->get(),

                'teachers' => Teacher::orderBy('name')
                    ->select('id', 'name')
                    ->get(),

                'rooms' => Room::orderBy('building_name')
                    ->orderBy('floor_no')
                    ->orderBy('room_number')
                    ->select('id', 'building_name', 'floor_no', 'room_number', 'is_lab')
                    ->get(),

                'semesters' => PaperTimetable::select('semester')
                    ->distinct()
                    ->orderBy('semester')
                    ->pluck('semester')
            ];

        });

        return response()->json([
            'success' => true,
            'data' => $filters
        ]);

    }

    /**
     * Return timetable
     */
    public function timetable(Request $request)
    {

        $query = PaperTimetable::with([
            'department:id,name',
            'course:id,name,dept_id',
            'paper:id,name,code',
            'teacher:id,name',
            'room:id,building_name,floor_no,room_number,is_lab'
        ]);

        if ($request->filled('department')) {
            $query->where('department_id', $request->department);
        }

        if ($request->filled('course')) {
            $query->where('course_id', $request->course);
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('paper')) {
            $query->where('paper_id', $request->paper);
        }

        if ($request->filled('teacher')) {
            $query->where('teacher_id', $request->teacher);
        }

        if ($request->filled('room')) {
            $query->where('room_id', $request->room);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->whereHas('department', fn($department) => $department->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('course', fn($course) => $course->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('paper', function ($paper) use ($search) {
                        $paper->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    })
                    ->orWhereHas('teacher', fn($teacher) => $teacher->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('room', function ($room) use ($search) {
                        $room->where('building_name', 'like', "%{$search}%")
                            ->orWhere('floor_no', 'like', "%{$search}%")
                            ->orWhere('room_number', 'like', "%{$search}%");
                    });
            });
        }

        $rows = $query
            ->orderByRaw("
                FIELD(day_name,
                'Monday',
                'Tuesday',
                'Wednesday',
                'Thursday',
                'Friday',
                'Saturday')
            ")
            ->orderBy('start_time')
            ->get();

        $result = [];

        foreach ($rows as $row) {

            $result[] = [

                'id' => $row->id,

                'day' => $row->day_name,

                'start' => substr($row->start_time,0,5),

                'end' => substr($row->end_time,0,5),

                'department' => optional($row->department)->name,

                'course' => optional($row->course)->name,

                'semester' => $row->semester,

                'paper' => optional($row->paper)->name,

                'paper_code' => optional($row->paper)->code,

                'teacher' => optional($row->teacher)->name,

                'room' => $this->formatRoom($row->room),

                'color' => $row->color,

                'lecture' => (bool)$row->is_lecture,

                'tutorial' => (bool)$row->is_tutorial,

                'practical' => (bool)$row->is_practical,

                'coordinator' => (bool)$row->is_coordinator,

                'batches' => $this->parseBatches($row->batches)

            ];

        }

        return response()->json([
            'success' => true,
            'count' => count($result),
            'data' => $result
        ]);

    }

    private function formatRoom(?Room $room): ?string
    {
        if (!$room) {
            return null;
        }

        $parts = array_filter([
            $room->building_name,
            'Floor ' . $room->floor_no,
            'Room ' . $room->room_number,
        ]);

        return implode(' - ', $parts) . ($room->is_lab ? ' (Lab)' : '');
    }

    private function parseBatches($batches): array
    {
        if (blank($batches)) {
            return [];
        }

        $decoded = json_decode((string) $batches, true);

        if (is_array($decoded)) {
            return array_values(array_filter($decoded));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $batches))));
    }

}
