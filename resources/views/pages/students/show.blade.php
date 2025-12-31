@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">

    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold">Student Profile</h2>
        <a href="{{ route('students.edit',$student) }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
            Edit
        </a>
    </div>

    <!-- BASIC INFO -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-lg mb-4">Basic Information</h3>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div><strong>Name:</strong> {{ $student->name }}</div>
            <div><strong>Control No:</strong> {{ $student->control_number }}</div>
            <div><strong>Mobile:</strong> {{ $student->mobile }}</div>
            <div><strong>Email:</strong> {{ $student->email }}</div>
            <div><strong>Admission Year:</strong> {{ $student->admission_academic_year }}</div>
            <div><strong>Status:</strong> {{ ucfirst($student->status) }}</div>
        </div>
    </div>

    <!-- ACADEMIC DETAILS -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-lg mb-4">Academic Details</h3>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div><strong>Roll No:</strong> {{ optional($student->academic)->roll_number }}</div>
            <div><strong>Department:</strong> {{ optional(optional($student->academic)->department)->name }}</div>
            <div><strong>Course:</strong> {{ optional(optional($student->academic)->course)->name }}</div>
            <div><strong>Semester:</strong> {{ optional($student->academic)->current_semester }}</div>
            <div><strong>Section:</strong> {{ optional($student->academic)->section }}</div>
            <div><strong>Academic Year:</strong> {{ optional($student->academic)->current_academic_year }}</div>
        </div>
    </div>

    <!-- PARENTS DETAILS -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-lg mb-4">Parent Details</h3>
        <div class="grid md:grid-cols-3 gap-4 text-sm">
            <div><strong>Father:</strong> {{ optional($student->enrolDetails)->father_name }}</div>
            <div><strong>Mother:</strong> {{ optional($student->enrolDetails)->mother_name }}</div>
            <div><strong>Contact:</strong> {{ optional($student->enrolDetails)->parents_contact_number }}</div>
            <div><strong>Email:</strong> {{ optional($student->enrolDetails)->parents_email_id }}</div>
        </div>
    </div>

    <!-- PAPERS -->
    <div class="bg-white rounded-xl shadow p-6">
        <h3 class="font-semibold text-lg mb-4">Current Semester Papers</h3>

        <table class="w-full text-sm border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Code</th>
                    <th class="p-2">Title</th>
                    <th class="p-2">Type</th>
                    <th class="p-2">Semester</th>
                </tr>
            </thead>
            <tbody class="divide-y">
            @forelse($student->papers as $paper)
                <tr>
                    <td class="p-2">{{ $paper->paper->code }}</td>
                    <td class="p-2">{{ $paper->paper->name }}</td>
                    <td class="p-2">{{ $paper->paper->paper_type }}</td>
                    <td class="p-2">{{ $paper->semester }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="p-3 text-center text-gray-500">
                        No papers chossed/assigned
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
