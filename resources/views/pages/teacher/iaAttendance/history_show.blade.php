@extends('layouts.app')

@section('content')
    <div class="max-w-7xl mx-auto px-4 py-6">

        <div class="mb-6">
            <h1 class="text-2xl font-bold">IA Marks Details</h1>
            <p class="text-sm text-gray-500">
                {{ $paper->code }} - {{ $paper->name }} |
                Semester {{ $semesterId }} |
                Section {{ $section }}
            </p>
        </div>
        <div class="mb-4 flex gap-3">

            <a href="{{ route('teacher.iaMarks.exportPdf', [
        'paperId' => $paper->id,
        'semesterId' => $semesterId,
        'section' => $section
    ]) }}" class="px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700">

                Export PDF

            </a>

            <a href="{{ route('teacher.iaMarks.history') }}"
                class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">

                Back

            </a>

        </div>
        <div class="bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
            <table class="min-w-full divide-y">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2">#</th>
                        <th class="px-6 py-2 text-left">Student</th>
                        <th class="px-6 py-3 text-left">Exam Roll Number</th>
                        <th class="px-6 py-3 text-left">College Roll Number</th>
                        {{-- <th class="px-4 py-2 text-center">Attendance %</th> --}}
                        @if(
                                (($paper->paper_type == 'SEC' || $paper->paper_type == 'VAC' || $paper->paper_type == 'AEC') && ($paper->number_of_lectures == 1 && $paper->number_of_tutorials == 0 && $paper->number_of_practicals == 1))
                                || ($paper->paper_type == 'AEC' && $paper->number_of_lectures == 2 && $paper->number_of_tutorials == 0 && $paper->number_of_practicals == 0)
                            )

                            <th class="px-4 py-2 text-center">Total IA Marks</th>
                        @else
                            <th class="px-4 py-2 text-center">Attendance Marks</th>
                            <th class="px-4 py-2 text-center">Class Test</th>
                            <th class="px-4 py-2 text-center">Assignment</th>
                            <th class="px-4 py-2 text-center">Total IA Marks</th>
                        @endif
                        <th class="px-4 py-2 text-center">Tutorial Activities</th>
                        <th class="px-4 py-2 text-center">Tutorial Attendance</th>
                        <th class="px-4 py-2 text-center">Total Tutorial Marks</th>
                        <th class="px-4 py-2 text-center font-bold">Grand Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @foreach($marks as $i => $m)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2">{{ $i + 1 }}</td>
                            <td class="px-6 py-2">{{ $m->student->name }}</td>
                            <td class="px-6 py-2 ">{{ optional($m->student->academic)->roll_number }}</td>
                            <td class="px-6 py-2 ">{{ optional($m->student->academic)->college_roll_number }}</td>
                            {{-- <td class="px-4 py-2 text-center">{{ $m->attendance_percentage }}%</td> --}}
                            @if(
                                    (($paper->paper_type == 'SEC' || $paper->paper_type == 'VAC' || $paper->paper_type == 'AEC') && ($paper->number_of_lectures == 1 && $paper->number_of_tutorials == 0 && $paper->number_of_practicals == 1))
                                    || ($paper->paper_type == 'AEC' && $paper->number_of_lectures == 2 && $paper->number_of_tutorials == 0 && $paper->number_of_practicals == 0)
                                )

                                <td class="px-4 py-2 text-center">{{ $m->total }}</td>
                            @else
                                <td class="px-4 py-2 text-center">{{ $m->attendance }}</td>
                                <td class="px-4 py-2 text-center">{{ $m->class_test }}</td>
                                <td class="px-4 py-2 text-center">{{ $m->assignment }}</td>
                                <td class="px-4 py-2 text-center">{{ $m->total }}</td>
                            @endif
                            <td class="px-4 py-2 text-center">{{ $m->tute_ca ?? '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $m->tute_attendance ?? '-' }}</td>
                            <td class="px-4 py-2 text-center">{{ $m->total_tute_marks ?? '-' }}</td>
                            <td class="px-4 py-2 text-center font-semibold">
                                {{ $m->grand_total }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <a href="{{ route('teacher.iaMarks.history') }}"
                class="px-6 py-2 bg-gray-600 text-white rounded hover:bg-gray-700">
                Back
            </a>
        </div>

    </div>
@endsection