<?php
use App\Http\Controllers\Api\PublicTimetableController;

Route::prefix('public')->group(function () {

    Route::get('/timetable/filters',[PublicTimetableController::class,'filters']);

    Route::get('/timetable',[PublicTimetableController::class,'timetable']);

    Route::get('/config',[PublicTimetableController::class,'config']);

});