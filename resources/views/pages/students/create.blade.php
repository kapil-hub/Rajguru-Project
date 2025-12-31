@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Add New Student</h2>
        <a href="{{ route('students.index') }}"
           class="px-4 py-2 border rounded-lg hover:bg-gray-50">
            ← Back
        </a>
    </div>

    <form method="POST" action="{{ route('students.store') }}"
          class="bg-white rounded-xl shadow p-6 space-y-8">
        @csrf

        <!-- ================= BASIC INFORMATION ================= -->
        <section>
            <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Student Name</label>
                    <input name="name" required
                           placeholder="Enter full name"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Control Number</label>
                    <input name="control_number" required
                           placeholder="College control number"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Admission Academic Year</label>
                    <input name="admission_academic_year" required
                           placeholder="2024-2025"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Student Email</label>
                    <input name="email" type="email" required
                           placeholder="student@example.com"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Mobile Number</label>
                    <input name="mobile" required
                           placeholder="10-digit mobile number"
                           class="border rounded-lg p-2 w-full">
                </div>
            </div>
        </section>

        <!-- ================= ACADEMIC INFORMATION ================= -->
        <section>
            <h3 class="text-lg font-semibold mb-4">Academic Information</h3>

            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Roll Number</label>
                    <input name="roll_number" required
                           placeholder="University roll number"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Department</label>
                    <select name="department_id" required
                            class="border rounded-lg p-2 w-full">
                        <option value="">Select department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Course</label>
                    <select name="course_id" required
                            class="border rounded-lg p-2 w-full">
                        <option value="">Select course</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}">{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Current Semester</label>
                    <input name="current_semester" required
                           placeholder="Eg: 1, 2, 3"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Section</label>
                    <input name="section" required
                           placeholder="Eg: A / B"
                           class="border rounded-lg p-2 w-full">
                </div>
            </div>
        </section>

        <!-- ================= PARENT INFORMATION ================= -->
        <section>
            <h3 class="text-lg font-semibold mb-4">Parent / Enrollment Details</h3>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Father Name</label>
                    <input name="father_name" required
                           placeholder="Father full name"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Mother Name</label>
                    <input name="mother_name" required
                           placeholder="Mother full name"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Parent Contact Number</label>
                    <input name="parents_contact_number" required
                           placeholder="Primary parent contact"
                           class="border rounded-lg p-2 w-full">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Parent Email</label>
                    <input name="parents_email_id" type="email" required
                           placeholder="parent@example.com"
                           class="border rounded-lg p-2 w-full">
                </div>
            </div>
        </section>

        <!-- ================= ACTIONS ================= -->
        <div class="flex justify-end gap-3 pt-4">
            <a href="{{ route('students.index') }}"
               class="px-4 py-2 border rounded-lg">
                Cancel
            </a>
            <button class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                Save Student
            </button>
        </div>

    </form>
</div>
@endsection
