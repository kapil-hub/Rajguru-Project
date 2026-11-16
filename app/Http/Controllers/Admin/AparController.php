<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AparSubmission;
use App\Models\Departments;
use App\Support\AparFormSchema;
use App\Support\AparPdfExporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AparController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'academic_year' => ['nullable', 'regex:/^\d{4}-\d{4}$/'],
            'status' => ['nullable', 'in:draft,submitted,changes_requested,reviewed'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        $submissions = AparSubmission::query()
            ->with(['faculty.department'])
            ->when(empty($filters['status']), fn ($query) => $query->where('status', '!=', 'draft'))
            ->when($filters['academic_year'] ?? null, fn ($query, $year) => $query->where('academic_year', $year))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['department_id'] ?? null, fn ($query, $departmentId) => $query->whereHas(
                'faculty',
                fn ($facultyQuery) => $facultyQuery->where('department_id', $departmentId)
            ))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->whereHas(
                'faculty',
                fn ($facultyQuery) => $facultyQuery->where(function ($facultyQuery) use ($search) {
                    $facultyQuery->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                })
            ))
            ->orderByRaw("CASE WHEN status = 'submitted' THEN 0 WHEN status = 'changes_requested' THEN 1 ELSE 2 END")
            ->orderByDesc('submitted_at')
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        $years = AparSubmission::query()
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('pages.admin.apar.index', [
            'submissions' => $submissions,
            'departments' => Departments::query()->orderBy('name')->get(),
            'years' => $years,
            'filters' => $filters,
        ]);
    }

    public function show(AparSubmission $apar)
    {
        $apar->load(['faculty.department', 'reviewer']);

        return view('pages.admin.apar.show', [
            'apar' => $apar,
            'steps' => AparFormSchema::steps(),
        ]);
    }

    public function review(Request $request, AparSubmission $apar)
    {
        abort_unless(in_array($apar->status, ['submitted', 'changes_requested'], true), 403);

        $data = $request->validate([
            'action' => ['required', 'in:reviewed,changes_requested'],
            'review_notes' => ['nullable', 'string', 'max:4000', 'required_if:action,changes_requested'],
            'hod_teaching_grade' => ['nullable', 'in:Good,Satisfactory,Not Satisfactory'],
            'hod_b2_grade' => ['nullable', 'in:Good,Satisfactory,Not Satisfactory'],
        ]);
        $overallGrade = $this->overallGrade(
            $data['hod_teaching_grade'] ?? null,
            $data['hod_b2_grade'] ?? null
        );

        $apar->update([
            'status' => $data['action'],
            'review_notes' => $data['review_notes'] ?? null,
            'hod_teaching_grade' => $data['hod_teaching_grade'] ?? null,
            'hod_b2_grade' => $data['hod_b2_grade'] ?? null,
            'hod_overall_grade' => $overallGrade,
            'reviewed_by' => auth('admin')->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('admin.apar.show', $apar)
            ->with('success', $data['action'] === 'reviewed'
                ? 'APAR marked as reviewed.'
                : 'APAR returned to the faculty member for changes.');
    }

    public function pdf(AparSubmission $apar, AparPdfExporter $pdf)
    {
        return $pdf->download($apar);
    }

    public function downloadEvidence(AparSubmission $apar, int $index)
    {
        if (! auth('admin')->check()) {
            abort_unless(
                auth('teacher')->check()
                    && (int) $apar->faculty_user_id === (int) auth('teacher')->id(),
                403
            );
        }

        $attachment = data_get($apar->form_data, 'enclosures.'.$index);
        abort_unless(is_array($attachment), 404);

        $path = $attachment['path'] ?? '';
        abort_unless(str_starts_with($path, 'apar/'.$apar->id.'/'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download(
            $path,
            basename($attachment['name'] ?? 'apar-evidence')
        );
    }

    private function overallGrade(?string $teachingGrade, ?string $b2Grade): ?string
    {
        if (! $teachingGrade || ! $b2Grade) {
            return null;
        }

        if ($teachingGrade === 'Good' && in_array($b2Grade, ['Good', 'Satisfactory'], true)) {
            return 'Good';
        }

        if ($teachingGrade === 'Satisfactory' && in_array($b2Grade, ['Good', 'Satisfactory'], true)) {
            return 'Satisfactory';
        }

        return 'Not Satisfactory';
    }
}
