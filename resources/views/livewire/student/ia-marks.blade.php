<div class="min-h-screen bg-gray-100 p-3 md:p-6">

    {{-- HEADER --}}
    <div class="bg-gradient-to-r  rounded-2xl shadow-xl p-2 md:p-2 text-yellow mb-6 sm:item-center">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-5">

            <div>
                <h1 class="text-3xl text-blue-700 md:text-4xl font-bold">
                    IA Marks Dashboard
                </h1>

                <p class="mt-2 text-black-700 text-sm md:text-base">
                    Welcome, {{ $student->name }}
                </p>
            </div>

            <div class="flex gap-4 flex-wrap">

                <div class="bg-white/20 backdrop-blur-lg rounded-2xl px-5 py-3">
                    <p class="text-xs uppercase tracking-wide opacity-80">
                        Semester
                    </p>

                    <h2 class="text-2xl font-bold">
                        {{ $student->semester_id }}
                    </h2>
                </div>

                <div class="bg-white/20 backdrop-blur-lg rounded-2xl px-5 py-3">
                    <p class="text-xs uppercase tracking-wide opacity-80">
                        Total Papers
                    </p>

                    <h2 class="text-2xl font-bold">
                        {{ count($papers) }}
                    </h2>
                </div>

            </div>

        </div>
    </div>

    {{-- EMPTY STATE --}}
    @if (count($papers) == 0)

        <div class="bg-white rounded-3xl shadow-lg p-10 text-center">

            <div class="text-6xl mb-4">
                📚
            </div>

            <h2 class="text-2xl font-bold text-gray-700">
                No Papers Found
            </h2>

            <p class="text-gray-500 mt-2">
                Papers are not assigned to this student yet.
            </p>

        </div>

    @else

        {{-- PAPERS --}}
        <div class="grid gap-5">

            @foreach ($papers as $paper)

                @php

                    $paperId = $paper->paper_master_id;

                    $mark = $marks[$paperId] ?? [];

                    $classTest = $mark['class_test'] ?? 0;
                    $assignment = $mark['assignment'] ?? 0;
                    $attendance = $mark['attendance'] ?? 0;
                    $tuteCa = $mark['tute_ca'] ?? 0;
                    $tuteAttendance = $mark['tute_attendance'] ?? 0;

                    $total = $classTest + $assignment + $attendance;

                    $tuteTotal = $tuteCa + $tuteAttendance;

                    $grandTotal = $total + $tuteTotal;

                @endphp

                <div
                    class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100 transition duration-300 hover:shadow-2xl">

                    {{-- PAPER HEADER --}}
                    <button wire:click="togglePaper({{ $paperId }})"
                        class="w-full p-5 md:p-6 flex items-center justify-between hover:bg-gray-50 transition">

                        <div class="text-left">

                            <h2 class="text-lg md:text-2xl font-bold text-gray-800">
                                {{ $paper->paper?->name }}
                            </h2>

                            <p class="text-sm text-gray-500 mt-1">
                                Paper Code :
                                <span class="font-semibold">
                                    {{ $paper->paper?->code }}
                                </span>
                            </p>

                        </div>

                        <div>

                            @if ($openPaper == $paperId)

                                <div class="bg-indigo-100 text-indigo-600 rounded-full p-2">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />

                                    </svg>

                                </div>

                            @else

                                <div class="bg-gray-100 text-gray-500 rounded-full p-2">

                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">

                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />

                                    </svg>

                                </div>

                            @endif

                        </div>

                    </button>

                    {{-- EXPANDABLE CONTENT --}}
                    @if ($openPaper == $paperId)

                        <div class="border-t bg-gradient-to-br from-gray-50 to-indigo-50 p-4 md:p-6">

                            {{-- MARKS GRID --}}
                            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

                                {{-- CLASS TEST --}}
                                <div class="bg-white rounded-2xl shadow-md p-4 text-center border border-indigo-100">

                                    <p class="text-sm text-gray-500">
                                        IA Class Test Marks
                                    </p>

                                    <h2 class="text-2xl md:text-3xl font-bold text-indigo-600 mt-2">

                                        {{ $classTest }}

                                    </h2>

                                </div>

                                {{-- ASSIGNMENT --}}
                                <div class="bg-white rounded-2xl shadow-md p-4 text-center border border-purple-100">

                                    <p class="text-sm text-gray-500">
                                        IA Assignment Marks
                                    </p>

                                    <h2 class="text-2xl md:text-3xl font-bold text-purple-600 mt-2">

                                        {{ $assignment }}

                                    </h2>

                                </div>

                                {{-- ATTENDANCE --}}
                                <div class="bg-white rounded-2xl shadow-md p-4 text-center border border-pink-100">

                                    <p class="text-sm text-gray-500">
                                        IA Attendance Marks
                                    </p>

                                    <h2 class="text-2xl md:text-3xl font-bold text-pink-600 mt-2">

                                        {{ $attendance }}

                                    </h2>

                                </div>

                                {{-- TUTE CA --}}
                                <div class="bg-white rounded-2xl shadow-md p-4 text-center border border-green-100">

                                    <p class="text-sm text-gray-500">
                                        Tut. Activity Marks
                                    </p>

                                    <h2 class="text-2xl md:text-3xl font-bold text-green-600 mt-2">

                                        {{ $tuteCa }}

                                    </h2>

                                </div>

                                {{-- TUTE ATTENDANCE --}}
                                <div class="bg-white rounded-2xl shadow-md p-4 text-center border border-yellow-100">

                                    <p class="text-sm text-gray-500">
                                        Tut. Attendance Marks
                                    </p>

                                    <h2 class="text-2xl md:text-3xl font-bold text-yellow-600 mt-2">

                                        {{ $tuteAttendance }}

                                    </h2>

                                </div>

                                {{-- GRAND TOTAL --}}
                                {{-- <div
                                    class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-2xl shadow-xl p-4 text-center">

                                    <p class="text-sm opacity-80">
                                        Grand Total
                                    </p>

                                    <h2 class="text-3xl md:text-4xl font-bold mt-2">

                                        {{ $grandTotal }}

                                    </h2>

                                </div> --}}

                            </div>

                            {{-- SUMMARY --}}
                            <div class="mt-6 bg-white rounded-2xl p-4 shadow border border-gray-100">

                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                                    <div>

                                        <h3 class="text-lg font-bold text-gray-800">

                                            Performance Summary

                                        </h3>

                                        <p class="text-sm text-gray-500 mt-1">

                                            Internal assessment marks overview.

                                        </p>

                                    </div>

                                    <div class="flex gap-3 flex-wrap">

                                        <div class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-xl text-sm font-semibold">

                                            Theory :
                                            {{ round($total) }}

                                        </div>

                                        <div class="bg-green-50 text-green-700 px-4 py-2 rounded-xl text-sm font-semibold">

                                            Tutorial :
                                            {{ round($tuteTotal) }}

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    @endif

                </div>

            @endforeach

        </div>

    @endif

</div>