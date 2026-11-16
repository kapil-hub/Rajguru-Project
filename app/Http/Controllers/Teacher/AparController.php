<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\AparSubmission;
use App\Support\AparAcademicYear;
use App\Support\AparPdfExporter;
use Illuminate\Http\Request;

class AparController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'academic_year' => ['nullable', 'regex:/^\d{4}-\d{4}$/'],
            'status' => ['nullable', 'in:draft,submitted,changes_requested,reviewed'],
        ]);
        $facultyId = auth('teacher')->id();

        $submissions = AparSubmission::query()
            ->where('faculty_user_id', $facultyId)
            ->when($filters['academic_year'] ?? null, fn ($query, $year) => $query->where('academic_year', $year))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('academic_year')
            ->orderByDesc('updated_at')
            ->get();

        $years = AparSubmission::query()
            ->where('faculty_user_id', $facultyId)
            ->select('academic_year')
            ->distinct()
            ->orderByDesc('academic_year')
            ->pluck('academic_year');

        return view('pages.teacher.apar', [
            'submissions' => $submissions,
            'years' => $years,
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        $existingYears = AparSubmission::query()
            ->where('faculty_user_id', auth('teacher')->id())
            ->pluck('academic_year')
            ->all();

        return view('pages.teacher.apar.create', [
            'availableYears' => array_values(array_diff(AparAcademicYear::options(), $existingYears)),
            'currentAcademicYear' => AparAcademicYear::current(),
        ]);
    }

    public function selectAcademicYear(Request $request)
    {
        $data = $request->validate([
            'academic_year' => ['required', 'in:'.implode(',', AparAcademicYear::options())],
        ]);
        $submission = AparSubmission::query()
            ->where('faculty_user_id', auth('teacher')->id())
            ->where('academic_year', $data['academic_year'])
            ->first();

        if ($submission) {
            return redirect()->route('teacher.apar.resume', $submission);
        }

        return redirect()->route('teacher.apar.create.form', ['academicYear' => $data['academic_year']]);
    }

    public function createForm(string $academicYear)
    {
        abort_unless(in_array($academicYear, AparAcademicYear::options(), true), 404);

        $submission = AparSubmission::query()
            ->where('faculty_user_id', auth('teacher')->id())
            ->where('academic_year', $academicYear)
            ->first();

        if ($submission) {
            return redirect()->route('teacher.apar.resume', $submission);
        }

        return view('pages.teacher.apar.form', [
            'academicYear' => $academicYear,
            'submissionId' => null,
        ]);
    }

    public function resume(AparSubmission $apar)
    {
        abort_unless((int) $apar->faculty_user_id === (int) auth('teacher')->id(), 403);

        return view('pages.teacher.apar.form', [
            'academicYear' => $apar->academic_year,
            'submissionId' => $apar->id,
        ]);
    }

    public function pdf(AparSubmission $apar, AparPdfExporter $pdf)
    {
        abort_unless((int) $apar->faculty_user_id === (int) auth('teacher')->id(), 403);

        return $pdf->download($apar);
    }
}
