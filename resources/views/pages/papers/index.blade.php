@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Papers Management</h2>
            <p class="text-sm text-gray-500">
                Manage papers, import via Excel or add manually
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('papers.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                ➕ Add Paper
            </a>

            <a href="{{ route('papers.template') }}"
               class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                ⬇ Download Template
            </a>
        </div>
    </div>

    <!-- Success / Error -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- IMPORT BOX (RESTORED ✅) -->
    <div class="bg-white shadow rounded-xl p-5 mb-6">
        <form action="{{ route('papers.import') }}"
              method="POST"
              enctype="multipart/form-data"
              class="flex flex-col md:flex-row md:items-end gap-4">
            @csrf

            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Import Papers (Excel)
                </label>
                <input type="file" name="file" required
                       class="w-full border rounded-lg px-3 py-2
                              focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            </div>

            <button type="submit"
                    class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                📤 Upload
            </button>
        </form>
    </div>

    <!-- SEARCH -->
    <div class="bg-white shadow rounded-xl p-4 mb-4">
        <form method="GET"
              action="{{ route('papers.index') }}"
              class="flex flex-col md:flex-row gap-3">

            <div class="relative flex-1">
                <input type="text"
                       name="search"
                       value="{{ $search ?? '' }}"
                       placeholder="Search by paper name, code, department or course..."
                       class="w-full pl-10 pr-4 py-2 border rounded-lg
                              focus:ring-2 focus:ring-indigo-500">
                <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
            </div>

            <div class="flex gap-2">
                <button type="submit"
                        class="px-5 py-2 bg-indigo-600 text-white rounded-lg">
                    Search
                </button>

                @if(!empty($search))
                    <a href="{{ route('papers.index') }}"
                       class="px-5 py-2 bg-gray-200 rounded-lg">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- SUMMARY -->
    <div class="text-sm text-gray-600 mb-2">
        Showing {{ $papers->firstItem() ?? 0 }} – {{ $papers->lastItem() ?? 0 }}
        of {{ $papers->total() }} papers
    </div>

    <!-- TABLE -->
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Department</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Course</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Semester</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Paper Code</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Paper Name</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Paper Type</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Credit Of  Lectures</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Credit Of Tutorials</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Credit Of Practicals</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold"> Marks Breakup</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                    @forelse($papers as $paper)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">{{ $paper->department->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $paper->course->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $paper->semester }}</td>
                            <td class="px-4 py-3 font-medium">{{ $paper->code }}</td>
                            <td class="px-4 py-3">{{ $paper->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                    {{ $paper->paper_type }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 text-xs rounded-full
                                    {{ $paper->status === 'Active'
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700' }}">
                                    {{ $paper->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ $paper->number_of_lectures }}</td>
                            <td class="px-4 py-3 font-medium">{{ $paper->number_of_tutorials }}</td>
                            <td class="px-4 py-3">{{ $paper->number_of_practicals }}</td>
                            <td> <a href = "/teacher/marksBreakup/{{ $paper->id }}" class= "bg-blue-100 text-blue-700 link" >Breakup</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7"
                                class="px-4 py-6 text-center text-gray-500">
                                No papers found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION -->
        @if($papers->hasPages())
            <div class="p-4 border-t">
                {{ $papers->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
