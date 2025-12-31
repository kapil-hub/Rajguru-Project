@extends('layouts.app')

@section('content')
@php
    function colorClass($percent) {
        if ($percent < 45) return 'bg-red-500 text-red-600';
        if ($percent < 75) return 'bg-orange-500 text-orange-600';
        return 'bg-green-500 text-green-600';
    }

    function percent($p, $h) {
        return $h > 0 ? round(($p / $h) * 100, 2) : 0;
    }

    $lecturePercent   = percent($lecturePresent, $lectureHeld);
    $tutePercent      = percent($tutePresent, $tuteHeld);
    $practicalPercent = percent($practicalPresent, $practicalHeld);
@endphp

<div class="max-w-7xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold mb-6">Student Dashboard</h1>

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-sm text-gray-500">Total Subjects</p>
            <p class="text-3xl font-bold">{{ $totalSubjects }}</p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-sm text-gray-500">Overall Attendance</p>
            <p class="text-3xl font-bold {{ explode(' ', colorClass($attendancePercent))[1] }}">
                {{ $attendancePercent }}%
            </p>
        </div>

        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="text-sm text-gray-500">Status</p>
            <p class="text-xl font-semibold">
                @if($attendancePercent < 45)
                    <span class="text-red-600">Critical</span>
                @elseif($attendancePercent < 75)
                    <span class="text-orange-600">Warning</span>
                @else
                    <span class="text-green-600">Safe</span>
                @endif
            </p>
        </div>
    </div>

    {{-- ATTENDANCE BREAKDOWN --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        {{-- LECTURE --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="font-semibold mb-1">Lecture</p>
            <p class="text-sm text-gray-500 mb-2">
                {{ $lecturePresent }} / {{ $lectureHeld }}
            </p>
            <div class="w-full bg-gray-200 h-3 rounded-full">
                <div class="h-3 rounded-full {{ explode(' ', colorClass($lecturePercent))[0] }}"
                     style="width: {{ $lecturePercent }}%">
                </div>
            </div>
            <p class="text-xs mt-1 {{ explode(' ', colorClass($lecturePercent))[1] }}">
                {{ $lecturePercent }}%
            </p>
        </div>

        {{-- TUTE --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="font-semibold mb-1">Tute</p>
            <p class="text-sm text-gray-500 mb-2">
                {{ $tutePresent }} / {{ $tuteHeld }}
            </p>
            <div class="w-full bg-gray-200 h-3 rounded-full">
                <div class="h-3 rounded-full {{ explode(' ', colorClass($tutePercent))[0] }}"
                     style="width: {{ $tutePercent }}%">
                </div>
            </div>
            <p class="text-xs mt-1 {{ explode(' ', colorClass($tutePercent))[1] }}">
                {{ $tutePercent }}%
            </p>
        </div>

        {{-- PRACTICAL --}}
        <div class="bg-white p-6 rounded-xl shadow border">
            <p class="font-semibold mb-1">Practical</p>
            <p class="text-sm text-gray-500 mb-2">
                {{ $practicalPresent }} / {{ $practicalHeld }}
            </p>
            <div class="w-full bg-gray-200 h-3 rounded-full">
                <div class="h-3 rounded-full {{ explode(' ', colorClass($practicalPercent))[0] }}"
                     style="width: {{ $practicalPercent }}%">
                </div>
            </div>
            <p class="text-xs mt-1 {{ explode(' ', colorClass($practicalPercent))[1] }}">
                {{ $practicalPercent }}%
            </p>
        </div>

    </div>

</div>
@endsection
