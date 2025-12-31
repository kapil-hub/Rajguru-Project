@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow">    
     <div class="mb-6">
       <h1 class="text-2xl font-bold mb-4">Daily Attendance</h1>
        <p class="text-sm text-gray-500 mt-1">
            {{ $assignment->course->name }} •
            Semester {{ $assignment->semester->name }} •
            Section {{ $assignment->section }} •
            {{ $assignment->paperMaster->name }}
        </p>
    </div>
    <form method="POST" action="{{ route('teacher.attendance.daily.store') }}">
        @csrf
        <input type="hidden" name="course_id" value="{{ $assignment->course_id }}">
        <input type="hidden" name="semester_id" value="{{ $assignment->semester_id }}">
        <input type="hidden" name="section" value="{{ $assignment->section }}">
        <input type="hidden" name="paper_master_id" value="{{ $assignment->paper_master_id }}">
        <div class="mb-4">
            <label class="block mb-1">Attendance Date</label>
            <input type="date" name="attendance_date"
                   class="w-100 px-3 py-2 border rounded-lg" required>
        </div>

        <table class="w-full border mt-4">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">Student</th>
                     @if($assignment->is_lecture)
                        <th class="px-6 py-3 text-center">Lecture</th>
                    @endif

                    @if($assignment->is_tute)
                        <th class="px-6 py-3 text-center">Tute</th>
                    @endif

                    @if($assignment->is_practical)
                        <th class="px-6 py-3 text-center">Practical</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($students as $s)
                <tr>
                    <td class="p-2">{{ $s->name }}</td>
                    @if($assignment->is_lecture)
                        <td class="text-center">
                            <input type="checkbox" name="attendance[{{ $s->id }}][lecture]">
                        </td>
                    @endif

                    @if($assignment->is_tute)
                        <td class="text-center">
                            <input type="checkbox" name="attendance[{{ $s->id }}][tute]">
                        </td>
                     @endif

                    @if($assignment->is_practical)
                        <td class="text-center">
                            <input type="checkbox" name="attendance[{{ $s->id }}][practical]">
                        </td>
                     @endif
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-6 text-right">
            <button class="px-6 py-2 bg-blue-600 text-white rounded-lg">
                Save Attendance
            </button>
        </div>
    </form>
</div>
@endsection
