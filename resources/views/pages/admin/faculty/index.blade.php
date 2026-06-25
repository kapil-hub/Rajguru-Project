@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Faculty Management</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Search, filter, and manage faculty members and their roles.
                </p>
            </div>
            <div>
                <a href="{{ route('admin.faculty.create') }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    <span class="text-lg">+</span> Add Faculty
                </a>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.faculty.index') }}" class="grid grid-cols-1 gap-4 md:grid-cols-4 items-end">
            <!-- Search Input -->
            <div class="space-y-2">
                <label for="search" class="text-sm font-medium text-gray-700">Search Faculty</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        🔍
                    </span>
                    <input type="text"
                           id="search"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Name, email, designation..."
                           class="w-full rounded-xl border border-gray-300 pl-9 pr-4 py-2.5 text-sm placeholder-gray-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                </div>
            </div>

            <!-- Department Filter -->
            <div class="space-y-2">
                <label for="department_id" class="text-sm font-medium text-gray-700">Department</label>
                <select id="department_id"
                        name="department_id"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    <option value="">All Departments</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="space-y-2">
                <label for="status" class="text-sm font-medium text-gray-700">Status</label>
                <select id="status"
                        name="status"
                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                    <option value="">All Statuses</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-2">
                <button type="submit"
                        class="flex-1 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'department_id', 'status']))
                    <a href="{{ route('admin.faculty.index') }}"
                       class="rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition text-center flex items-center justify-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-600 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4">#</th>
                        <th class="px-6 py-4">Name</th>
                        <th class="px-6 py-4">Designation</th>
                        <th class="px-6 py-4">Department</th>
                        <th class="px-6 py-4">Courses/Papers</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Roles</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 border-t border-gray-200">
                    @forelse($facultyUsers as $index => $f)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <!-- Index -->
                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $facultyUsers->firstItem() + $index }}
                            </td>
                            <!-- Name -->
                            <td class="px-6 py-4 font-bold text-gray-900">
                                {{ $f->name }}
                            </td>
                            <!-- Designation -->
                            <td class="px-6 py-4 text-gray-600">
                                {{ $f->designation }}
                            </td>
                            <!-- Department -->
                            <td class="px-6 py-4 text-gray-600 font-medium">
                                {{ $f->department->name ?? '-' }}
                            </td>
                            <!-- Courses/Papers -->
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 max-w-xs">
                                    @forelse($f->details as $d)
                                        <span class="inline-flex items-center rounded-lg bg-gray-100 px-2 py-1 text-xs font-medium text-gray-800 border border-gray-200" title="{{ $d->course->name ?? '-' }} - {{ $d->paperMaster->name ?? '-' }}">
                                            {{ $d->course->name ?? '-' }} : {{ $d->paperMaster->name ?? '-' }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-gray-400">No courses assigned</span>
                                    @endforelse
                                </div>
                            </td>
                            <!-- Email -->
                            <td class="px-6 py-4 text-gray-600 font-mono">
                                {{ $f->email }}
                            </td>
                            <!-- Roles -->
                            <td class="px-6 py-4">
                                <div class="scale-95 origin-left">
                                    <livewire:components.role-manager authType="teacher" :authId="$f->id" />
                                </div>
                            </td>
                            <!-- Status -->
                            <td class="px-6 py-4">
                                @if($f->status)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-700 border border-green-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-600"></span>
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-semibold text-red-700 border border-red-200">
                                        <span class="h-1.5 w-1.5 rounded-full bg-red-600"></span>
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <!-- Actions -->
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center items-center gap-2">
                                    <a href="{{ route('admin.faculty.edit', $f->id) }}"
                                       class="inline-flex items-center rounded-lg bg-yellow-50 px-3 py-1.5 text-xs font-semibold text-yellow-700 border border-yellow-200 hover:bg-yellow-100 transition">
                                        ✏️ Edit
                                    </a>
                                    <form action="{{ route('admin.faculty.delete', $f->id) }}" method="POST" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                onclick="return confirm('Are you sure you want to delete this faculty member?')"
                                                class="inline-flex items-center rounded-lg bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 border border-red-200 hover:bg-red-100 transition">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500 font-medium">
                                No faculty members found match your search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Section -->
        @if($facultyUsers->hasPages() || $facultyUsers->total() > 0)
            <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4 border-t border-gray-200 pt-6">
                <div class="text-sm text-gray-500">
                    Showing <span class="font-medium text-gray-900">{{ $facultyUsers->firstItem() ?? 0 }}</span> to
                    <span class="font-medium text-gray-900">{{ $facultyUsers->lastItem() ?? 0 }}</span> of
                    <span class="font-medium text-gray-900">{{ $facultyUsers->total() }}</span> faculty members
                </div>
                <div>
                    {{ $facultyUsers->links('pagination::tailwind') }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection