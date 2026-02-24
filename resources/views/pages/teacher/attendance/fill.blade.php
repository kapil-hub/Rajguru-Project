@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Fill Attendance</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $assignment->course->name }} •
            Semester {{ $assignment->semester->name }} •
            Section {{ $assignment->section }} •
            {{ $assignment->paperMaster->name }}
        </p>
        Month:
        <span class="font-semibold">
            {{ \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->format('F Y') }}
        </span>
    </div>

    
        {{-- WORKING DAYS CARD --}}
        <div class="bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
            <h2 class="text-lg font-semibold mb-4">Classes Held</h2>
            <form action="{{ route('teacher.monthly.attendance.fill.downloadTemplate',[
                                    'assignment' => $assignment->id,
                                    'month' => $month,
                                    'year' => $year
                                ]) }}" method="POST">
                @csrf
                <input type= "hidden" name="student_obj" value="{{ $students }}">
                <div class="flex flex-wrap gap-4 items-end">

                    @if($assignment->is_lecture)
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Lecture</label>
                        <input type="number" name ="lecture_days" id="lecture_days"
                            class="w-40 px-3 py-2 border rounded-lg"
                            min="0" max="31" required>
                    </div>
                    @endif

                    @if($assignment->is_tute)
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Tutorial</label>
                        <input type="number" name = "tute_days" id="tute_days"
                            class="w-40 px-3 py-2 border rounded-lg"
                            min="0" max="31" required>
                    </div>
                    @endif

                    @if($assignment->is_practical)
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Practical</label>
                        <input type="number" name="practical_days" id="practical_days"
                            class="w-40 px-3 py-2 border rounded-lg"
                            min="0" max="31" required>
                    </div>
                    @endif

                    <button type="button"
                            onclick="addAttendanceFields()"
                            id="showStudentsBtn"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Show Students
                    </button>
                    <label
                        class="ml-4 flex items-center gap-2 border border-gray-300 rounded-md px-3 py-2 cursor-pointer"
                    >
                        <input
                            type="checkbox"
                            id="ffff"
                            class="accent-blue-600"
                        >
                        <span class="text-sm font-medium">Bulk Import</span>
                    </label>
                  
                </div>
         
            <div class="bg-white rounded-xl shadow border border-indigo-200 mb-10 hidden" id="bulkSection" style="margin-top:25px">

                <div class="px-6 py-4 border-b bg-indigo-50 rounded-t-xl">
                    <h2 class="text-lg font-semibold text-indigo-800 flex items-center gap-2">
                        📥 Bulk Attendance Import
                    </h2>
                    <p class="text-sm text-indigo-600 mt-1">
                        Recommended for faster monthly attendance entry
                    </p>
                </div>

                <div class="p-6 space-y-6">

                    {{-- STEP 1 --}}
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            1
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">Download Excel Template</h4>
                            <p class="text-sm text-gray-500 mb-2">
                                Auto-filled with students & working days
                            </p>

                            
                                <input type="hidden" name="student_obj" value="{{ $students }}">

                                <button type="submit" class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                                    ⬇ Download Template
                                </button>
                            </form>
                        </div>
                    </div>
              
                    <hr>

                    {{-- STEP 2 --}}
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            2
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-800">Fill Attendance in Excel</h4>
                            <ul class="text-sm text-gray-500 list-disc ml-5 mt-1">
                                <li>Edit only <b>Present (P)</b> columns</li>
                                <li>Do not modify student names</li>
                                <li>Present ≤ Working Days</li>
                            </ul>
                        </div>
                    </div>

                    <hr>

                    {{-- STEP 3 --}}
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                            3
                        </div>
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-800 mb-2">Upload Filled Excel</h4>

                            <form action="{{ route('attendance.import') }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="flex flex-col sm:flex-row gap-4">
                                @csrf

                                {{-- meta --}}
                                <input type="hidden" name="paper_master_id" value="{{ $assignment->paper_master_id }}">
                                <input type="hidden" name="course_id" value="{{ $assignment->course_id }}">
                                <input type="hidden" name="semester_id" value="{{ $assignment->semester_id }}">
                                <input type="hidden" name="section" value="{{ $assignment->section }}">
                                <input type="hidden" name="month" value="{{ $month }}">
                                <input type="hidden" name="year" value="{{ $year }}">

                                <input type="file"
                                    name="file"
                                    accept=".xlsx"
                                    required
                                    class="block w-full sm:w-80 text-sm
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-lg file:border-0
                                    file:bg-indigo-50 file:text-indigo-700">

                                <button class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                    🚀 Import Attendance
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf

        {{-- HIDDEN META --}}
        <input type="hidden" name="course_id" value="{{ $assignment->course_id }}">
        <input type="hidden" name="semester_id" value="{{ $assignment->semester_id }}">
        <input type="hidden" name="section" value="{{ $assignment->section }}">
        <input type="hidden" name="paper_master_id" value="{{ $assignment->paper_master_id }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        {{-- ATTENDANCE TABLE (HIDDEN INITIALLY) --}}
        <div id="attendanceTable" class="hidden bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">Exam Roll Number</th>
                        <th class="px-4 py-3">College Roll Number</th>
                        <th class="px-6 py-3 text-left">Student Name</th>

                        @if($assignment->is_lecture)
                            <th class="px-6 py-3 text-center">Lecture<br><span class="text-xs">Classes Held / Classes Attended</span></th>
                        @endif

                        @if($assignment->is_tute)
                            <th class="px-6 py-3 text-center">Tutorial<br><span class="text-xs">Classes Held / Classes Attended</span></th>
                        @endif

                        @if($assignment->is_practical)
                            <th class="px-6 py-3 text-center">Practical<br><span class="text-xs">Classes Held / Classes Attended</span></th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y">
                @foreach($students as $i => $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $s['academic']['roll_number'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2">{{ $s['academic']['college_roll_number'] ?? 'N/A' }}</td>
                        <td class="px-6 py-2 font-medium">{{ $s->name }}</td>

                        {{-- LECTURE --}}
                        @if($assignment->is_lecture)
                        <td class="px-6 py-2 text-center space-x-2">
                            <input type="number"
                                   name="attendance[{{ $s->id }}][lecture][working]"
                                   class="lecture-working w-16 text-center border rounded-lg"
                                   min="0" max="31">
                            <input type="number"
                                   name="attendance[{{ $s->id }}][lecture][present]"
                                    value="{{ $oldAttendences[$s->id]['lecture_present_days'] ?? '' }}"
                                   class="w-16 text-center border rounded-lg"
                                   min="0" max="31">
                        </td>
                        @endif

                        {{-- TUTE --}}
                        @if($assignment->is_tute)
                        <td class="px-6 py-2 text-center space-x-2">
                            <input type="number"
                                   name="attendance[{{ $s->id }}][tute][working]"
                                   class="tute-working w-16 text-center border rounded-lg"
                                   min="0" max="31">
                            <input type="number"
                                   name="attendance[{{ $s->id }}][tute][present]"
                                   value="{{ $oldAttendences[$s->id]['tute_present_days'] ?? '' }}"
                                   class="w-16 text-center border rounded-lg"
                                   min="0" max="31">
                        </td>
                        @endif

                        {{-- PRACTICAL --}}
                        @if($assignment->is_practical)
                        <td class="px-6 py-2 text-center space-x-2">
                            <input type="number"
                                   name="attendance[{{ $s->id }}][practical][working]"
                                   class="practical-working w-16 text-center border rounded-lg"
                                   min="0" max="31">
                            <input type="number"
                                   name="attendance[{{ $s->id }}][practical][present]"
                                   value="{{ $oldAttendences[$s->id]['practical_present_days'] ?? '' }}"
                                   class="w-16 text-center border rounded-lg"
                                   min="0" max="31">
                        </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- SAVE BUTTON --}}
        <div id="saveBtn" class="hidden flex justify-end mt-6">
            <button class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Save Attendance
            </button>
        </div>

    </form>
