@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold mb-6">My Attendance</h1>

    @forelse($attendance as $paperId => $records)

        @php
            $subject = $records->first()->paper;
        @endphp

        {{-- SUBJECT CARD --}}
        <div class="mb-8 bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="text-lg font-semibold">
                   {{ $subject->code }} {{ $subject->name }}
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">Month</th>

                            <th class="px-4 py-2 text-center">Lecture<br><span class="text-xs">W / P</span></th>
                            <th class="px-4 py-2 text-center">Tutorial<br><span class="text-xs">W / P</span></th>
                            <th class="px-4 py-2 text-center">Practical<br><span class="text-xs">W / P</span></th>

                            <th class="px-4 py-2 text-center">%</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">

                    @foreach($records as $r)

                        @php
                            $totalWorking =
                                $r->lecture_working_days +
                                $r->tute_working_days +
                                $r->practical_working_days;

                            $totalPresent =
                                $r->lecture_present_days +
                                $r->tute_present_days +
                                $r->practical_present_days;

                            $percentage = $totalWorking > 0
                                ? round(($totalPresent / $totalWorking) * 100, 2)
                                : 0;
                        @endphp

                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">
                                {{ $r->month }}/{{ $r->year }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $r->lecture_working_days }} /
                                {{ $r->lecture_present_days }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $r->tute_working_days }} /
                                {{ $r->tute_present_days }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $r->practical_working_days }} /
                                {{ $r->practical_present_days }}
                            </td>

                            <td class="px-4 py-3 text-center font-semibold
                                {{ $percentage < 75 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $percentage }}%
                            </td>
                        </tr>

                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @empty
        <div class="text-center text-gray-500">
            No attendance data available
        </div>
    @endforelse

</div>
@endsection
