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

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2">#</th>
                    <th class="px-6 py-2 text-left">Student</th>
                    {{-- <th class="px-4 py-2 text-center">Attendance %</th> --}}
                    <th class="px-4 py-2 text-center">Attendance Marks</th>
                    <th class="px-4 py-2 text-center">Class Test</th>
                    <th class="px-4 py-2 text-center">Assignment</th>
                    <th class="px-4 py-2 text-center">Tutorial (CA)</th>
                    <th class="px-4 py-2 text-center font-bold">Total</th>
                </tr>
            </thead>

            <tbody class="divide-y">
            @foreach($marks as $i => $m)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $i+1 }}</td>
                    <td class="px-6 py-2">{{ $m->student->name }}</td>
                    {{-- <td class="px-4 py-2 text-center">{{ $m->attendance_percentage }}%</td> --}}
                    <td class="px-4 py-2 text-center">{{ $m->attendance }}</td>
                    <td class="px-4 py-2 text-center">{{ $m->class_test }}</td>
                    <td class="px-4 py-2 text-center">{{ $m->assignment }}</td>
                    <td class="px-4 py-2 text-center">{{ $m->tute_ca ?? '-' }}</td>
                    <td class="px-4 py-2 text-center font-semibold">
                        {{ $m->total }}
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
