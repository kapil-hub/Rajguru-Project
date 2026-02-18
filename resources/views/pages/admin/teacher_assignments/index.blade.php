@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-800">
            Teacher Class Assignment
        </h1>
        <p class="text-sm text-gray-500 mt-1">
            Assign teachers to courses, semesters, sections and subjects
        </p>
    </div>


    <!-- ================= FORM CARD ================= -->
    <div class="bg-white rounded-xl shadow-sm border p-6 mb-10">
        <h2 class="text-lg font-semibold text-gray-700 mb-6">
            New Assignment
        </h2>

        <form method="POST"
              action="{{ route('admin.teacher.assignments.store') }}"
              class="space-y-6">
            @csrf

            <!-- Row 1 : Main Select Inputs -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @if(auth('admin')->check())
                    <select name="teacher_id" required class="form-select rounded-lg">
                        <option value="">Teacher</option>
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                @endif
                @if(auth('teacher')->check())
                    <label
                        class="ml-4 flex items-center gap-2 border border-gray-300 rounded-md px-3 py-2 cursor-pointer"
                    >
                        <span class="text-sm font-medium">{{ auth('teacher')->user()->name }}</span>
                    </label>
                @endif

                <select name="course_id" required class="form-select rounded-lg">
                    <option value="">Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
        

                @php
                    $currentYear = date('Y');
                    $nextYear = $currentYear + 1;
                    $prevYear = $currentYear - 1;

                    $currentSession = $currentYear . '-' . substr($nextYear, -2);
                    $previousSession = $prevYear . '-' . substr($currentYear, -2);
                @endphp

                <select name="academic_session" class="form-input w-full">
                    <option value="">Select Academic Session</option>
                    <option value="{{ $currentSession }}">{{ $currentSession }}</option>
                    <option value="{{ $previousSession }}">{{ $previousSession }}</option>
                </select>
             
                <select name="semester_id" required class="form-select rounded-lg">
                    <option value="">Semester</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                    @endforeach
                </select>

                <select name="section" required class="form-select rounded-lg">
                    <option value="">Section</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec }}">{{ $sec }}</option>
                    @endforeach
                </select>

                <select name="paper_master_id" required class="form-select rounded-lg paper-select">
                    <option value="">Search Paper</option>
                    @foreach($papers as $paper)
                        <option value="{{ $paper->id }}">
                            {{ $paper->code }} - {{ $paper->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Row 2 : Role Selection -->
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">
                    Teacher Role for this Class
                </p>

                <div class="flex flex-wrap gap-4">
                    @foreach([
                        'is_lecture' => 'Lecture',
                        'is_tute' => 'Tutorial',
                        'is_practical' => 'Practical',
                        'is_coordinator' => 'Coordinator'
                    ] as $name => $label)
                        <label
                            class="flex items-center gap-2 px-4 py-2 border rounded-lg cursor-pointer
                                   bg-gray-50 hover:bg-gray-100 transition">
                            <input type="checkbox"
                                   name="{{ $name }}"
                                   class="rounded text-blue-600 focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Row 3 : Action Button -->
            <div class="flex justify-end">
                <button type="submit"
                    class="inline-flex items-center px-6 py-2 rounded-lg
                           bg-blue-600 text-white font-medium
                           hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    Save
                </button>
            </div>
        </form>
    </div>

    <!-- ================= ASSIGNED LIST ================= -->
    <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-700">
                Assigned Classes
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Teacher</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Course</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Semester</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Section</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Subject</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Roles</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                            Status
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    @foreach($assignments as $a)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-800">
                                {{ $a->teacher->name }}
                            </td>
                            <td class="px-4 py-3">{{ $a->course->name }}</td>
                            <td class="px-4 py-3">{{ $a->semester->name }}</td>
                            <td class="px-4 py-3">{{ $a->section }}</td>
                            <td class="px-4 py-3">{{ $a->paperMaster->name }}</td>
                            <td class="px-4 py-3 text-center space-x-1">
                                @if($a->is_lecture)
                                    <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-700">Lecture</span>
                                @endif
                                @if($a->is_tute)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">Tutorial</span>
                                @endif
                                @if($a->is_practical)
                                    <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-700">Practical</span>
                                @endif
                                @if($a->is_coordinator)
                                    <span class="px-2 py-1 text-xs rounded bg-orange-100 text-orange-700">Coordinator</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($a->is_active)
                                    <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                                        Active
                                    </span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <form method="POST"
                                    action="{{ route('admin.teacher.assignments.status', $a->id) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit"
                                        class="px-3 py-1 text-xs rounded
                                        {{ $a->is_active
                                            ? 'bg-red-100 text-red-700 hover:bg-red-200'
                                            : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        {{ $a->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach

                    @if($assignments->isEmpty())
                        <tr>
                            <td colspan="6" class="text-center py-6 text-gray-500">
                                No assignments found
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.paper-select').forEach(el => {
            new TomSelect(el, {
                placeholder: 'Search paper...',
                allowEmptyOption: true,
                create: false,
                maxOptions: null
            });
        });
    });
</script>
@endsection
