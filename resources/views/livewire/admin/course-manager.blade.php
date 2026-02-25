<div>
    <!-- PAGE HEADER -->
    <div class="bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-indigo-700">
                    Course Management
                </h1>
                <p class="text-gray-500 text-sm mt-1">
                    Manage and control all courses
                </p>
            </div>

            <button wire:click="openModal"
                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 
                       rounded-xl shadow-md transition duration-200">
                + Add Course
            </button>
        </div>
        @if (session()->has('success'))
            <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-700 font-medium">
                {{ session('success') }}
            </div>
        @endif
    <!-- Filter Card -->
    <div class="bg-white rounded-4x4 shadow-md p-5 mb-6">
        <div class="flex gap-4">
            <input type="text"
                wire:model.live="search"
                placeholder="Search course..."
                class="border border-gray-300 rounded-lg px-4 py-2
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none">

            <select wire:model.live="statusFilter"
                class="border border-gray-300 rounded-lg px-4 py-2
                       focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                <option value="">All Status</option>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
    </div>

    <!-- Course Table Card -->
    <div class="bg-white rounded-2xl shadow-md overflow-hidden">

        <table class="w-full text-sm">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="text-left px-6 py-3">Name</th>
                    <th class="text-center px-6 py-3">Dept</th>
                    <th class="text-center px-6 py-3">Program</th>
                    <th class="text-center px-6 py-3">Type</th>
                    <th class="text-center px-6 py-3">Status</th>
                    <th class="text-right px-6 py-3">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y">
                @foreach($courses as $course)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-medium text-gray-800">
                        {{ $course->name }}
                    </td>

                    <td class="text-center">{{ $course->dept_id }}</td>
                    <td class="text-center">{{ $course->program_code }}</td>
                    <td class="text-center">{{ $course->types }}</td>

                    <td class="text-center">
                        <span class="px-3 py-1 text-xs rounded-full font-semibold
                            {{ $course->status 
                                ? 'bg-green-100 text-green-700' 
                                : 'bg-red-100 text-red-700' }}">
                            {{ $course->status ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td class="text-right px-6 py-4 space-x-3">
                        <button wire:click="edit({{ $course->id }})"
                            class="text-indigo-600 hover:underline font-medium">
                            Edit
                        </button>

                        <button wire:click="delete({{ $course->id }})"
                            class="text-red-600 hover:underline font-medium">
                            Delete
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
            <!-- MODAL -->
        @if($isOpen)
            <div class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
                <div class="bg-white w-full max-w-lg rounded-2xl shadow-xl p-6">

                    <h2 class="text-xl font-bold text-indigo-700 mb-5">
                        {{ $courseId ? 'Edit Course' : 'Add Course' }}
                    </h2>

                    <div class="space-y-4">

                        <!-- Course Name -->
                        <div>
                            <label class="text-sm font-medium">Course Name</label>
                            <input type="text" wire:model="name"
                                class="w-full border rounded-lg px-4 py-2 mt-1">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Department Dropdown -->
                        <div>
                            <label class="text-sm font-medium">Department</label>
                            <select wire:model="dept_id"
                                class="w-full border rounded-lg px-4 py-2 mt-1">
                                <option value="">Select Department</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('dept_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <!-- Program Code -->
                        <div>
                            <label class="text-sm font-medium">Program Code</label>
                            <input type="text" wire:model="program_code"
                                class="w-full border rounded-lg px-4 py-2 mt-1">
                        </div>

                        <!-- Type -->
                        <div>
                            <label class="text-sm font-medium">Course Type</label>
                            <select wire:model="types"
                                class="w-full border rounded-lg px-4 py-2 mt-1">
                                <option value="">Select Type</option>
                                <option value="1">UG</option>
                                <option value="2">PG</option>
                            </select>
                        </div>

                        <!-- Status Checkbox -->
                        <div class="flex items-center gap-2 mt-2">
                            <input type="checkbox" wire:model="status"
                                class="h-4 w-4 text-indigo-600 rounded" {{ $status ?'checked':' ' }}>
                            <label class="text-sm font-medium">Active</label>
                        </div>

                        <!-- Buttons -->
                        <div class="flex justify-end gap-3 pt-4">
                            <button wire:click="$set('isOpen', false)"
                                class="px-4 py-2 bg-gray-200 rounded-lg">
                                Cancel
                            </button>

                            <button wire:click="store"
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="p-5">
                {{ $courses->links() }}
            </div>
        </div>

</div>