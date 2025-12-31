@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold mb-4">
        Attendance Details ({{ \Carbon\Carbon::createFromDate((int)$year, (int)$month, 1)->format('F Y') }})
    </h1>

    <div class="bg-white shadow rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-6 py-3">Student</th>
                    <th class="px-6 py-3 text-center">Lecture (Working / Present)</th>
                    <th class="px-6 py-3 text-center">Tute (Working / Present)</th>
                    <th class="px-6 py-3 text-center">Practical (Working / Present)</th>
                    <th class="px-6 py-3 text-center">Lecture %</th>
                    <th class="px-6 py-3 text-center">Tute %</th>
                    <th class="px-6 py-3 text-center">Practical %</th>
                </tr>
            </thead>

            <tbody class="bg-white divide-y">
                @foreach($records as $i => $r)
                    @php
                        // Lecture percentage
                        $lecture_percentage = $r->lecture_working_days > 0
                            ? round(($r->lecture_present_days / $r->lecture_working_days) * 100, 2)
                            : 0;

                        // Tute percentage
                        $tute_percentage = $r->tute_working_days > 0
                            ? round(($r->tute_present_days / $r->tute_working_days) * 100, 2)
                            : 0;

                        // Practical percentage
                        $practical_percentage = $r->practical_working_days > 0
                            ? round(($r->practical_present_days / $r->practical_working_days) * 100, 2)
                            : 0;
                    @endphp

                    <tr class="border-b">
                        <td class="px-4 py-2">{{ $i + 1 }}</td>
                        <td class="px-6 py-2">{{ $r->student->name }}</td>

                        <td class="px-6 py-2 text-center font-semibold">
                            {{ $r->lecture_working_days . ' / ' . $r->lecture_present_days }}
                        </td>

                        <td class="px-6 py-2 text-center font-semibold">
                            {{ $r->tute_working_days . ' / ' . $r->tute_present_days }}
                        </td>

                        <td class="px-6 py-2 text-center font-semibold">
                            {{ $r->practical_working_days . ' / ' . $r->practical_present_days }}
                        </td>

                        <td class="px-6 py-2 text-center">{{ $lecture_percentage }}%</td>
                        <td class="px-6 py-2 text-center">{{ $tute_percentage }}%</td>
                        <td class="px-6 py-2 text-center">{{ $practical_percentage }}%</td>
                    </tr>
                @endforeach

                @if(count($records) === 0)
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                            No attendance records found for this month.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>
@endsection
