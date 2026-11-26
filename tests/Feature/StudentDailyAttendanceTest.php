<?php

use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\DashboardController;
use App\Models\Student;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('student_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->timestamps();
    });

    Schema::create('student_academic', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_user_id');
        $table->unsignedBigInteger('current_semester');
        $table->timestamps();
    });

    Schema::create('student_papers', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_user_id');
        $table->unsignedBigInteger('paper_master_id');
        $table->timestamps();
    });

    Schema::create('attendance_settings', function (Blueprint $table) {
        $table->id();
        $table->boolean('status');
        $table->string('attendance_type');
        $table->timestamps();
    });

    Schema::create('paper_master', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('code');
        $table->timestamps();
    });

    Schema::create('student_daily_attendances', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('semester_id');
        $table->unsignedBigInteger('paper_master_id');
        $table->date('attendance_date');
        $table->boolean('lecture')->nullable();
        $table->boolean('tute')->nullable();
        $table->boolean('practical')->nullable();
        $table->timestamps();
    });

    Schema::create('student_attendances', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('student_id');
        $table->unsignedBigInteger('semester_id');
        $table->unsignedBigInteger('paper_master_id');
        $table->unsignedInteger('lecture_working_days')->default(0);
        $table->unsignedInteger('lecture_present_days')->default(0);
        $table->unsignedInteger('tute_working_days')->default(0);
        $table->unsignedInteger('tute_present_days')->default(0);
        $table->unsignedInteger('practical_working_days')->default(0);
        $table->unsignedInteger('practical_present_days')->default(0);
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('student_attendances');
    Schema::dropIfExists('student_daily_attendances');
    Schema::dropIfExists('paper_master');
    Schema::dropIfExists('attendance_settings');
    Schema::dropIfExists('student_papers');
    Schema::dropIfExists('student_academic');
    Schema::dropIfExists('student_users');
});

test('daily attendance is summarized from its flags by the recorded month', function () {
    $student = Student::create([
        'name' => 'Attendance Student',
        'email' => 'attendance-student@example.test',
        'password' => bcrypt('password'),
    ]);

    $student->academic()->create(['current_semester' => 2]);
    $student->papers()->create(['paper_master_id' => 7]);

    \App\Models\AttendanceSetting::create([
        'status' => 1,
        'attendance_type' => 'daily',
    ]);

    \App\Models\Paper::create([
        'id' => 7,
        'name' => 'Mathematics',
        'code' => 'MATH101',
    ]);

    $student->dailyAttendances()->createMany([
        [
            'semester_id' => 2,
            'paper_master_id' => 7,
            'attendance_date' => '2026-03-12',
            'lecture' => 1,
            'tute' => 0,
            'practical' => null,
        ],
        [
            'semester_id' => 2,
            'paper_master_id' => 7,
            'attendance_date' => '2026-04-09',
            'lecture' => 0,
            'tute' => 1,
            'practical' => null,
        ],
    ]);
    $student->dailyAttendances()->create([
        'semester_id' => 2,
        'paper_master_id' => 8,
        'attendance_date' => '2026-03-13',
        'lecture' => 1,
        'tute' => 1,
        'practical' => 1,
    ]);

    $this->actingAs($student, 'student');
    $view = app(StudentAttendanceController::class)->index();
    $monthlyRecords = $view->getData()['attendance']->first()->values();

    expect($monthlyRecords)->toHaveCount(2)
        ->and($monthlyRecords[0]->month)->toBe(3)
        ->and($monthlyRecords[0]->lecture_working_days)->toBe(1)
        ->and($monthlyRecords[0]->lecture_present_days)->toBe(1)
        ->and($monthlyRecords[0]->tute_working_days)->toBe(1)
        ->and($monthlyRecords[0]->tute_present_days)->toBe(0)
        ->and($monthlyRecords[1]->month)->toBe(4)
        ->and($monthlyRecords[1]->lecture_working_days)->toBe(1)
        ->and($monthlyRecords[1]->lecture_present_days)->toBe(0)
        ->and($monthlyRecords[1]->tute_present_days)->toBe(1);
});

test('monthly dashboard totals include only current papers', function () {
    $student = Student::create([
        'name' => 'Monthly Attendance Student',
        'email' => 'monthly-attendance-student@example.test',
        'password' => bcrypt('password'),
    ]);

    $student->academic()->create(['current_semester' => 1]);
    $student->papers()->create(['paper_master_id' => 7]);

    \App\Models\AttendanceSetting::create([
        'status' => 1,
        'attendance_type' => 'monthly',
    ]);

    \App\Models\StudentAttendance::create([
        'student_id' => $student->id,
        'semester_id' => 1,
        'paper_master_id' => 7,
        'lecture_working_days' => 115,
        'lecture_present_days' => 70,
    ]);
    \App\Models\StudentAttendance::create([
        'student_id' => $student->id,
        'semester_id' => 1,
        'paper_master_id' => 8,
        'lecture_working_days' => 100,
        'lecture_present_days' => 100,
    ]);

    \App\Models\Paper::create([
        'id' => 7,
        'name' => 'Current Mathematics',
        'code' => 'MATH101',
    ]);
    \App\Models\Paper::create([
        'id' => 8,
        'name' => 'Archived Mathematics',
        'code' => 'MATH100',
    ]);

    $this->actingAs($student, 'student');
    $view = app(DashboardController::class)->index();
    $data = $view->getData();
    $attendanceView = app(StudentAttendanceController::class)->index();

    expect($data['lectureHeld'])->toBe(115)
        ->and($data['lecturePresent'])->toBe(70)
        ->and($data['totalHeld'])->toBe(115)
        ->and($data['totalPresent'])->toBe(70)
        ->and($attendanceView->getData()['attendance']->keys()->all())->toBe([7]);
});
