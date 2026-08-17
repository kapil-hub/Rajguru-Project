@extends('layouts.app')
@section('content')

    <h2 class="text-2xl font-bold mb-6">Pending Attendance</h2>

    <form method="GET" action="{{ route('teacher.attendance.pending') }}" class="bg-white shadow rounded-2xl p-5 mb-6 border-l-8 border-indigo-600">
        <label for="month_year" class="block text-sm font-medium text-gray-700 mb-2">Filter by Month</label>
        <div class="flex flex-col sm:flex-row gap-3">
            <select id="month_year" name="month_year" class="w-full sm:w-72 rounded-lg border-gray-300">
                @foreach($monthOptions as $monthOption)
                    <option value="{{ $monthOption['value'] }}" @selected($selectedMonthKey === $monthOption['value']) @disabled($monthOption['disabled'])>
                        {{ $monthOption['label'] }}{{ $monthOption['disabled'] ? ' (Future)' : '' }}
                    </option>
                @endforeach
            </select>
            <button class="px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Search
            </button>
        </div>
    </form>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        @foreach($assignments as $a)

            @php
                $semesterNum = (int) $a->semester_id;
                $postFix = ($semesterNum % 2 == 0) ? 'even' : 'odd';
                $setting = $attendanceSettings->firstWhere('semester_type', $postFix);
                $preFix = $setting ? $setting->attendance_type . '.' : '';
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

                @if(!$setting)
                    <div class="mt-4 text-sm text-red-600 font-semibold">
                        Attendance configuration not defined by admin.
                    </div>
                @else
                    <div class="mt-3">
                        <h4 class="text-sm font-medium text-gray-700 mb-1">
                            Selected Month:
                        </h4>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('teacher.' . $preFix . 'attendance.fill', [
                                'assignment' => $a->id,
                                'month' => $selectedMonth,
                                'year' => $selectedYear
                            ]) }}"
                                class="bg-blue-500 text-white text-sm px-3 py-1 rounded shadow hover:bg-blue-600 transition {{ in_array($selectedMonth, $isLocked) ? 'pointer-events-none opacity-50' : '' }}">
                                {{ \Carbon\Carbon::createFromDate($selectedYear, $selectedMonth, 1)->format('M Y') }}
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        @endforeach

    </div>

    @if($assignments->isEmpty())
        <div class="bg-white rounded-2xl p-6 text-sm text-gray-500 border">
            No timetable assignments found for this teacher.
        </div>
    @endif

@endsection
