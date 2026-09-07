<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\Departments;
use App\Models\Notification;
use App\Models\Paper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    private function activeStudent()
    {
        $student = auth('student')->user();

        if (!$student) {
            abort(403, 'Unauthorized');
        }

        if (!$student->academic || !in_array((string) $student->status, ['1', 'active', 'Active'], true)) {
            abort(403, 'Notifications are available only for active students.');
        }

        return $student->load(['academic', 'papers']);
    }

    public function view()
    {
        try {
            $student = $this->activeStudent();
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }

        $notifications = Notification::active()
            ->latest()
            ->get()
            ->filter(fn (Notification $notification) => $notification->matchesStudent($student));

        return view('pages.notification-view', compact('notifications'));
    }

    public function download(Notification $notification)
    {
        try {
            $student = $this->activeStudent();
        } catch (\Throwable $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }

        if (!$notification->is_active || !$notification->matchesStudent($student)) {
            abort(403, 'You are not allowed to download this notification.');
        }

        if (!Storage::disk('public')->exists($notification->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($notification->file_path);
    }

    public function index()
    {
        $notifications = Notification::latest()->paginate(15);

        return view('pages.admin.notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('pages.admin.notifications.create', [
            'departments' => Departments::orderBy('name')->get(),
            'courses' => Courses::orderBy('name')->get(),
            'papers' => Paper::where('status', 'Active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240',
            'target_all_active_students' => 'nullable|boolean',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'integer|exists:departments,id',
            'course_ids' => 'nullable|array',
            'course_ids.*' => 'integer|exists:courses,id',
            'paper_ids' => 'nullable|array',
            'paper_ids.*' => 'integer|exists:paper_master,id',
            'is_active' => 'nullable|boolean',
        ]);

        $targetAll = $request->boolean('target_all_active_students');

        if (!$targetAll && !$request->filled('department_ids') && !$request->filled('course_ids') && !$request->filled('paper_ids')) {
            return back()
                ->withInput()
                ->withErrors(['target' => 'Choose all active students or select at least one department, course, or paper.']);
        }

        $path = $request->file('file')->store('notifications', 'public');

        Notification::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $path,
            'target_all_active_students' => $targetAll,
            'department_ids' => $targetAll ? [] : array_map('intval', $validated['department_ids'] ?? []),
            'course_ids' => $targetAll ? [] : array_map('intval', $validated['course_ids'] ?? []),
            'paper_ids' => $targetAll ? [] : array_map('intval', $validated['paper_ids'] ?? []),
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth('admin')->id(),
        ]);

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification created successfully.');
    }

    public function destroy(Notification $notification)
    {
        if (Storage::disk('public')->exists($notification->file_path)) {
            Storage::disk('public')->delete($notification->file_path);
        }

        $notification->delete();

        return redirect()
            ->route('admin.notifications.index')
            ->with('success', 'Notification deleted successfully.');
    }
}
