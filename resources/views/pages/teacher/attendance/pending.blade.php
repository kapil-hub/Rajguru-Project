@extends('layouts.app')
@section('content')

<h2 class="text-2xl font-bold mb-6">Pending Attendance</h2>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

@foreach($assignments as $a)

    @php
     if(($a->semester_type) % 2 == 0 ){
        $postFix = 'even';
     }else{
        $postFix = 'odd';
     }
        $key = $a->academic_session . '_' . $postFix;
        // echo $key;die;
        $setting = $attendanceSettings[$key] ?? null;
        // echo "<pre>";print_r($setting);die;
    @endphp

    <div class="bg-white shadow rounded p-4 border border-gray-200">

        <div class="mb-2">
            <h3 class="text-lg font-semibold">
                {{ $a->course->name }} - {{ $a->semester->name }}
            </h3>
            <p class="text-gray-600">
                {{ $a->section }} | {{ $a->paperMaster->name }}
            </p>
        </div>

        {{-- ADMIN CONFIG NOT SET --}}
        @if(!$setting)
            <div class="mt-4 text-sm text-red-600 font-semibold">
                ⚠ Attendance configuration not defined by admin.
            </div>
        @else
            @php
                $startMonth = (int) $setting->start_month;
                $endMonth   = (int) $setting->end_month;
            @endphp

            @if($startMonth < 1 || $endMonth < 1 || $startMonth > 12 || $endMonth > 12)
                <div class="mt-4 text-sm text-red-600 font-semibold">
                    ⚠ Invalid month configuration by admin.
                </div>

            @elseif($startMonth > $endMonth)
                <div class="mt-4 text-sm text-red-600 font-semibold">
                    ⚠ Academic session month range is invalid.
                </div>

            @else
                <div class="mt-3">
                    <h4 class="text-sm font-medium text-gray-700 mb-1">
                        Pending Months:
                    </h4>

                    <div class="flex flex-wrap gap-2">
                        @for($m = $startMonth; $m <= $endMonth; $m++)
                            @php $preFix = $setting->attendance_type.'.' ?? ' '; @endphp
                            <a href="{{ route('teacher.'.$preFix.'attendance.fill', [
                                    'assignment' => $a->id,
                                    'month' => $m,
                                    'year' => now()->year
                                ]) }}"
                            class="bg-blue-500 text-white text-sm px-3 py-1 rounded shadow hover:bg-blue-600 transition">
                                {{ \Carbon\Carbon::createFromDate(
                                    now()->year,
                                    $m,
                                    1
                                )->format('M Y') }}
                            </a>
                        @endfor
                    </div>
                </div>
            @endif
        @endif

    </div>
@endforeach

</div>

@endsection
