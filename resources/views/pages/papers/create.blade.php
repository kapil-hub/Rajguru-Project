@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <!-- Header -->
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Add Paper</h2>
        <p class="text-sm text-gray-500">Manually add a new paper to the system</p>
    </div>


    <!-- Form Card -->
    <div class="bg-white shadow rounded-xl p-6">
        <form action="{{ route('papers.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Department + Course -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Department <span class="text-red-500">*</span>
                    </label>
                    <select name="dept_id"
                            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            required>
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('dept_id') == $dept->id ? 'selected' : '' }}>
                                {{ $dept->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Course <span class="text-red-500">*</span>
                    </label>
                    <select name="course_id"
                            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            required>
                        <option value="">Select Course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Semester + Code -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Semester <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="semester" value="{{ old('semester') }}"
                           class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                           placeholder="e.g. 1, 2, 3" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Paper Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code" value="{{ old('code') }}"
                           class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                           placeholder="e.g. CS101" required>
                </div>
            </div>

            <!-- Paper Name -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Paper Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                       placeholder="Enter paper name" required>
            </div>

            <!-- Type + Status -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Paper Type <span class="text-red-500">*</span>
                    </label>
                    <select name="paper_type"
                            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            required>
                        <option value="">Select Type</option>
                        @foreach ($paperTypes as $type )
                             <option value="{{ $type }}" {{ old('paper_type')==$type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                       
                    
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status"
                            class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                            required>
                        <option value="Active" {{ old('status')=='Active' ? 'selected' : '' }}>Active</option>
                        <option value="Inactive" {{ old('status')=='Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Lecture Credit <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="number_of_lectures" value="{{ old('number_of_lectures') }}" 
                        class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                        min="0" max="4" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tute Credit <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="number_of_tutorials" value="{{ old('number_of_tutorials') }}"
                        class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                         min="0" max="4" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Practical Credit <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="number_of_practicals" value="{{ old('number_of_practicals') }}"
                        class="w-full rounded-lg border px-3 py-2 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                         min="0" max="4" required>
                </div>
            </div>
            <!-- Actions -->
            <div class="flex justify-end gap-3 pt-4">
                <a href="{{ route('papers.index') }}"
                   class="px-4 py-2 rounded-lg border text-gray-600 hover:bg-gray-100 transition">
                    Cancel
                </a>

                <button type="submit"
                        class="px-6 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition">
                    Save Paper
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
