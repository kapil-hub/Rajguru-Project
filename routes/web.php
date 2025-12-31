<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PaperController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentImportController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\StudentAttendanceController;
use App\Http\Controllers\Admin\AdminFacultyController;
use App\Http\Controllers\AdminAttendanceSettingController;
use App\Http\Controllers\DailyAttendanceController;


Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');


Route::middleware(['auth:admin,teacher,student'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});
Route::middleware('auth:admin,teacher,student')->group(function () {

    Route::get('/change-password', 
        [AuthController::class, 'showChangePassword']
    )->name('password.change');

    Route::post('/change-password', 
        [AuthController::class, 'updatePassword']
    )->name('password.update');

});

Route::middleware('auth:teacher')->prefix('teacher')->group(function () {

    Route::get('attendance', [AttendanceController::class,'pendingList'])->name('teacher.attendance.pending');
    Route::get('monthly/attendance/fill/{assignment}/{month}/{year}', [AttendanceController::class,'fillAttendance'])->name('teacher.monthly.attendance.fill');
    Route::post('attendance/monthly/store', [AttendanceController::class,'storeAttendance'])->name('teacher.attendance.store');

    Route::get('/attendance/history', 
        [AttendanceController::class, 'history']
    )->name('teacher.attendance.history');

    Route::get('/attendance/history/{paper}/{month}/{year}', 
        [AttendanceController::class, 'show']
    )->name('teacher.attendance.history.show');

    Route::get('daily/attendance/fill/{assignment}/{month}/{year}', [DailyAttendanceController::class,'fillAttendance'])->name('teacher.daily.attendance.fill');
    Route::post('attendance/daily/store', [DailyAttendanceController::class,'store'])->name('teacher.attendance.daily.store');

});



Route::middleware('auth:admin')->group(function() {
    Route::get('/papers', [PaperController::class,'index'])->name('papers.index');
    Route::get('/papers/create', [PaperController::class,'create'])->name('papers.create');
    Route::post('/papers', [PaperController::class,'store'])->name('papers.store');
    Route::get('/papers/template', [PaperController::class,'downloadTemplate'])->name('papers.template');
    Route::post('/papers/import', [PaperController::class,'import'])->name('papers.import');
    Route::post('/papers/import/confirm',  [PaperController::class,'confirmImport'])->name('papers.import.confirm');
    Route::get('attendance-settings', 
        [AdminAttendanceSettingController::class, 'index']
    )->name('admin.attendance.settings');

    Route::post('attendance-settings', 
        [AdminAttendanceSettingController::class, 'store']
    )->name('admin.attendance.settings.store');
     Route::prefix('students')->group(function () {

        // Download blank template
        Route::get('/template', [StudentImportController::class, 'template'])
            ->name('students.template');

        // Upload & preview
        Route::post('/import/preview', [StudentImportController::class, 'preview'])
            ->name('students.import.preview');

        // Confirm import
        Route::post('/import/confirm', [StudentImportController::class, 'confirm'])
            ->name('students.import.confirm');

        // Progress polling
        Route::get('/import/progress', [StudentImportController::class, 'progress'])
            ->name('students.import.progress');

        Route::get('/import/progress/status', [StudentImportController::class, 'progressStatus'])->name('students.import.progress.status');
    
    });

    Route::prefix('students')->name('students.')->group(function () {

            Route::get('/', [StudentController::class, 'index'])->name('index');

            Route::get('/create', [StudentController::class, 'create'])->name('create');


            Route::post('/', [StudentController::class, 'store'])->name('store');
    });


    Route::get('teacher-assignments', [AdminController::class,'teacherAssignments'])->name('admin.teacher.assignments');
    Route::post('teacher-assignments', [AdminController::class,'storeTeacherAssignment'])->name('admin.teacher.assignments.store');
    Route::patch('/admin/teacher-assignments/{id}/status',[AdminController::class, 'toggleStatus'])->name('admin.teacher.assignments.status');


});
Route::middleware('auth:admin')->prefix('admin')->group(function(){
    Route::get('faculty', [AdminFacultyController::class,'index'])->name('admin.faculty.index');
    Route::get('faculty/create', [AdminFacultyController::class,'create'])->name('admin.faculty.create');
    Route::post('faculty/store', [AdminFacultyController::class,'store'])->name('admin.faculty.store');
    Route::get('faculty/{faculty}/edit', [AdminFacultyController::class,'edit'])->name('admin.faculty.edit');
    Route::put('admin/faculty/{faculty}',[AdminFacultyController::class, 'update'])->name('admin.faculty.update');
    Route::delete('faculty/{faculty}/delete', [AdminFacultyController::class,'destroy'])->name('admin.faculty.delete');
});


Route::middleware('auth:admin,student')->group(function() {

    Route::prefix('students')->name('students.')->group(function () {
         Route::get('/my-attendance',
            [StudentAttendanceController::class, 'index']
        )->name('student.attendance.index');
        Route::get('/{student}', [StudentController::class, 'show'])->name('show');


        Route::get('/{student}/edit', [StudentController::class, 'edit'])->name('edit');


        Route::put('/{student}', [StudentController::class, 'update'])->name('update');


    });
});























