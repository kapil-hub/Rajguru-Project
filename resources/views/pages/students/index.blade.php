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

    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Name</th>
                <th class="border p-2">Control No</th>
                <th class="border p-2">Course</th>
                <th class="border p-2">Semester</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($students as $student)
            <tr>
                <td class="border p-2">{{ $student->name }}</td>
                <td class="border p-2">{{ $student->control_number }}</td>
                <td class="border p-2">{{ optional(optional($student->academic)->course)->name }}</td>
                <td class="border p-2">{{ optional($student->academic)->current_semester }}</td>
                <td class="border p-2">{{ $student->status }}</td>
                <td class="p-3 text-right space-x-2">
                    <a href="{{ route('students.show',$student) }}"
                        class="px-3 py-1 bg-blue-600 text-white rounded">View</a>
                    <a href="{{ route('students.edit',$student) }}"
                        class="px-3 py-1 bg-gray-600 text-white rounded">Edit</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
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
