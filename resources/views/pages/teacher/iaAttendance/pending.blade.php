@extends('layouts.app')
@section('content')

<h2 class="text-2xl font-bold mb-6">Pending IA Attendance</h2>

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

    <div class="bg-white shadow rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

        <div class="mb-2">
            <h3 class="text-lg font-semibold">
                {{ $a->course->name }} - {{ $a->semester->name }}
            </h3>
            <p class="text-gray-600">
                {{ $a->section }} | {{ $a->paperMaster->name }}
            </p>
        </div>

        {{-- ADMIN CONFIG NOT SET --}}
       @if($a->is_coordinator == 0)
            <center><p> Only Co-ordinator are allowed to fill IA Attendence</p></center>
        @else
            <div class="mt-3">
                <div class="flex flex-wrap gap-2">
                        <a href="{{ route('teacher.iaAttendance.fill', [
                                'assignment' => $a->id
                            ]) }}"
                        class="bg-blue-500 text-white text-sm px-3 py-1 rounded shadow hover:bg-blue-600 transition">
                            Fill
                        </a>
                </div>
            </div>
        @endif

    </div>
@endforeach

</div>

@endsection
