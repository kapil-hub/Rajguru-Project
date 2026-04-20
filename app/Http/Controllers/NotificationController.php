<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    private function getFilePath()
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403, 'Unauthorized');
        }

        $semester = $student->academic->current_semester;
        

        $map = [
            2 => 'Choices_III_Sem_2026-27.pdf',
            4 => 'Choices_V_Sem_2026-27.pdf',
            6 => 'Choices_VII_Sem_2026-27.pdf',
        ];

        if (!isset($map[$semester])) {
            abort(403, 'Notifications available only for 2nd, 4th and 6th semester Students only');
        }

        return 'notifications/' . $map[$semester];
    }

    public function view()
    {
        try {
            $filePath = $this->getFilePath();
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }


        return view('pages.notification-view', compact('filePath'));
    }

    public function download()
    {
        try {
            $filePath = $this->getFilePath();
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }


        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($filePath);
    }
}
