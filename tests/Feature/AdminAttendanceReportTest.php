<?php

use App\Exports\StudentAttendanceMasterExport;
use App\Http\Controllers\Admin\AdminAttendanceController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('departments', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });

    Schema::create('courses', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });

    Schema::create('student_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });

    Schema::create('student_academic', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_user_id');
        $table->unsignedBigInteger('department_id');
        $table->unsignedBigInteger('course_id');
        $table->string('roll_number');
        $table->unsignedInteger('current_semester');
    });

    Schema::create('student_papers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_user_id');
        $table->unsignedBigInteger('paper_master_id');
    });

    Schema::create('paper_master', function (Blueprint $table) {
        $table->id();
        $table->string('name');
    });

    Schema::create('attendance_settings', function (Blueprint $table) {
        $table->id();
        $table->string('academic_session');
    });

    Schema::create('student_attendances', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('semester_id');
        $table->unsignedBigInteger('paper_master_id');
        $table->unsignedInteger('section');
        $table->unsignedInteger('month');
        $table->unsignedInteger('year');
        $table->unsignedInteger('lecture_working_days')->default(0);
        $table->unsignedInteger('lecture_present_days')->default(0);
        $table->unsignedInteger('tute_working_days')->default(0);
        $table->unsignedInteger('tute_present_days')->default(0);
        $table->unsignedInteger('practical_working_days')->default(0);
        $table->unsignedInteger('practical_present_days')->default(0);
    });

    DB::table('departments')->insert(['id' => 1, 'name' => 'Computing']);
    DB::table('courses')->insert(['id' => 1, 'name' => 'BSc']);
    DB::table('paper_master')->insert(['id' => 1, 'name' => 'Algorithms']);
    DB::table('paper_master')->insert(['id' => 2, 'name' => 'Former Paper']);
});

afterEach(function () {
    Schema::dropIfExists('student_attendances');
    Schema::dropIfExists('attendance_settings');
    Schema::dropIfExists('paper_master');
    Schema::dropIfExists('student_papers');
    Schema::dropIfExists('student_academic');
    Schema::dropIfExists('student_users');
    Schema::dropIfExists('courses');
    Schema::dropIfExists('departments');
});

function insertAttendanceReportStudent(int $studentId, string $name, string $rollNumber): void
{
    DB::table('student_users')->insert([
        'id' => $studentId,
        'name' => $name,
    ]);

    DB::table('student_academic')->insert([
        'student_user_id' => $studentId,
        'department_id' => 1,
        'course_id' => 1,
        'roll_number' => $rollNumber,
        'current_semester' => 2,
    ]);
    DB::table('student_papers')->insert([
        'student_user_id' => $studentId,
        'paper_master_id' => 1,
    ]);
}

function insertAttendanceReportRow(
    int $studentId,
    int $month,
    int $year,
    int $lectureHeld,
    int $lecturePresent,
    int $section = 1,
    int $paperId = 1
): void {
    DB::table('student_attendances')->insert([
        'student_id' => $studentId,
        'semester_id' => 1,
        'paper_master_id' => $paperId,
        'section' => $section,
        'month' => $month,
        'year' => $year,
        'lecture_working_days' => $lectureHeld,
        'lecture_present_days' => $lecturePresent,
    ]);
}

test('admin attendance percentages are weighted by classes held', function () {
    insertAttendanceReportStudent(1, 'Student One', 'R001');
    insertAttendanceReportRow(1, now()->month, now()->year, 1, 1);
    insertAttendanceReportRow(1, now()->month, now()->year, 9, 8, 2);
    insertAttendanceReportRow(1, now()->month, now()->year, 100, 0, 3, 2);

    $view = app(AdminAttendanceController::class)->index(Request::create(
        '/admin/attendance?semester=1',
        'GET',
        ['semester' => 1]
    ));
    $student = $view->getData()['students']->first();

    expect($student->lecture_avg)->toBe(90.0);
});

test('attendance Excel filters by year and combines duplicate paper rows', function () {
    insertAttendanceReportStudent(1, 'Student One', 'R001');
    insertAttendanceReportRow(1, 6, 2026, 4, 3);
    insertAttendanceReportRow(1, 6, 2026, 6, 4, 2);
    insertAttendanceReportRow(1, 6, 2025, 100, 100);
    insertAttendanceReportRow(1, 7, 2026, 100, 100);
    insertAttendanceReportRow(1, 12, 2025, 200, 200);
    insertAttendanceReportRow(1, 7, 2027, 300, 300);
    insertAttendanceReportRow(1, 6, 2026, 1000, 1000, 4, 2);

    $monthlyRows = (new StudentAttendanceMasterExport(6, 2026, true))->collection();
    $overallRows = (new StudentAttendanceMasterExport(6, 2026, false))->collection();

    expect($monthlyRows[0][6])->toBe(10)
        ->and($monthlyRows[0][7])->toBe(7)
        ->and($monthlyRows[1][6])->toBe(10)
        ->and($monthlyRows[1][7])->toBe(7)
        ->and($overallRows[0][6])->toBe(110)
        ->and($overallRows[0][7])->toBe(107);
});
