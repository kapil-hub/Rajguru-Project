<?php

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Paper;
use App\Models\StudentPaper;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;


class RegistrationController extends Controller
{
    use AuthorizesRequests;

    const MAX_PAPER_TO_SELECT = [
        "DSC_1",
        "DSC_2",
        "DSC_3",
        "GE",
        "AEC",
        "SEC",
        "VAC"
        ];

    const ALTERNATIVE_SEMESTER = [
            "I"=>1,
            "II"=>2,
            "III"=>3,
            "IV"=>4,
            "V"=>5,
            "VI"=>6,
            "VII"=>7,
            "VIII"=>8,
            1=>"I",
            2=>"II",
            3=>"III",
            4=>"IV",
            5=>"V",
            6=>"VI",
            7=>"VII",
            8=>"VIII"
        ];

    public function index(Student $student){
        $this->authorize('view', $student);

        $deptId   = $student->academic->department_id;
        $courseId = $student->academic->course_id;
        $semester = $student->academic->current_semester;

        $papers = Paper::where('semester', $semester)
            ->orWhere('semester',self::ALTERNATIVE_SEMESTER[$semester])
            ->where(function ($query) use ($deptId, $courseId) {

                // Allow all NON-GE papers from same dept & course
                $query->where(function ($q) use ($deptId, $courseId) {
                    $q->whereNotIn('paper_type', ['GE','AEC','SEC','VAC'])
                    ->where('dept_id', $deptId)
                    ->where('course_id', $courseId);
                })

                // OR allow GE papers from OTHER dept or course
                ->orWhere(function ($q) use ($deptId, $courseId) {
                    $q->whereIn('paper_type', ['GE','AEC','SEC','VAC'])
                    ->where(function ($x) use ($deptId, $courseId) {
                        $x->where('dept_id', '!=', $deptId)
                        ->where('dept_id',15)
                        ->orWhere('course_id', '!=', $courseId);
                    });
                });
            })
            ->get()
            ->groupBy('paper_type');
        return view('pages.registration.index', [
                'student' => $student,
                'papers'  => $papers, // grouped by paper_type
            ]);
    }

    public function store(Request $request, Student $student)
{

    $this->authorize('view', $student);


    $validated = $request->validate([
        'papers.DSC_1' => 'required|integer|different:papers.DSC_2,papers.DSC_3',
        'papers.DSC_2' => 'required|integer|different:papers.DSC_1,papers.DSC_3',
        'papers.DSC_3' => 'required|integer|different:papers.DSC_1,papers.DSC_2',
        'papers.GE'    => 'required|integer',
        'papers.AEC'   => 'required|integer',
        'papers.SEC'   => 'required|integer',
        'papers.VAC'   => 'required|integer',
    ]);


    $paperIds = array_values($validated['papers']);

    if (count($paperIds) !== count(array_unique($paperIds))) {
        return back()
            ->withErrors(['papers' => 'You cannot select the same paper more than once.'])
            ->withInput();
    }


    $semester = $student->academic->current_semester;
    $academicYear = $student->academic->current_academic_year;


    DB::transaction(function () use ($student, $paperIds, $semester, $academicYear) {

        // Optional: prevent duplicate submission
        StudentPaper::where('student_user_id', $student->id)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->delete();

        foreach ($paperIds as $paperId) {
            StudentPaper::create([
                'student_user_id' => $student->id,
                'paper_master_id' => $paperId,
                'semester'        => $semester,
                'academic_year'   => $academicYear,
            ]);
        }
    });


    return redirect()
        ->route('students.show', $student->id)
        ->with('success', 'Paper registration completed successfully.');
}

}
