@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Fill IA Marks</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $assignment->course->name }} •
            Semester {{ $assignment->semester->name }} •
            Section {{ $assignment->section }} •
           {{ $assignment->paperMaster->code }} - {{ $assignment->paperMaster->name }}
        </p>
    </div>
        {{-- Marks Breakup Table --}}

        <fieldset  class="overflow-x-auto">
             <legend>Marks Breakup</legend>
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Theory Total </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Theory</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">IA Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Class Test (IA)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Assignment (IA)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Attendance (IA)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Tutorial ( CA )</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Practical ( CA )</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Practical ( Written Exam )</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Practical ( Viva Voce  )</th>

                        <th class="px-4 py-3 text-left text-xs font-semibold">Grand Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                   @php 
                        $lectureB = lectureMarksBreakup($assignment->paperMaster->number_of_lectures);
                        $tuteB = tutorialMarksBreakup($assignment->paperMaster->number_of_tutorials);
                        $practicalB  = practicalMarksBreakup($assignment->paperMaster->number_of_practicals)
                   @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $lectureB["total"] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $lectureB["theory"] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $lectureB["ia"] }}</td>
                        <td class="px-4 py-3 font-medium">{{ $lectureB["ia_breakup"]["class_test"] }}</td>
                        <td class="px-4 py-3">{{ $lectureB["ia_breakup"]["assignment"] }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                {{$lectureB["ia_breakup"]["attendance"] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{$tuteB["ca"] }}</td>
                        <td class="px-4 py-3"> {{$practicalB["ca"]}}</td>
                        <td class="px-4 py-3"> {{$practicalB["written_exam"]}}</td>
                        <td class="px-4 py-3"> {{$practicalB["viva_voce"]}}</td>

                        <td class="px-4 py-3"><strong> {{ ($assignment->paperMaster->number_of_lectures + $assignment->paperMaster->number_of_tutorials +$assignment->paperMaster->number_of_practicals ) * 40}} </strong></td>

                    </tr>
            </tbody>
            </table>
        </fieldset >



    <form method="POST" action="{{ route('teacher.iaMarks.store') }}">
        @csrf

        {{-- HIDDEN META --}}
        <input type="hidden" name="course_id" value="{{ $assignment->course_id }}">
        <input type="hidden" name="semester_id" value="{{ $assignment->semester_id }}">
        <input type="hidden" name="section" value="{{ $assignment->section }}">
        <input type="hidden" name="paper_master_id" value="{{ $assignment->paper_master_id }}">




        {{-- Marks Filling TABLE (HIDDEN INITIALLY) --}}
        <div id="attendanceTable" class=" bg-white rounded-xl shadow border overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-8 py-3 text-left">Student</th>
                    <th class="px-16 py-3 text-center">Lecture Attendence<br><span class="text-xs">Total Lecture / Total Attended</span></th>
                    <th class="px-4 py-3 text-center">%</span></th>

                    <th class="px-16 py-3 text-center">Tutorial Attendence <br><span class="text-xs">Total Tutes / Total Attended</span></th>
                    <th class="px-4 py-3 text-center">%</span></th>
                    {{-- <th class="px-6 py-3 text-center">IA (Attendance) <br><span class="text-xs">TM / OM</span></th> --}}
                    @if($assignment->paperMaster->number_of_tutorials == 1)
                        <th class="px-4 py-3 text-center">Tutorial (CA Marks) <br> {{$tuteB["ca"]}}</th>
                    @endif
                    <th class="px-4 py-3 text-center">IA (Class Test Marks) <br>{{ $lectureB["ia_breakup"]["class_test"] }} </th>
                    <th class="px-4 py-3 text-center">IA (Assignment Marks) <br> {{ $lectureB["ia_breakup"]["assignment"] }}</th>
                    <th class="px-4 py-3 text-center">IA (Attendance Marks) <br>{{$lectureB["ia_breakup"]["attendance"] }}</th>
                    <th class="px-4 py-3 text-center">Total IA Marks</th>
                    @if($assignment->paperMaster->number_of_tutorials == 1)
                        <th class="px-4 py-3 text-center">Total Tutorial Marks</th>
                    @endif

                    <th class="px-4 py-3 text-center">Total Assisment</th>
    
                    </tr>
                </thead>

                <tbody class="mFilling divide-y">
                @foreach($students as $i => $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $i + 1 }}</td>
                        <td class="px-6 py-2 font-medium">{{ $s->name }}</td>
                        <td class="px-2 py-2 text-center space-x-2">
                            <div class="relative inline-flex items-center gap-4 out-of-wrapper focus-group">
                                <input type="number"
                                    class="w-16 text-center border rounded-lg focus:outline-none"
                                    value="{{ $oldAttendences[$s->id]['total_lecture_working_days'] ?? '' }}"
                                    readonly>

                                <input type="number"
                                    class="w-16 text-center border rounded-lg focus:outline-none"
                                    value="{{ $oldAttendences[$s->id]['total_lecture_present_days'] ?? '' }}"
                                    readonly>
                            </div>

                            @php
                                $p = $oldAttendences[$s->id]['lecture_percentage'] ?? 0;
                            @endphp
                            
                        </td>
                        <td>
                            <div class="w-16 text-center border rounded-lg
                                {{ $p >= 67 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $p }}%
                            </div>
                        </td>
                       
                        <td class="px-2 py-2 text-center space-x-2">
                            <div class="relative inline-flex items-center gap-4 out-of-wrapper focus-group">
                                <input type="number"
                                    class="w-16 text-center border rounded-lg focus:outline-none"
                                    value="{{ $oldAttendences[$s->id]['total_tute_working_days'] ?? '' }}"
                                    readonly>

                                <input type="number"
                                    class="w-16 text-center border rounded-lg focus:outline-none"
                                    value="{{ $oldAttendences[$s->id]['total_tute_present_days'] ?? '' }}"
                                    readonly>
                            </div>
                            @php
                                $pt =  $oldAttendences[$s->id]['tute_percentage'] ?? 0;
                            @endphp
                            
                        </td>
                        <td>
                            <div class="w-16 text-center border rounded-lg
                                {{ $pt >= 67 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $pt }}%
                            </div>
                        </td>

                        @if($assignment->paperMaster->number_of_tutorials == 1)  
                            <td class="px-6 py-2 text-center space-x-2">
                                <input type="number"
                                    name="tuteCaMarks[{{ $s->id }}]"
                                    value = "{{ $oldData[$s->id]["tute_ca"] ?? 0 }}"
                                    class="w-16 mark-input-tute text-center border rounded-lg"
                                    min="0" max="{{ $tuteB["ca"] }}">
                            </td>
                        @endif
                        <td class="px-6 py-2 text-center space-x-2">
                            <input type="number"
                                   name="iaclassTest[{{ $s->id }}]"
                                   value = "{{ $oldData[$s->id]["class_test"] ?? 0 }}"
                                   class="w-16 mark-input text-center border rounded-lg"
                                   min="0" max="{{ $lectureB["ia_breakup"]["class_test"] }}">
                        </td>
                        <td class="px-6 py-2 text-center space-x-2">
                            <input type="number"
                                   name="iaAssignment[{{ $s->id }}]"
                                   value = "{{ $oldData[$s->id]["assignment"] ?? 0 }}"
                                   class="w-16 mark-input text-center border rounded-lg"
                                   min="0" max="{{ $lectureB["ia_breakup"]["assignment"] }}">
                        </td>
                        <td class="px-6 py-2 text-center space-x-2">
                            @php
                                $lecturePercent = $oldAttendences[$s->id]['lecture_percentage'] ?? 0;
                                $lectureBreakup = lectureMarksBreakup($assignment->paperMaster->number_of_lectures);
                                $iaMarks = attendanceMarks($lecturePercent, $lectureBreakup["ia_breakup"]["attendance"]);
                            @endphp

                            
                            <input type="number"
                                name="iaAttendance[{{ $s->id }}]"
                                class="w-16 mark-input text-center border rounded-lg bg-gray-100"
                                value="{{ $iaMarks }}"
                                step="0.01"
                                readonly>
        
                        </td>
                        <td class="px-6 py-2 text-center">
                            <input type="number"
                                name="totalIaMarks[{{ $s->id }}]"
                                class="total-marks w-16 text-center border rounded-lg bg-yellow-50"
                                step="0.01"
                                readonly>
                        </td>
                        @if($assignment->paperMaster->number_of_tutorials == 1)
                            <td class="px-6 py-2 text-center">
                                <input type="number"
                                    name="totalTuteMarks[{{ $s->id }}]"
                                    class="total-tute-marks w-16 text-center border rounded-lg bg-yellow-50"
                                    step="0.01"
                                    readonly>
                            </td>
                        @endif
                        <td class="px-6 py-2 text-center">
                            <input type="number"
                                name="grandTotal[{{ $s->id }}]"
                                class="grand-total w-16 text-center border rounded-lg bg-yellow-50"
                                step="0.01"
                                readonly>
                        </td>
                        
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- SAVE BUTTON --}}
        <div id="saveBtn" class=" flex justify-end mt-6">
            <button class="px-8 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Save Marks
            </button>
        </div>

    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    function calculateRowTotal(row) {
        let total = 0;
        let grand_total = 0;

        row.querySelectorAll('.mark-input').forEach(input => {
            total += parseFloat(input.value) || 0;
            grand_total += parseFloat(input.value) || 0;
        });

        row.querySelector('.total-marks').value = total.toFixed(2);
        @if($assignment->paperMaster->number_of_tutorials == 1)  
            totals = parseFloat(row.querySelector('.total-marks').value) + parseFloat(row.querySelector('.total-tute-marks').value);
        @else
            totals = parseFloat(row.querySelector('.total-marks').value);
        @endif
        row.querySelector('.grand-total').value  = totals.toFixed(2);
    }

    
    function calculateTuteRowTotal(row) {
        let total_tute = 0;
        // alert(grand_total);
        row.querySelectorAll('.mark-input-tute').forEach(input => {
            total_tute += parseFloat(input.value) || 0;
        });

        row.querySelector('.total-tute-marks').value = total_tute.toFixed(2);
        
        totals = parseFloat(row.querySelector('.total-marks').value) + parseFloat(row.querySelector('.total-tute-marks').value);
        row.querySelector('.grand-total').value  = totals.toFixed(2);
      
    }



    document.querySelectorAll('tbody.mFilling tr').forEach(row => {

        row.querySelectorAll('.mark-input').forEach(input => {
            input.addEventListener('input', () => calculateRowTotal(row));
        });
        @if($assignment->paperMaster->number_of_tutorials == 1)  
            row.querySelectorAll('.mark-input-tute').forEach(input => {
                input.addEventListener('input', () => calculateTuteRowTotal(row));
            });
        @endif
        calculateRowTotal(row);
        @if($assignment->paperMaster->number_of_tutorials == 1)  
            calculateTuteRowTotal(row);
        @endif
        
    });




});
document.addEventListener('DOMContentLoaded', function () {
    const saveBtn = document.getElementById('saveBtn');

    document.querySelectorAll('.mark-input').forEach(input => {
        input.addEventListener('input', () => {
            saveBtn.classList.remove('hidden');
        });
    });
});
</script>
@endsection
