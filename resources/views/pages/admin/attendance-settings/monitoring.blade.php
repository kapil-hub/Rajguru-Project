@extends('layouts.app')

@section('content')

<div class="p-6 space-y-6">

    {{-- ===================== --}}
    {{-- TOP SUMMARY CARDS --}}
    {{-- ===================== --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

        <div class="bg-white rounded-2xl shadow p-5">
            <p class="text-gray-500 text-sm">Total Classes</p>
            <h2 class="text-3xl font-bold text-indigo-600">{{ $totalClasses }}</h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <p class="text-gray-500 text-sm">Marked</p>
            <h2 class="text-3xl font-bold text-green-600">{{ $markedCount }}</h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <p class="text-gray-500 text-sm">Not Marked</p>
            <h2 class="text-3xl font-bold text-red-600">{{ $notMarkedCount }}</h2>
        </div>

        <div class="bg-white rounded-2xl shadow p-5">
            <p class="text-gray-500 text-sm">Month</p>
            <h2 class="text-2xl font-semibold text-indigo-600">
                {{ \Carbon\Carbon::create()->month($month)->format('F') }} {{ $year }}
            </h2>
        </div>

    </div>

    {{-- ===================== --}}
    {{-- FILTER SECTION --}}
    {{-- ===================== --}}
    <div class="bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
        <form method="GET" class="flex flex-wrap items-end gap-4">

            {{-- Month --}}
            <div>
                <label class="text-sm text-gray-600 block mb-1">Month</label>
                <select name="month" class="border rounded-lg px-4 py-2">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->setMonth($m)->format('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Year --}}
            <div>
                <label class="text-sm text-gray-600 block mb-1">Year</label>
                <select name="year" class="border rounded-lg px-4 py-2">
                    @for($y = now()->year; $y >= now()->year - 3; $y--)
                        <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>
                            {{ $y }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Teacher --}}
            <div>
                <label class="text-sm text-gray-600 block mb-1">Teacher</label>
                <select name="teacher_id" class="border rounded-lg px-4 py-2">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}"
                            {{ $teacherId == $teacher->id ? 'selected' : '' }}>
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Status --}}
            <div>
                <label class="text-sm text-gray-600 block mb-1">Status</label>
                <select name="status" class="border rounded-lg px-4 py-2">
                    <option value="">All</option>
                    <option value="marked" {{ $status === 'marked' ? 'selected' : '' }}>
                        Marked
                    </option>
                    <option value="not_marked" {{ $status === 'not_marked' ? 'selected' : '' }}>
                        Not Marked
                    </option>
                </select>
            </div>

            <button type="submit"
                class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">
                Apply
            </button>

        </form>
    </div>

    {{-- ===================== --}}
    {{-- ATTENDANCE TABLE --}}
    {{-- ===================== --}}
    <div class="bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600 overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-gray-600">
                <tr>
                    <th class="px-4 py-3 text-left">Teacher</th>
                    <th class="px-4 py-3 text-left">Course</th>
                    <th class="px-4 py-3 text-center">Semester</th>
                    <th class="px-4 py-3 text-center">Section</th>
                    <th class="px-4 py-3 text-left">Paper</th>
                    <th class="px-4 py-3 text-center">Status</th>
                </tr>
            </thead>

            <tbody class="divide-y">

                @forelse($records as $row)
                    <tr class="hover:bg-gray-50 transition">

                        <td class="px-4 py-3">
                            {{ optional(\App\Models\Teacher::find($row->teacher_id))->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3">
                            {{ optional(\App\Models\Courses::find($row->course_id))->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $row->semester_id }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            {{ $row->section }}
                        </td>

                        <td class="px-4 py-3">
                            {{ optional(\App\Models\Paper::find($row->paper_master_id))->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($row->is_marked)
                                <span class="px-3 py-1 text-xs rounded-full bg-green-100 text-green-700 font-medium">
                                    ✔ Marked
                                </span>
                            @else
                                <span class="px-3 py-1 text-xs rounded-full bg-red-100 text-red-700 font-medium">
                                    ✖ Not Marked
                                </span>
                            @endif
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-6 text-gray-500">
                            No records found.
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

        {{-- PAGINATION --}}
        <div class="p-4 border-t">
            {{ $records->withQueryString()->links() }}
        </div>

    </div>

</div>

@endsection
