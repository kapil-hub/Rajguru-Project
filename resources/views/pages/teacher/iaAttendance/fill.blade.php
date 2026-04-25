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

                                        <fieldset  class="overflow-x-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
                                             <legend>Marks Breakup</legend>
                                            <div class="overflow-x-auto"> 
                                                <table class="min-w-full text-sm">

                                                    <!-- HEADER ROW 1 (GROUP TITLES) -->
                                                    <thead>
                                                        <tr class="text-white text-xs uppercase tracking-wider">
                                                            <th colspan="2" class="bg-indigo-600 px-4 py-3 text-center" style="color:#fff">Theory</th>
                                                            <th colspan="4" class="bg-blue-600 px-4 py-3 text-center" style="color:#fff">Internal Assessment (IA)</th>
                                                            <th colspan="3" class="bg-purple-600 px-4 py-3 text-center" style="color:#fff">Tutorial</th>
                                                            <th colspan="4" class="bg-orange-500 px-4 py-3 text-center" style="color:#fff">Practical</th>
                                                            <th class="bg-green-600 px-4 py-3 text-center" style="color:#fff">Grand</th>
                                                        </tr>

                                                        <!-- HEADER ROW 2 (SUB TITLES) -->
                                                        <tr class="bg-gray-50 text-gray-700 text-xs font-semibold">
                                                            <th class="px-4 py-2">Total</th>
                                                            <th class="px-4 py-2">Theory</th>
                                                            @if(
        (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC' || $assignment->paperMaster->paper_type == 'AEC') && ($assignment->paperMaster->number_of_lectures == 1 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 1))
        || ($assignment->paperMaster->paper_type == 'AEC' && $assignment->paperMaster->number_of_lectures == 2 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 0)
    )
                                                                <th colspan = "4" class="px-4 py-2">IA Total</th> 
                                                            @else
                                                                <th class="px-4 py-2">IA Total</th>
                                                                <th class="px-4 py-2">Class Test</th>
                                                                <th class="px-4 py-2">Assignment</th>
                                                                <th class="px-4 py-2">Attendance</th>
                                                            @endif

                                                            <th class="px-4 py-2">Tutorial Total </th>
                                                            <th class="px-4 py-2">Tutorial Activities</th>
                                                            <th class="px-4 py-2">Tutorial Attendance</th>
                                                            @if(
        (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC') && ($assignment->paperMaster->number_of_lectures == 0 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 2))
        || (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC' || $assignment->paperMaster->paper_type == 'AEC') && ($assignment->paperMaster->number_of_lectures == 1 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 1))
    )
                                                                <th colspan="4" class="px-4 py-2">Total</th>
                                                            @else
                                                                <th class="px-4 py-2">Total</th>
                                                                <th class="px-4 py-2">CA</th>
                                                                <th class="px-4 py-2">Written</th>
                                                                <th class="px-4 py-2">Viva</th>
                                                            @endif

                                                            <th class="px-4 py-2">Total</th>
                                                        </tr>
                                                    </thead>

                                                    <tbody class="text-center font-medium">

                                                        @php 
                                                            $lectureB = $lecture = lectureMarksBreakup($assignment->paperMaster->number_of_lectures);
    $tuteB = $tute = tutorialMarksBreakup($assignment->paperMaster->number_of_tutorials);
    $practicalB = $practical = practicalMarksBreakup($assignment->paperMaster->number_of_practicals);
    $practicalTotal = $practical["ca"] + $practical["written_exam"] + $practical["viva_voce"];
    $grandTotal = ($assignment->paperMaster->number_of_lectures + $assignment->paperMaster->number_of_tutorials + $assignment->paperMaster->number_of_practicals) * 40;
                                                        @endphp

                                                        <tr class="hover:bg-gray-50 transition">

                                                            <!-- THEORY -->
                                                            <td class="px-4 py-3 bg-indigo-50">{{ $lecture["total"] ?? '-' }}</td>
                                                            <td class="px-4 py-3 bg-indigo-100 text-indigo-700 font-semibold">
                                                                {{ $lecture["theory"] ?? '-' }}
                                                            </td>

                                                            <!-- IA -->
                                                            @if(
        (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC' || $assignment->paperMaster->paper_type == 'AEC') && ($assignment->paperMaster->number_of_lectures == 1 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 1))
        || ($assignment->paperMaster->paper_type == 'AEC' && $assignment->paperMaster->number_of_lectures == 2 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 0)
    )
                                                                <td colspan="4" class="px-4 py-3 bg-blue-50 font-semibold">
                                                                    {{ $lecture["ia"] }}
                                                                </td>
                                                            @else
                                                                <td class="px-4 py-3 bg-blue-50 font-semibold">
                                                                    {{ $lecture["ia"] }}
                                                                </td>
                                                                <td class="px-4 py-3 bg-blue-50">{{ $lecture["ia_breakup"]["class_test"] }}</td>
                                                                <td class="px-4 py-3 bg-blue-50">{{ $lecture["ia_breakup"]["assignment"] }}</td>
                                                                <td class="px-4 py-3 bg-blue-100 text-blue-700 font-semibold">
                                                                    {{ $lecture["ia_breakup"]["attendance"] }}
                                                                </td>
                                                            @endif



                                                            <!-- TUTORIAL -->
                                                            <td class="px-4 py-3 bg-purple-50">{{ $tute["total_tute"] }}</td>
                                                            <td class="px-4 py-3 bg-purple-50 font-semibold">{{ $tute["activities"] }}</td>
                                                            <td class="px-4 py-3 bg-purple-50 font-semibold">{{ $tute["attendance"] }}</td>

                                                            <!-- PRACTICAL -->
                                                            @if(
        (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC') && ($assignment->paperMaster->number_of_lectures == 0 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 2))
        || (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC' || $assignment->paperMaster->paper_type == 'AEC') && ($assignment->paperMaster->number_of_lectures == 1 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 1))
    )
                                                                <td colspan="4" class="px-4 py-3 bg-orange-50 font-semibold">
                                                                    {{ $practicalTotal }}
                                                                </td>
                                                            @else
                                                                <td class="px-4 py-3 bg-orange-50 font-semibold">
                                                                    {{ $practicalTotal }}
                                                                </td>
                                                                <td class="px-4 py-3 bg-orange-50">{{ $practical["ca"] }}</td>
                                                                <td class="px-4 py-3 bg-orange-50">{{ $practical["written_exam"] }}</td>
                                                                <td class="px-4 py-3 bg-orange-100 text-orange-700 font-semibold">
                                                                    {{ $practical["viva_voce"] }}
                                                                </td>
                                                            @endif

                                                            <!-- GRAND -->
                                                            <td class="px-4 py-3 bg-green-100 text-green-700 text-lg font-bold">
                                                                {{ $grandTotal }}
                                                            </td>

                                                        </tr>

                                                    </tbody>
                                                </table>
                                            </div>
                                        </fieldset >



                                    <form method="POST" action="{{ route('teacher.iaMarks.store') }}">
                                        @csrf

                                        {{-- HIDDEN META --}}
                                        <input type="hidden" name="course_id" value="{{ $assignment->course_id }}">
                                        <input type="hidden" name="semester_id" value="{{ $assignment->semester_id }}">
                                        <input type="hidden" name="section" value="{{ $assignment->section }}">
                                        <input type="hidden" name="paper_master_id" value="{{ $assignment->paper_master_id }}">




                                        {{-- Marks Filling TABLE (HIDDEN INITIALLY) --}}
                                        <div id="attendanceTable" class=" bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600 overflow-x-auto">

                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-100">
                                                    <tr>
                                                    <th class="px-4 py-3">#</th>
                                                    <th class="px-8 py-3 text-left">Student Name</th>
                                                    <th class="px-8 py-3 text-left">Exam Roll Number</th>
                                                     <th class="px-8 py-3 text-left">College Roll Number</th>
                                                    <th class="px-16 py-3 text-center">Lecture Attendence<br><span class="text-xs">Total Lecture / Total Attended</span></th>
                                                    <th class="px-4 py-3 text-center">%</span></th>

                                                    <th class="px-16 py-3 text-center">Tutorial Attendence <br><span class="text-xs">Total Tutes / Total Attended</span></th>
                                                    <th class="px-4 py-3 text-center">%</span></th>
                                                    {{-- <th class="px-6 py-3 text-center">IA (Attendance) <br><span class="text-xs">TM / OM</span></th> --}}
                                                    @if($assignment->paperMaster->number_of_tutorials == 1)
                                                        <th class="px-4 py-3 text-center">Tutorial Activities <br> {{$tuteB["activities"]}}</th>
                                                        <th class="px-4 py-3 text-center">Tutorial Attendance <br> {{$tuteB["attendance"]}}</th>
                                                    @endif
                                                    @if(
        (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC' || $assignment->paperMaster->paper_type == 'AEC') && ($assignment->paperMaster->number_of_lectures == 1 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 1))
        || ($assignment->paperMaster->paper_type == 'AEC' && $assignment->paperMaster->number_of_lectures == 2 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 0)
    )
                                                        <th colspan="4" class="px-4 py-3 text-center">Total IA Marks <br>{{ $lectureB["ia_breakup"]["total"] }} </th>
                                                    @else
                                                        <th class="px-4 py-3 text-center">IA (Class Test Marks) <br>{{ $lectureB["ia_breakup"]["class_test"] }} </th>
                                                        <th class="px-4 py-3 text-center">IA (Assignment Marks) <br> {{ $lectureB["ia_breakup"]["assignment"] }}</th>
                                                        <th class="px-4 py-3 text-center">IA (Attendance Marks) <br>{{$lectureB["ia_breakup"]["attendance"] }}</th>
                                                        <th class="px-4 py-3 text-center">Total IA Marks</th>
                                                    @endif
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
                                                        <td class="px-6 py-2 font-medium">{{ optional($s->academic)->roll_number }}</td>
                                                        <td class="px-6 py-2 font-medium">{{ optional($s->academic)->college_roll_number }}</td>
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
                                                                $pt = $oldAttendences[$s->id]['tute_percentage'] ?? 0;
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
                                                                    min="0" max="{{ $tuteB["activities"] }}">
                                                            </td>
                                                            @php
                                                                $tutePercent = $oldAttendences[$s->id]['tute_percentage'] ?? 0;
                                                                $tuteBreakup = tutorialMarksBreakup($assignment->paperMaster->number_of_tutorials);
                                                                $tuteAttMarks = attendanceMarks($tutePercent, $tuteBreakup["attendance"]);
                                                                
                                                                
                                                            @endphp
                                                            <td class="px-6 py-2 text-center space-x-2">
                                                                <input type="number"
                                                                    name="tuteAttendanceMarks[{{ $s->id }}]"
                                                                    value = "{{ $tuteAttMarks }}"
                                                                    class="w-16 mark-input-tute text-center border rounded-lg bg-gray-100"
                                                                    min="0" readonly>
                                                            </td>
                                                            
                                                            
                                                            
                                                        @endif
                                                        @if(
                                                            (($assignment->paperMaster->paper_type == 'SEC' || $assignment->paperMaster->paper_type == 'VAC' || $assignment->paperMaster->paper_type == 'AEC') && ($assignment->paperMaster->number_of_lectures == 1 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 1))
                                                            || ($assignment->paperMaster->paper_type == 'AEC' && $assignment->paperMaster->number_of_lectures == 2 && $assignment->paperMaster->number_of_tutorials == 0 && $assignment->paperMaster->number_of_practicals == 0)
                                                        )
                                                            <td colspan="4" class="px-6 py-2 text-center space-x-2">
                                                                <input type="number"
                                                                    name="totalIaMarks[{{ $s->id }}]"
                                                                    value = "{{ $oldData[$s->id]["total"] ?? 0 }}"
                                                                    class="w-16 mark-input total-marks text-center border rounded-lg"
                                                                    min="0" max="{{ $lectureB["ia"] }}">
                                                            </td>
                                                        @else
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
                                                        @endif
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
