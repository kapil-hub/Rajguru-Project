@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <h2 class="text-2xl font-bold mb-4">Import Confirmation</h2>

    {{-- Invalid Rows --}}
    @if(!empty($invalidRows) && count($invalidRows) > 0)
        <div class="mb-6 bg-red-100 text-red-700 p-4 rounded-lg">
            <strong>Invalid / Duplicate Rows (Not Imported)</strong>
        </div>

        <div class="overflow-x-auto mb-6">
            <table class="min-w-full border">
                <thead class="bg-red-50">
                    <tr>
                        <th class="border px-3 py-2">Row</th>
                        <th class="border px-3 py-2">Reason</th>
                        <th class="border px-3 py-2">Department</th>
                        <th class="border px-3 py-2">Course</th>
                        <th class="border px-3 py-2">Semester</th>
                        <th class="border px-3 py-2">Code</th>
                        <th class="border px-3 py-2">Number Of Lectures</th>
                        <th class="border px-3 py-2">Number Of Tutorials</th>
                        <th class="border px-3 py-2">Number Of Practicals</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invalidRows as $row)
                        <tr class="bg-red-50">
                            <td class="border px-3 py-2">{{ $row['row'] }}</td>
                            <td class="border px-3 py-2 text-red-600">{{ $row['reason'] }}</td>
                            <td class="border px-3 py-2">{{ $row['data']['department_name'] ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $row['data']['course_name'] ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $row['data']['semester'] ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $row['data']['paper_code'] ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $row['data']['number_of_lectures'] ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $row['data']['number_of_tutorials'] ?? '-' }}</td>
                            <td class="border px-3 py-2">{{ $row['data']['number_of_practicals'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Valid Rows --}}
    @if(!empty($validRows) && count($validRows) > 0)
        <div class="mb-4 bg-green-100 text-green-700 p-4 rounded-lg">
            <strong>Valid Rows (Will Be Imported)</strong>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full border">
                <thead class="bg-green-50">
                    <tr>
                        <th class="border px-3 py-2">Department ID</th>
                        <th class="border px-3 py-2">Course ID</th>
                        <th class="border px-3 py-2">Semester</th>
                        <th class="border px-3 py-2">Code</th>
                        <th class="border px-3 py-2">Paper Name</th>
                        <th class="border px-3 py-2">Number Of Lectures</th>
                        <th class="border px-3 py-2">Number Of Tutorials</th>
                        <th class="border px-3 py-2">Number Of Practicals</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($validRows as $row)
                        <tr>
                            <td class="border px-3 py-2">{{ $row['dept_id'] }}</td>
                            <td class="border px-3 py-2">{{ $row['course_id'] }}</td>
                            <td class="border px-3 py-2">{{ $row['semester'] }}</td>
                            <td class="border px-3 py-2">{{ $row['code'] }}</td>
                            <td class="border px-3 py-2">{{ $row['name'] }}</td>
                            <td class="border px-3 py-2">{{ $row['number_of_lectures'] }}</td>
                            <td class="border px-3 py-2">{{ $row['number_of_tutorials'] }}</td>
                            <td class="border px-3 py-2">{{ $row['number_of_practicals'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <form action="{{ route('papers.import.confirm') }}" method="POST" class="mt-6">
            @csrf
            <button type="submit"
                class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                Confirm Import
            </button>
        </form>
    @endif

</div>
@endsection
