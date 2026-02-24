@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Import Preview</h1>
        <p class="text-sm text-gray-500">
            Review valid and invalid records before confirming import.
        </p>
    </div>

    <!-- Invalid Records -->
    @if(count($invalidRows))
    <div class="bg-red-50 border border-red-200 rounded-lg p-5 mb-8">
        <h2 class="text-lg font-semibold text-red-700 mb-3">
            ❌ Invalid Rows ({{ count($invalidRows) }})
        </h2>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-red-100">
                    <tr>
                        <th class="border px-3 py-2">Row</th>
                        <th class="border px-3 py-2">Errors</th>
                        <th class="border px-3 py-2">Student Name</th>
                        <th class="border px-3 py-2">Email</th>
                        <th class="border px-3 py-2">Department</th>
                        <th class="border px-3 py-2">Course</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invalidRows as $row)
                    <tr class="bg-white">
                        <td class="border px-3 py-2 text-center">
                            {{ $row['row_number'] }}
                        </td>
                        <td class="border px-3 py-2 text-red-600">
                            <ul class="list-disc list-inside">
                                @foreach($row['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="border px-3 py-2">{{ $row['data'][1] }}</td>
                        <td class="border px-3 py-2">{{ $row['data'][2] }}</td>
                        <td class="border px-3 py-2">{{ $row['data'][6] }}</td>
                        <td class="border px-3 py-2">{{ $row['data'][7] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- Valid Records -->
    @if(count($validRows))
    <div class="bg-green-50 border border-green-200 rounded-lg p-5 mb-8">
        <h2 class="text-lg font-semibold text-green-700 mb-3">
            ✅ Valid Rows ({{ count($validRows) }})
        </h2>

        <div class="overflow-x-auto max-h-96">
            <table class="min-w-full text-sm border">
                <thead class="bg-green-100 sticky top-0">
                    <tr>
                        <th class="border px-3 py-2">#</th>
                        <th class="border px-3 py-2">Student Name</th>
                        <th class="border px-3 py-2">Email</th>
                        <th class="border px-3 py-2">Mobile</th>
                        <th class="border px-3 py-2">Department</th>
                        <th class="border px-3 py-2">Course</th>
                        <th class="border px-3 py-2">Semester</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($validRows as $index => $row)
                    <tr class="bg-white">
                        <td class="border px-3 py-2 text-center">
                            {{ $index + 1 }}
                        </td>
                        <td class="border px-3 py-2">{{ $row[1] }}</td>
                        <td class="border px-3 py-2">{{ $row[2] }}</td>
                        <td class="border px-3 py-2">{{ $row[3] }}</td>
                        <td class="border px-3 py-2">{{ $row[6] }}</td>
                        <td class="border px-3 py-2">{{ $row[7] }}</td>
                        <td class="border px-3 py-2">{{ $row[8] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Confirm Button -->
        <div class="mt-6 flex justify-end">
            <form action="{{ route('students.import.confirm') }}" method="POST">
                @csrf
                <button
                    class="px-6 py-3 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
                    ✅ Confirm Import ({{ count($validRows) }} Students)
                </button>
            </form>
        </div>
    </div>
    @endif

    <!-- Back Button -->
    <div class="flex justify-start">
        <a href="{{ route('students.index') }}"
           class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
            ← Back
        </a>
    </div>

</div>
@endsection
