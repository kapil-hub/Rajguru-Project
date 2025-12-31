@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">

<h2 class="text-2xl font-bold mb-4">Mark Attendance</h2>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <select id="paper_id" class="form-select">
        <option value="">Select Subject</option>
        @foreach($papers as $paper)
            <option value="{{ $paper->id }}">{{ $paper->name }}</option>
        @endforeach
    </select>

    <input type="date" id="date" class="form-input">
    <select name="course_id" required>
        <option value="">Select Course</option>
        @foreach($courses as $course)
            <option value="{{ $course->id }}">{{ $course->name }}</option>
        @endforeach
    </select>

    <select name="semester_id" required>
        <option value="">Select Semester</option>
        @foreach($semesters as $semester)
            <option value="{{ $semester }}">{{ $semester }}</option>
        @endforeach
    </select>

    <select name="section" required>
        <option value="">Select Section</option>
        <option value="A">A</option>
        <option value="B">B</option>
    </select>
    <button onclick="loadStudents()" class="bg-blue-600 text-white px-4 py-2 rounded">
        Load Students
    </button>
</div>

<form method="POST" action="{{ route('teacher.attendance.store') }}">
    @csrf
    <input type="hidden" name="paper_id" id="form_paper_id">
    <input type="hidden" name="date" id="form_date">

    <div id="student-list"></div>

    <button class="mt-4 bg-green-600 text-white px-6 py-2 rounded">
        Save Attendance
    </button>
</form>

</div>

<script>
function loadStudents() {
    let paper = document.getElementById('paper_id').value;
    let date = document.getElementById('date').value;

    document.getElementById('form_paper_id').value = paper;
    document.getElementById('form_date').value = date;

    fetch('{{ route("teacher.attendance.load") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ paper_id: paper, date: date })
    })
    .then(res => res.text())
    .then(html => document.getElementById('student-list').innerHTML = html);
}
</script>
@endsection
