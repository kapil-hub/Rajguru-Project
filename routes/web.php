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
use App\Http\Controllers\IaController;
use App\Http\Controllers\Registration\RegistrationController;
use App\Http\Controllers\Admin\RegistrationWindowController;
use App\Http\Controllers\Admin\AdminAttendanceController;


Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/forgot-password', [AuthController::class, 'showForgot']);
Route::post('/forgot-password', [AuthController::class, 'sendOtp']);

Route::get('/verify-otp', [AuthController::class, 'showOtpForm']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::get('/reset-password', [AuthController::class, 'showResetPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

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

Route::middleware(['auth:admin,teacher'])->group(function () {
    Route::get('/teacher/marksBreakup/{paperId}', [IaController::class,'index'])->name('teacher.marksBreakup');
    Route::get('/paper/edit/{id}', 
        [PaperController::class, 'edit']
    )->name('paper.edit');
    Route::post('/paper/update/{id}', 
        [PaperController::class, 'update']
    )->name('paper.update');
});


Route::middleware('auth:teacher')->prefix('teacher')->group(function () {

    Route::get('profile', [AdminFacultyController::class,'edit'])->name('teacher.profile.edit');
    Route::put('profile/{faculty}/update',[AdminFacultyController::class, 'update'])->name('teacher.profile.update');

    Route::get('attendance', [AttendanceController::class,'pendingList'])->name('teacher.attendance.pending');
    Route::get('monthly/attendance/fill/{assignment}/{month}/{year}', [AttendanceController::class,'fillAttendance'])->name('teacher.monthly.attendance.fill');
    Route::post('monthly/attendance/fill/{assignment}/{month}/{year}/downloadTemplate', [AttendanceController::class,'downloadTemplate'])->name('teacher.monthly.attendance.fill.downloadTemplate');
    
    Route::post('/attendance/import',[AttendanceController::class, 'import'])->name('attendance.import');
    Route::post('attendance/monthly/store', [AttendanceController::class,'storeAttendance'])->name('teacher.attendance.store');

    Route::get('/attendance/history', 
        [AttendanceController::class, 'history']
    )->name('teacher.attendance.history');

    


    Route::get('/attendance/history/{paper}/{month}/{year}', 
        [AttendanceController::class, 'show']
    )->name('teacher.attendance.history.show');

    Route::get('daily/attendance/fill/{assignment}/{month}/{year}', [DailyAttendanceController::class,'fillAttendance'])->name('teacher.daily.attendance.fill');
    Route::post('attendance/daily/store', [DailyAttendanceController::class,'store'])->name('teacher.attendance.daily.store');
    

// IA Marks

    Route::get('iaAttendance/', [IaController::class,'pendingList'])->name('teacher.iaAttendance.pendingList');
     Route::get('monthly/iaAttendance/fill/{assignment}', [IaController::class,'fillAttendance'])->name('teacher.iaAttendance.fill');
    Route::post('iaMarks/store', [IaController::class,'storeAttendance'])->name('teacher.iaMarks.store');

    Route::get('/iaMarks/history', 
        [IaController::class, 'history']
    )->name('teacher.iaMarks.history');

    Route::get('/iaMarks/history/{paper}/{semester}/{section}', 
        [IaController::class, 'show']
    )->name('teacher.iaMarks.view');

    // Route::get('daily/iaAttendance/fill/{assignment}/{month}/{year}', [IaController::class,'fillAttendance'])->name('teacher.daily.iaAttendance.fill');
    // Route::post('iaAttendance/daily/store', [IaController::class,'store'])->name('teacher.iaAttendance.daily.store');
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

    Route::get('admin/attendance-monitoring', 
            [AdminController::class, 'attendanceMonitorig']
        )->name('admin.attendance.monitoring');

    Route::get(
        '/admin/student-attendance-master',
        [AdminAttendanceController::class, 'index']
    )->name('admin.attendance.master');

    Route::get(
        '/admin/student-attendance-master/excel/{month}/{year}',
        [AdminAttendanceController::class, 'exportExcel']
    )->name('admin.attendance.master.excel');

});
Route::middleware('auth:admin')->group(function(){
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

        Route::middleware('registration.open')->get('registration/{student}',[RegistrationController::class,'index']);
           Route::post('registration/{student}',
            [RegistrationController::class,'store']
        )->name('registration.store');
    });
});

Route::middleware('auth:admin')
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // List all registration windows
        Route::get('/registration-windows',
            [RegistrationWindowController::class, 'index']
        )->name('registration-window.index');

        // Show create form
        Route::get('/registration-windows/create',
            [RegistrationWindowController::class, 'create']
        )->name('registration-window.create');

        // Store new window
        Route::post('/registration-windows',
            [RegistrationWindowController::class, 'store']
        )->name('registration-window.store');

        // Show edit form
        Route::get('/registration-windows/{window}/edit',
            [RegistrationWindowController::class, 'edit']
        )->name('registration-window.edit');

        // Update window
        Route::put('/registration-windows/{window}',
            [RegistrationWindowController::class, 'update']
        )->name('registration-window.update');

        // Open / Close window
        Route::patch('/registration-windows/{window}/toggle',
            [RegistrationWindowController::class, 'toggle']
        )->name('registration-window.toggle');

    });
    Route::prefix('admin')->group(function () {
        Route::resource('departments', App\Http\Controllers\Admin\DepartmentController::class)->names([
            'index'   => 'admin.departments.index',
            'create'  => 'admin.departments.create',
            'store'   => 'admin.departments.store',
            'show'    => 'admin.departments.show',
            'edit'    => 'admin.departments.edit',
            'update'  => 'admin.departments.update',
            // 'destroy' => 'admin.departments.destroy',
        ]);
    });






















