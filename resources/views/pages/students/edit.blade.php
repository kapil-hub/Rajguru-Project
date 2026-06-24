@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Edit Student</h2>
        <a href="{{ route('students.show', $student) }}"
           class="px-4 py-2 border rounded-lg hover:bg-gray-50">
            ← Back
        </a>
    </div>
    <!-- Attendance Warning Modal -->
    <div id="attendanceModal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">

        <div class="bg-white w-[420px] rounded-xl shadow-xl p-6 text-center">

    <h2 class="text-xl font-semibold text-purple-600 mb-4">
        Attendance Found
    </h2>

    <p class="text-gray-600 mb-2">
        Attendance exists for the following papers and will be deleted:
    </p>

    <ul id="attendancePapers"
        class="text-sm text-red-600 mb-6 list-disc text-left pl-6"></ul>

    <div class="flex justify-center gap-4">

        <button id="cancelBtn"
            class="px-5 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg">
            Cancel
        </button>

        <button id="confirmBtn"
            class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
            Yes Delete
        </button>

    </div>

</div>

    </div>
    <form method="POST" id="studentForm" action="{{ route('students.update', $student) }}"
          class="bg-white rounded-xl shadow p-6 space-y-8">
        @csrf
        @method('PUT')
        <input type="hidden" id="confirm_delete_attendance" name="confirm_delete_attendance" value="0">
        <!-- ================= BASIC INFORMATION ================= -->
        <section>
            <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Student Name</label>
                    <input name="name" value="{{ $student->name }}" required
                           class="border rounded-lg p-2 w-full">
                    @error('name')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Control Number</label>
                    <input value="{{ $student->control_number }}" disabled
                           class="border rounded-lg p-2 w-full bg-gray-100">
                    @error('control_number')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Admission Academic Year</label>
                    <input name="admission_academic_year"
                           value="{{ $student->admission_academic_year }}" required
                           class="border rounded-lg p-2 w-full">

                    @error('admission_academic_year')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Student Email</label>
                    <input name="email" type="email"
                           value="{{ $student->email }}" required
                           class="border rounded-lg p-2 w-full">
                    @error('email')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Mobile Number</label>
                    <input name="mobile" value="{{ $student->mobile }}" required
                           class="border rounded-lg p-2 w-full">
                    @error('mobile')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <!-- ================= ACADEMIC INFORMATION ================= -->
        <section>
            <h3 class="text-lg font-semibold mb-4">Academic Information</h3>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Roll Number</label>
                    <input name="roll_number"
                           value="{{ optional($student->academic)->roll_number }}"
                           required class="border rounded-lg p-2 w-full">
                    @error('roll_number')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">College Roll Number</label>
                    <input name="college_roll_number"
                           value="{{ optional($student->academic)->college_roll_number }}"
                           required class="border rounded-lg p-2 w-full">
                    @error('college_roll_number')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department</label>
                    <select name="department_id" required
                            class="border rounded-lg p-2 w-full">
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}"
                                @selected(optional($student->academic)->department_id == $dept->id)>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Course</label>
                    <select name="course_id" required
                            class="border rounded-lg p-2 w-full">
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}"
                                @selected(optional($student->academic)->course_id == $course->id)>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('course_id')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Current Semester</label>
                    <input name="current_semester"
                           value="{{ optional($student->academic)->current_semester }}"
                           required class="border rounded-lg p-2 w-full">
                    @error('current_semester')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Section</label>
                    <input name="section"
                           value="{{ optional($student->academic)->section }}"
                           required class="border rounded-lg p-2 w-full">
                    @error('section')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        <!-- ================= PARENT INFORMATION ================= -->
        <section>
            <h3 class="text-lg font-semibold mb-4">Parent / Enrollment Details</h3>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Father Name</label>
                    <input name="father_name"
                           value="{{ optional($student->enrolDetails)->father_name }}"
                           required class="border rounded-lg p-2 w-full">
                    @error('father_name')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Mother Name</label>
                    <input name="mother_name"
                           value="{{ optional($student->enrolDetails)->mother_name }}"
                           required class="border rounded-lg p-2 w-full">

                    @error('mother_name')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Parent Contact Number</label>
                    <input name="parents_contact_number"
                           value="{{ optional($student->enrolDetails)->parents_contact_number }}"
                           required class="border rounded-lg p-2 w-full">

                    @error('parents_contact_number')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Parent Email</label>
                    <input name="parents_email_id" type="email"
                           value="{{ optional($student->enrolDetails)->parents_email_id }}"
                           required class="border rounded-lg p-2 w-full">
                    @error('parents_email_id')
                        <div class="text-lg font-semibold text-red-700 mb-3">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </section>

        
        <!-- ================= STUDENT PAPERS ================= -->
            <section>
                <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                    📚 Student Papers
                </h3>

                <!-- CURRENT SEMESTER -->
                <div class="bg-gray-50 rounded-xl p-4 mb-6">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-700">Current Semester Papers</h4>

                        <button type="button"
                            onclick="addPaper(false)"
                            class="text-sm flex items-center gap-1 text-indigo-600 hover:underline">
                            ➕ Add Paper
                        </button>
                    </div>

                    <div id="current-papers" class="space-y-3">
                        @foreach($student->papers->where('is_backlog', false) as $index => $paper)
                            @include('pages.students.partials.paper-row', [
                                'index' => $index,
                                'paper' => $paper,
                                'allPapers' => $allPapers,
                                'isBacklog' => false
                            ])
                        @endforeach
                    </div>
                </div>

                <!-- BACKLOG PAPERS -->
                <div class="bg-red-50 rounded-xl p-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-red-700">⚠ Backlog Papers</h4>

                        <button type="button"
                            onclick="addPaper(true)"
                            class="text-sm flex items-center gap-1 text-red-600 hover:underline">
                            ➕ Add Backlog
                        </button>
                    </div>

                    <div id="backlog-papers" class="space-y-3">
                        @foreach($student->papers->where('is_backlog', true) as $index => $paper)
                            @include('pages.students.partials.paper-row', [
                                'index' => 'b'.$index,
                                'paper' => $paper,
                                'allPapers' => $allPapers,
                                'isBacklog' => true
                            ])
                        @endforeach
                    </div>
                </div>
            </section>
            <!-- ================= ACTIONS ================= -->
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('students.show', $student) }}"
               class="px-4 py-2 border rounded-lg">
                Cancel
            </a>
            <button class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Update Student
            </button>
        </div>
    </form>

    @if(auth('admin')->check() && !empty($academicHistory))
        <!-- ACADEMIC HISTORY (ADMIN ONLY) -->
        <div class="bg-white rounded-xl shadow p-6 mt-6 border-t-4 border-indigo-600">
            <h3 class="font-bold text-xl mb-4 text-indigo-700 flex items-center gap-2">
                📜 Academic History (All Semesters)
            </h3>
            
            <div class="space-y-6">
                @foreach($academicHistory as $sem => $history)
                    <div class="border rounded-xl p-4 bg-gray-50 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between border-b pb-3 mb-4">
                            <div>
                                <span class="text-lg font-bold text-gray-800">Semester {{ $sem }}</span>
                                @if($history['is_current'])
                                    <span class="ml-2 inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-800">
                                        Current Active
                                    </span>
                                @else
                                    <span class="ml-2 inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-200 text-gray-800">
                                        Historical
                                    </span>
                                @endif
                            </div>
                            <div class="mt-2 md:mt-0 text-sm text-gray-600 flex flex-wrap gap-x-4">
                                <div><strong>Course:</strong> {{ $history['academic']->course_name ?? '-' }}</div>
                                <div><strong>Section:</strong> {{ $history['academic']->section ?? '-' }}</div>
                                <div><strong>Acad Year:</strong> {{ $history['academic']->current_academic_year ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 mb-4 text-sm text-gray-600">
                            <div><strong>Exam Roll No:</strong> {{ $history['academic']->roll_number ?? '-' }}</div>
                            <div><strong>College Roll No:</strong> {{ $history['academic']->college_roll_number ?? '-' }}</div>
                        </div>

                        <h4 class="font-semibold text-sm mb-2 text-gray-700">Papers Registered:</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border bg-white rounded-lg overflow-hidden">
                                <thead class="bg-gray-100 text-gray-700 font-medium">
                                    <tr>
                                        <th class="p-2 text-left">Code</th>
                                        <th class="p-2 text-left">Paper Name</th>
                                        <th class="p-2 text-left">Type</th>
                                        <th class="p-2 text-center">Status</th>
                                        <th class="p-2 text-center">Year</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y text-gray-600">
                                    @forelse($history['papers'] as $paper)
                                        <tr class="{{ $paper->is_backlog ? 'bg-red-50 text-red-800' : '' }}">
                                            <td class="p-2 font-mono text-xs">{{ $paper->paper_code }}</td>
                                            <td class="p-2">
                                                {{ $paper->paper_name }}
                                                @if($paper->is_backlog)
                                                    <span class="ml-2 inline-flex items-center text-xs font-bold px-1.5 py-0.2 rounded bg-red-200 text-red-800">
                                                        Backlog
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="p-2 text-xs">{{ $paper->paper_type }}</td>
                                            <td class="p-2 text-center text-xs">
                                                @if($paper->is_backlog)
                                                    <span class="text-red-600">Backlog</span>
                                                @else
                                                    <span class="text-green-600">Regular</span>
                                                @endif
                                            </td>
                                            <td class="p-2 text-center text-xs">{{ $paper->academic_year }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-4 text-center text-gray-400 text-sm">
                                                No papers registered in this semester.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>


<script>
let paperIndex = 1000;

function addPaper(isBacklog = false) {

    let container = isBacklog
        ? document.getElementById('backlog-papers')
        : document.getElementById('current-papers');

    let semesterField = isBacklog
        ? `<div class="w-32">
                <input type="number"
                       name="papers[${paperIndex}][semester]"
                       placeholder="Backlog Sem"
                       class="border rounded-lg p-2 w-full">
           </div>`
        : '';

    let html = `
        <div class="flex items-center gap-3 bg-white p-3 rounded-lg shadow-sm">
            <input type="hidden"
                   name="papers[${paperIndex}][is_backlog]"
                   value="${isBacklog ? 1 : 0}">

            <div class="flex-1">
                <select name="papers[${paperIndex}][paper_id]"
                        class="border rounded-lg p-2 w-full">
                    <option value="">Select Paper</option>
                    @foreach($allPapers as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->name }} ({{ $p->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            ${semesterField}

            <button type="button"
                    onclick="this.closest('.flex').remove()"
                    class="text-red-600 text-xl">🗑️</button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    paperIndex++;
}
</script>
<script>
function initPaperSelect(container = document) {

    container.querySelectorAll('.paper-select').forEach(select => {

        if (select.tomselect) return; // prevent double init

        let ts = new TomSelect(select, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            onChange(value) {
                let option = select.querySelector(`option[value="${value}"]`);
                let semester = option?.dataset.semester || '';

                let row = select.closest('.paper-row');
                let semBox = row.querySelector('.semester-box');

                if (semBox) {
                    semBox.value = semester;
                }

                validateDuplicatePapers();
            }
        });
    });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    initPaperSelect();
});
</script>
<script>
function validateDuplicatePapers() {

    let selected = [];
    let hasDuplicate = false;

    document.querySelectorAll('.paper-select').forEach(sel => {
        if (sel.value) {
            if (selected.includes(sel.value)) {
                hasDuplicate = true;
                sel.tomselect.control.classList.add('border-red-500');
            } else {
                selected.push(sel.value);
                sel.tomselect.control.classList.remove('border-red-500');
            }
        }
    });

    if (hasDuplicate) {
        alert('Same paper cannot be assigned twice to a student.');
    }
}

function removePaper(btn) {
    btn.closest('.paper-row').remove();
    validateDuplicatePapers();
}
</script>


<script>

let allowSubmit = false;

document.getElementById("studentForm").addEventListener("submit", function(e){

    if(allowSubmit) return;

    e.preventDefault();

    let form = this;
    let formData = new FormData(form);

    fetch(form.action,{
        method:"POST",
        headers:{
            "X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content,
            "Accept":"application/json",
            "X-Requested-With":"XMLHttpRequest"
        },
        body:formData
    })
    .then(res => res.json())
    .then(data => {

        if(data.attendance_found){

            let list = document.getElementById("attendancePapers");
            list.innerHTML = "";

            data.papers.forEach(function(paper){
                let li = document.createElement("li");
                li.textContent = paper;
                list.appendChild(li);
            });

            document.getElementById("attendanceModal").classList.remove("hidden");
            document.getElementById("attendanceModal").classList.add("flex");

        }else{

            allowSubmit = true;
            form.submit(); // normal form submit

        }

    })
    .catch(err => console.error(err));

});


document.getElementById("confirmBtn").addEventListener("click", function(){

    document.getElementById("confirm_delete_attendance").value = 1;

    allowSubmit = true;

    document.getElementById("studentForm").submit();
});


document.getElementById("cancelBtn").addEventListener("click", function(){

    document.getElementById("attendanceModal").classList.add("hidden");

});

</script>

@endsection
