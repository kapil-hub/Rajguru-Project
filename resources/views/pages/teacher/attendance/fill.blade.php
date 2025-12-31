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

    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf

        {{-- HIDDEN META --}}
        <input type="hidden" name="course_id" value="{{ $assignment->course_id }}">
        <input type="hidden" name="semester_id" value="{{ $assignment->semester_id }}">
        <input type="hidden" name="section" value="{{ $assignment->section }}">
        <input type="hidden" name="paper_master_id" value="{{ $assignment->paper_master_id }}">
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">

        {{-- WORKING DAYS CARD --}}
        <div class="bg-white rounded-xl shadow border p-6 mb-6">
            <h2 class="text-lg font-semibold mb-4">Working Days</h2>

            <div class="flex flex-wrap gap-4 items-end">

                @if($assignment->is_lecture)
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Lecture</label>
                    <input type="number" id="lecture_days"
                           class="w-40 px-3 py-2 border rounded-lg"
                           min="0" max="31">
                </div>
                @endif

                @if($assignment->is_tute)
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tute</label>
                    <input type="number" id="tute_days"
                           class="w-40 px-3 py-2 border rounded-lg"
                           min="0" max="31">
                </div>
                @endif

                @if($assignment->is_practical)
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Practical</label>
                    <input type="number" id="practical_days"
                           class="w-40 px-3 py-2 border rounded-lg"
                           min="0" max="31">
                </div>
                @endif

                <button type="button"
                        onclick="addAttendanceFields()"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Add Attendance Fields
                </button>
            </div>
        </div>

        {{-- ATTENDANCE TABLE (HIDDEN INITIALLY) --}}
        <div id="attendanceTable" class="hidden bg-white rounded-xl shadow border overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-6 py-3 text-left">Student</th>

                        @if($assignment->is_lecture)
                            <th class="px-6 py-3 text-center">Lecture<br><span class="text-xs">WD / P</span></th>
                        @endif

                        @if($assignment->is_tute)
                            <th class="px-6 py-3 text-center">Tute<br><span class="text-xs">WD / P</span></th>
                        @endif

                        @if($assignment->is_practical)
                            <th class="px-6 py-3 text-center">Practical<br><span class="text-xs">WD / P</span></th>
                        @endif
                    </tr>
                </thead>

                <tbody class="divide-y">
                @foreach($students as $i => $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $i + 1 }}</td>
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
</script>
@endsection
