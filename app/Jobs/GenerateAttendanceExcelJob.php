<?php

namespace App\Jobs;

use App\Exports\StudentAttendanceMasterExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;

class GenerateAttendanceExcelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $month;

    protected $year;

    protected $breakup;

    protected $userId;

    protected $fileName;

    public function __construct($month, $year, $breakup, $userId)
    {
        $this->month = $month;
        $this->year = $year;
        $this->breakup = $breakup;
        $this->userId = $userId;
        $this->fileName = "attendance_{$month}_{$year}_user_{$userId}.xlsx";
    }

    public function handle()
    {
        Excel::store(
            new StudentAttendanceMasterExport($this->month, $this->year, $this->breakup),
            'exports/'.$this->fileName,
            'public'
        );

        cache()->put(
            "excel_ready_user_{$this->userId}",
            $this->fileName,
            now()->addMinutes(30)
        );
    }
}
