@extends('layouts.app')

@section('content')
<div class="p-4">

    <h2 class="text-xl font-semibold mb-4">Manage Students</h2>

    <div class="max-w-6xl mx-auto px-4 py-8">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Import Students</h1>
            <p class="text-sm text-gray-500">
                Download the template, fill student data, and import.
            </p>
        </div>
        
        <a href="{{  route('students.create') }}"
                class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg shadow hover:bg-green-700">
            + Create student
        </a>
        
        <!-- Download Template -->
        <a href="{{ route('students.template') }}"
           class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
            ⬇ Download Excel Template
        </a>
    </div>

    <!-- Upload Card -->
    <div class="bg-white rounded-xl shadow-md p-6">

        <form action="{{ route('students.import.preview') }}"
              method="POST"
              enctype="multipart/form-data">

            @csrf

            <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                <p class="text-gray-600 mb-2">Upload filled Excel file (.xlsx)</p>

                <input type="file"
                       name="file"
                       required
                       accept=".xlsx"
                       class="block mx-auto text-sm text-gray-600">

                @error('file')
                    <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Upload & Preview
                </button>
            </div>
        </form>
    </div>

</div>

    <div class="max-w-7xl mx-auto px-4 py-6">

    <!-- ================= HEADER ================= -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Manage Students</h1>
            <p class="text-sm text-gray-500">
                Search, browse and manage students
            </p>
        </div>
        
        <!-- Search -->
        <form method="GET" class="flex gap-2">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search name / control no"
                   class="w-64 px-4 py-2 border rounded-lg focus:ring focus:ring-indigo-200">

            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                🔍 Search
            </button>
        </form>
    </div>

    <!-- ================= TABLE CARD ================= -->
    <div class="bg-white rounded-xl shadow overflow-hidden">

        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                <tr>
                    <th class="p-3">Name</th>
                    <th class="p-3">Control No</th>
                    <th class="p-3">Course</th>
                    <th class="p-3">Semester</th>
                    <th class="p-3">Status</th>
                    <th class="p-3 text-right">Action</th>
                </tr>
            </thead>

            <tbody class="divide-y">
            @forelse($students as $student)
                <tr class="hover:bg-gray-50 transition">
                    <td class="p-3 font-medium">
                        {{ $student->name }}
                    </td>

                    <td class="p-3">
                        {{ $student->control_number }}
                    </td>

                    <td class="p-3">
                        {{ optional(optional($student->academic)->course)->name ?? '-' }}
                    </td>

                    <td class="p-3">
                        {{ optional($student->academic)->current_semester ?? '-' }}
                    </td>

                    <td class="p-3">
                        <span class="px-2 py-1 rounded text-xs font-semibold
                            {{ $student->status === 'active'
                                ? 'bg-green-100 text-green-700'
                                : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>

                    <td class="p-3 text-right space-x-2">
                        <a href="{{ route('students.show', $student) }}"
                           class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                            View
                        </a>

                        <a href="{{ route('students.edit', $student) }}"
                           class="px-3 py-1 bg-gray-600 text-white rounded hover:bg-gray-700">
                            Edit
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-6 text-center text-gray-500">
                        No students found 😕
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- ================= PAGINATION ================= -->
    <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div class="text-sm text-gray-500">
            @if($students->total() > 0)
                Showing {{ $students->firstItem() }} –
                {{ $students->lastItem() }}
                of {{ $students->total() }} students
            @endif
        </div>

        <div>
            {{ $students->links('pagination::tailwind') }}
        </div>

    </div>

</div>
</div>

@include('pages.students.partials.add-modal')
@include('pages.students.partials.edit-modal')

<script>
function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('addModal').classList.add('hidden');
}
function openEditModal(id) {
    document.getElementById('editForm').action = '/students/' + id;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>
@endsection
