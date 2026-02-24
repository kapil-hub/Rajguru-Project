@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 p-6">

    <div class="max-w-8xl ounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-700">Department Management</h2>

            <button onclick="document.getElementById('addModal').classList.remove('hidden')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow">
                + Add Department
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto ">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-600 uppercase text-sm">
                        <th class="p-3 text-left">#</th>
                        <th class="p-3 text-left">Department Name</th>
                        <th class="p-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($departments as $dept)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3">{{ $dept->id }}</td>
                            <td class="p-3 font-medium">{{ $dept->name }}</td>
                            <td class="p-3 text-center space-x-2">

                                <!-- Edit -->
                                <button onclick="editDepartment({{ $dept->id }}, '{{ $dept->name }}')"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $departments->links() }}
        </div>

    </div>
</div>

<!-- Add / Edit Modal -->
<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white w-96 p-6 rounded-xl shadow-lg">
        <h3 class="text-lg font-bold mb-4">Add Department</h3>

        <form id="departmentForm" method="POST" action="{{ route('admin.departments.store') }}">
            @csrf
            <input type="text" name="name" id="deptName"
                class="w-full border p-2 rounded mb-4"
                placeholder="Enter Department Name">

            <div class="flex justify-end space-x-2">
                <button type="button"
                    onclick="document.getElementById('addModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-400 text-white rounded">
                    Cancel
                </button>

                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded">
                    Save
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editDepartment(id, name) {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('deptName').value = name;

    let form = document.getElementById('departmentForm');
    form.action = '/admin/departments/' + id;

    if (!document.getElementById('methodField')) {
        let method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PUT';
        method.id = 'methodField';
        form.appendChild(method);
    }
}
</script>

@endsection
