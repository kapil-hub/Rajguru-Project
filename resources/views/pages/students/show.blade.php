@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
    @if(auth('admin')->check())
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-bold">Student Profile</h2>
            <a href="{{ route('students.edit',$student) }}"
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg">
                Edit
            </a>
        </div>
    @endif

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
            <div><strong>Exam Roll No:</strong> {{ optional($student->academic)->roll_number }}</div>
            <div><strong>College Roll No:</strong> {{ optional($student->academic)->college_roll_number }}</div>
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
                <th class="p-2">Paper Code</th>
                <th class="p-2">Paper Name</th>
                <th class="p-2">Paper Type</th>
                <th class="p-2">Semester</th>
            </tr>
        </thead>

        <tbody class="divide-y">
        @forelse($student->papers as $paper)
            <tr class="
                {{ $paper->is_backlog 
                    ? 'bg-red-50 text-red-800' 
                    : 'bg-white' }}
            ">
                <td class="p-2 font-medium">
                    {{ $paper->paper->code }}
                </td>

                <td class="p-2">
                    {{ $paper->paper->name }}

                    @if($paper->is_backlog)
                        <span class="ml-2 inline-flex items-center
                            text-xs font-semibold px-2 py-0.5
                            rounded-full bg-red-200 text-red-800">
                            Backlog
                        </span>
                    @endif
                </td>

                <td class="p-2">
                    {{ $paper->paper->paper_type }}
                </td>

                <td class="p-2 font-semibold">
                    {{ $paper->semester }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="p-3 text-center text-gray-500">
                    No papers chosen / assigned
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

    @if(auth('admin')->check() && !empty($academicHistory))
        <!-- ACADEMIC HISTORY (ADMIN ONLY) -->
        <div class="bg-white rounded-xl shadow p-6 mt-6 border-t-4 border-indigo-600">
            <h3 class="font-bold text-xl mb-4 text-indigo-700 flex items-center gap-2">
                📜 Academic History (All Semesters)
            </h3>
            
            <div class="space-y-6">
                @foreach($academicHistory as $sem => $history)
                    <div class="border rounded-xl p-4 bg-gray-50 shadow-sm">
                        <div class="flex flex-col md:flex-row md:items-center justify-between border-b pb-3 mb-4">
                            <div>
                                <span class="text-lg font-bold text-gray-800">Semester {{ $sem }}</span>
                                @if($history['is_current'])
                                    <span class="ml-2 inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-800">
                                        Current Active
                                    </span>
                                @else
                                    <span class="ml-2 inline-flex items-center text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-200 text-gray-800">
                                        Historical
                                    </span>
                                @endif
                            </div>
                            <div class="mt-2 md:mt-0 text-sm text-gray-600 flex flex-wrap gap-x-4">
                                <div><strong>Course:</strong> {{ $history['academic']->course_name ?? '-' }}</div>
                                <div><strong>Section:</strong> {{ $history['academic']->section ?? '-' }}</div>
                                <div><strong>Acad Year:</strong> {{ $history['academic']->current_academic_year ?? '-' }}</div>
                            </div>
                        </div>

                        <div class="grid md:grid-cols-2 gap-4 mb-4 text-sm text-gray-600">
                            <div><strong>Exam Roll No:</strong> {{ $history['academic']->roll_number ?? '-' }}</div>
                            <div><strong>College Roll No:</strong> {{ $history['academic']->college_roll_number ?? '-' }}</div>
                        </div>

                        <h4 class="font-semibold text-sm mb-2 text-gray-700">Papers Registered:</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm border bg-white rounded-lg overflow-hidden">
                                <thead class="bg-gray-100 text-gray-700 font-medium">
                                    <tr>
                                        <th class="p-2 text-left">Code</th>
                                        <th class="p-2 text-left">Paper Name</th>
                                        <th class="p-2 text-left">Type</th>
                                        <th class="p-2 text-center">Status</th>
                                        <th class="p-2 text-center">Year</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y text-gray-600">
                                    @forelse($history['papers'] as $paper)
                                        <tr class="{{ $paper->is_backlog ? 'bg-red-50 text-red-800' : '' }}">
                                            <td class="p-2 font-mono text-xs">{{ $paper->paper_code }}</td>
                                            <td class="p-2">
                                                {{ $paper->paper_name }}
                                                @if($paper->is_backlog)
                                                    <span class="ml-2 inline-flex items-center text-xs font-bold px-1.5 py-0.2 rounded bg-red-200 text-red-800">
                                                        Backlog
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="p-2 text-xs">{{ $paper->paper_type }}</td>
                                            <td class="p-2 text-center text-xs">
                                                @if($paper->is_backlog)
                                                    <span class="text-red-600">Backlog</span>
                                                @else
                                                    <span class="text-green-600">Regular</span>
                                                @endif
                                            </td>
                                            <td class="p-2 text-center text-xs">{{ $paper->academic_year }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="p-4 text-center text-gray-400 text-sm">
                                                No papers registered in this semester.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection
