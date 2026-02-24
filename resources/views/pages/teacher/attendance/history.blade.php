@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold mb-6">Attendance History</h1>

    <div class="bg-white shadow rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3">Course</th>
                    <th class="px-6 py-3">Paper</th>
                    <th class="px-6 py-3">Month</th>
                    <th class="px-6 py-3 text-center">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($records as $r)
                <tr class="border-b">
                    <td class="px-6 py-3">{{ $r->paper->course->name }}</td>
                    <td class="px-6 py-3">{{ $r->paper->name }}</td>
                    <td class="px-6 py-3">{{ \Carbon\Carbon::createFromDate((int)$r->year, (int)$r->month, 1)->format('F Y') }}</td>
                    <td class="px-6 py-3 text-center">
                        <a href="{{ route('teacher.attendance.history.show',
                            [$r->paper_master_id, $r->month, $r->year]) }}"
                           class="px-4 py-1 bg-blue-600 text-white rounded">
                            View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-500">
                        No attendance history found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
