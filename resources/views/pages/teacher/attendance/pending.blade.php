@extends('layouts.app')
@section('content')

    <h2 class="text-2xl font-bold mb-6">Pending Attendance</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach($assignments as $a)

            @php
                $semesterNum = (int)$a->semester_id;
                $postFix = ($semesterNum % 2 == 0) ? 'even' : 'odd';
                $setting = $attendanceSettings->firstWhere('semester_type', $postFix);
                
                // Get all months for this class group in the pool table
                $poolMonths = \App\Models\TimetableHeldPool::where([
                    'teacher_id' => auth('teacher')->id(),
                    'course_id' => $a->course_id,
                    'semester_id' => $a->semester_id,
                    'section' => $a->section,
                    'batch_identifier' => $a->batch_identifier ?? '',
                    'paper_master_id' => $a->paper_master_id,
                ])->orderBy('year', 'asc')->orderBy('month', 'asc')->get();
            @endphp

            <div class="bg-white shadow rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

                <div class="mb-2">
                    <h3 class="text-lg font-semibold">
                        {{ $a->course->name }} - {{ $a->semester->name }}
                    </h3>
                    <p class="text-gray-600">
                        {{ $a->section }}@if($a->batch_identifier) | Batch {{ $a->batch_identifier }}@endif | {{ $a->paperMaster->name }}
                    </p>
                </div>

                {{-- ADMIN CONFIG NOT SET --}}
                @if(!$setting)
                    <div class="mt-4 text-sm text-red-600 font-semibold">
                        ⚠ Attendance configuration not defined by admin.
                    </div>
                @elseif($poolMonths->isEmpty())
                    <div class="mt-4 text-sm text-gray-500 font-semibold">
                         No classes marked held yet for this subject.
                    </div>
                @else
                    <div class="mt-3">
                        <h4 class="text-sm font-medium text-gray-700 mb-1">
                            Available Months:
                        </h4>

                        <div class="flex flex-wrap gap-2">
                            @foreach($poolMonths as $mRecord)
                                @php $preFix = $setting->attendance_type . '.' ?? ' '; @endphp
                                <a href="{{ route('teacher.' . $preFix . 'attendance.fill', [
                                    'assignment' => $mRecord->id,
                                    'month' => $mRecord->month,
                                    'year' => $mRecord->year
                                ]) }}"
                                    class="bg-blue-500 text-white text-sm px-3 py-1 rounded shadow hover:bg-blue-600 transition {{ in_array($mRecord->month, $isLocked) ? 'pointer-events-none opacity-50' : '' }}">
                                    {{ \Carbon\Carbon::createFromDate(
                                    $mRecord->year,
                                    $mRecord->month,
                                    1
                                )->format('M Y') }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        @endforeach

    </div>

@endsection
