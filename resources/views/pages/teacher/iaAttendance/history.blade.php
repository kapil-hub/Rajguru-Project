@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <h2 class="text-xl font-semibold mb-4">
        IA Marks History (Last 6 Months)
    </h2>

    @if($papers->isEmpty())
        <div class="text-gray-600">
            No IA marks saved in the last 6 months.
        </div>
    @else
        <table class="w-full border text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2 border">Paper</th>
                    <th class="p-2 border">Course</th>
                    <th class="p-2 border">Semester</th>
                    <th class="p-2 border">Section</th>
                    <th class="p-2 border">Students</th>
                    <th class="p-2 border">Last Updated</th>
                    <th class="p-2 border">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($papers as $row)
                    <tr>
                        <td class="p-2 border">{{ $row->name }}</td>
                        <td class="p-2 border">{{ $row->name }}</td>
                        <td class="p-2 border text-center">{{ $row->semester_id }}</td>
                        <td class="p-2 border text-center">{{ $row->section }}</td>
                        <td class="p-2 border text-center">{{ $row->students_count }}</td>
                        <td class="p-2 border text-center">
                            {{ \Carbon\Carbon::parse($row->last_updated)->format('d M Y') }}
                        </td>
                        <td class="p-2 border text-center">
                            <a href="{{ route('teacher.iaMarks.view', [
                                $row->paper_id,
                                $row->semester_id,
                                $row->section
                            ]) }}"
                            class="px-4 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                            View 
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</div>
@endsection
