@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 p-4">

    {{-- ══════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl shadow-xl p-6 border-l-8 border-indigo-600">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Student Management</h1>
                <p class="text-gray-500 mt-1">Browse, filter and manage all students</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('students.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Student
                </a>
                <a href="{{ route('students.template') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow transition text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Template
                </a>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         IMPORT CARD (collapsible)
    ══════════════════════════════════════════════ --}}
    <div x-data="{ open: false }" class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <button @click="open = !open"
            class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-indigo-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                </div>
                <span class="font-semibold text-gray-800">Import Students via Excel</span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-collapse class="px-6 pb-6 border-t border-gray-100">
            <p class="text-sm text-gray-500 mt-4 mb-4">Download the template, fill in student data, then upload it.</p>
            <form action="{{ route('students.import.preview') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center">
                    <p class="text-gray-500 text-sm mb-3">Upload filled Excel file (.xlsx)</p>
                    <input type="file" name="file" required accept=".xlsx"
                           class="block mx-auto text-sm text-gray-600">
                    @error('file')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="submit"
                            class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl shadow transition text-sm">
                        Upload &amp; Preview
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         STATS CARDS
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ request()->fullUrlWithQuery(['view' => 'current', 'page' => 1]) }}"
           class="bg-white rounded-2xl border {{ $view === 'current' ? 'border-indigo-400 ring-2 ring-indigo-200' : 'border-gray-200' }} shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($totalCurrent) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Current Students</p>
            </div>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['view' => 'past', 'page' => 1]) }}"
           class="bg-white rounded-2xl border {{ $view === 'past' ? 'border-amber-400 ring-2 ring-amber-200' : 'border-gray-200' }} shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($totalPast) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Old Students</p>
            </div>
        </a>

        <a href="{{ request()->fullUrlWithQuery(['view' => 'all', 'page' => 1]) }}"
           class="bg-white rounded-2xl border {{ $view === 'all' ? 'border-gray-500 ring-2 ring-gray-200' : 'border-gray-200' }} shadow-sm p-5 flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($totalAll) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">All Students</p>
            </div>
        </a>
    </div>

    {{-- ══════════════════════════════════════════════
         VIEW TOGGLE TABS
    ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-1.5 inline-flex gap-1">
        @foreach([
            ['view' => 'current', 'label' => 'Current Students',          'color' => 'indigo', 'count' => $totalCurrent],
            ['view' => 'past',    'label' => 'Old Students', 'color' => 'amber',  'count' => $totalPast],
            ['view' => 'all',     'label' => 'All Students',              'color' => 'gray',   'count' => $totalAll],
        ] as $tab)
            <a href="{{ request()->fullUrlWithQuery(['view' => $tab['view'], 'page' => 1]) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition
                      {{ $view === $tab['view']
                            ? 'bg-indigo-600 text-white shadow'
                            : 'text-gray-600 hover:bg-gray-100' }}">
                {{ $tab['label'] }}
                <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs
                             {{ $view === $tab['view'] ? 'bg-white/20 text-white' : 'bg-gray-200 text-gray-700' }}">
                    {{ $tab['count'] }}
                </span>
            </a>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════════════
         FILTERS
    ══════════════════════════════════════════════ --}}
    <form method="GET" class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        {{-- Keep the current view tab --}}
        <input type="hidden" name="view" value="{{ $view }}">

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            {{-- Search --}}
            <div class="relative lg:col-span-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input type="text" name="search"
                       value="{{ request('search') }}"
                       placeholder="Search name / control no…"
                       class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition">
            </div>

            {{-- Department --}}
            <select name="department_id"
                    class="py-2.5 px-3 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition bg-white">
                <option value="">All Departments</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>

            {{-- Course --}}
            <select name="course_id"
                    class="py-2.5 px-3 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition bg-white">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>

            {{-- Semester --}}
            <div class="flex gap-2">
                <select name="semester"
                        class="flex-1 py-2.5 px-3 text-sm rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition bg-white">
                    <option value="">All Semesters</option>
                    @for($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}" {{ request('semester') == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                        </option>
                    @endfor
                </select>

                <button type="submit"
                        class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow transition text-sm shrink-0">
                    Apply
                </button>

                @if(request()->hasAny(['search','department_id','course_id','semester']))
                    <a href="{{ route('students.index', ['view' => $view]) }}"
                       class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl transition text-sm shrink-0">
                        ✕
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ══════════════════════════════════════════════
         FLASH MESSAGES
    ══════════════════════════════════════════════ --}}
    @if(session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-700">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l7.879-7.879a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- ══════════════════════════════════════════════
         TABLE
    ══════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden">

        {{-- Table header bar --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                @if($view === 'current')
                    <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                    <span class="font-semibold text-gray-800 text-sm">Current Students</span>
                @elseif($view === 'past')
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span class="font-semibold text-gray-800 text-sm">Old Students</span>
                @else
                    <span class="w-2.5 h-2.5 rounded-full bg-gray-500"></span>
                    <span class="font-semibold text-gray-800 text-sm">All Students</span>
                @endif
            </div>
            <span class="text-xs text-gray-400">{{ $students->total() }} record(s)</span>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">#</th>
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">Name</th>
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">Control No.</th>
                        @if($view !== 'past')
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">Roll No.</th>
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">Department</th>
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">Course</th>
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">Sem</th>
                        @endif
                        <th class="px-5 py-4 text-left font-semibold text-gray-600 text-xs uppercase">Status</th>
                        <th class="px-5 py-4 text-right font-semibold text-gray-600 text-xs uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($students as $student)
                    <tr class="hover:bg-indigo-50/30 transition">
                        <td class="px-5 py-4 text-gray-400 font-medium text-xs">
                            {{ $students->firstItem() + $loop->index }}
                        </td>

                        {{-- Name + email --}}
                        <td class="px-5 py-4">
                            <p class="font-semibold text-gray-900">{{ $student->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $student->email }}</p>
                        </td>

                        {{-- Control Number --}}
                        <td class="px-5 py-4 font-mono text-gray-600 text-xs">
                            {{ $student->control_number ?? '—' }}
                        </td>

                        @if($view !== 'past')
                        {{-- Roll No --}}
                        <td class="px-5 py-4 font-mono text-gray-700 text-xs">
                            {{ $student->academic?->roll_number ?? '—' }}
                        </td>

                        {{-- Department --}}
                        <td class="px-5 py-4 text-gray-600 text-xs">
                            {{ $student->academic?->department?->name ?? '—' }}
                        </td>

                        {{-- Course --}}
                        <td class="px-5 py-4 text-gray-600 text-xs">
                            {{ $student->academic?->course?->name ?? '—' }}
                        </td>

                        {{-- Semester --}}
                        <td class="px-5 py-4 text-center">
                            @if($student->academic?->current_semester)
                                <span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                    Sem {{ $student->academic->current_semester }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        @endif

                        {{-- Status --}}
                        <td class="px-5 py-4">
                            @php
                                $statusVal = strtolower($student->status ?? '');
                                $statusClass = match($statusVal) {
                                    'active', '1' => 'bg-green-100 text-green-700',
                                    'inactive', '0' => 'bg-red-100 text-red-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                <span class="w-1.5 h-1.5 rounded-full
                                    {{ in_array($statusVal, ['active','1']) ? 'bg-green-500' : (in_array($statusVal, ['inactive','0']) ? 'bg-red-500' : 'bg-gray-400') }}">
                                </span>
                                {{ ucfirst($student->status ?? 'Unknown') }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="inline-flex gap-1.5">
                                <a href="{{ route('students.show', $student) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100 font-medium text-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    View
                                </a>
                                <a href="{{ route('students.edit', $student) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 font-medium text-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="px-6 py-16 text-center text-gray-400">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <p class="font-medium">No students found</p>
                                <p class="text-sm">Try adjusting your search or filters.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <p class="text-sm text-gray-500">
                    Showing <span class="font-semibold text-gray-700">{{ $students->firstItem() }}</span>
                    – <span class="font-semibold text-gray-700">{{ $students->lastItem() }}</span>
                    of <span class="font-semibold text-gray-700">{{ $students->total() }}</span> students
                </p>
                {{ $students->links('pagination::tailwind') }}
            </div>
        @else
            <div class="px-6 py-4 border-t border-gray-100 text-sm text-gray-500">
                {{ $students->total() }} student(s) found
            </div>
        @endif
    </div>

</div>
@endsection