</div>

{{-- JAVASCRIPT --}}
<script>
function addAttendanceFields() {

    const lecture = document.getElementById('lecture_days')?.value;
    const tute = document.getElementById('tute_days')?.value;
    const practical = document.getElementById('practical_days')?.value;

    if (lecture !== undefined) {
        document.querySelectorAll('.lecture-working').forEach(el => el.value = lecture);
    }

    if (tute !== undefined) {
        document.querySelectorAll('.tute-working').forEach(el => el.value = tute);
    }

    if (practical !== undefined) {
        document.querySelectorAll('.practical-working').forEach(el => el.value = practical);
    }

    document.getElementById('attendanceTable').classList.remove('hidden');
    document.getElementById('saveBtn').classList.remove('hidden');
    document.getElementById("bulkSection").classList.add("hidden");
    document.getElementById("ffff").checked = false;
}


document.addEventListener('input', function (e) {

    // Only react on "present" inputs
    if (!e.target.name?.includes('[present]')) return;

    const presentInput = e.target;
    const td = presentInput.closest('td');
    const workingInput = td.querySelector('input[name*="[working]"]');

    if (!workingInput) return;

    const working = parseInt(workingInput.value || 0);
    const present = parseInt(presentInput.value || 0);

    if (present > working) {
        alert('Present days cannot be more than working days');
        presentInput.value = working;
        presentInput.focus();
    }
});

document.getElementById("ffff").addEventListener("change", function () {
    const bulkSection = document.getElementById("bulkSection");

    if (this.checked) {
        bulkSection.classList.remove("hidden");
    } else {
        bulkSection.classList.add("hidden");
    }
});
</script>
@endsection
