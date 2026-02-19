@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto p-6 space-y-6">

<h2 class="text-2xl font-bold">Create Student</h2>

<form method="POST" action="{{ route('students.store') }}"
      class="bg-white rounded-xl shadow p-6 space-y-8">
@csrf

{{-- ================= BASIC INFO ================= --}}
<section>
<h3 class="font-semibold mb-4">Basic Information</h3>
<div class="grid md:grid-cols-3 gap-4">

<input name="name" placeholder="Student Name"
 class="border rounded-lg p-2 w-full">
 <div>
    <input  
        class="border rounded-lg p-2 w-full" name="control_number" placeholder="Control Number">
</div>
<input name="admission_academic_year" placeholder="Academic Year"
 class="border rounded-lg p-2 w-full">

<input name="email" placeholder="Email"
 class="border rounded-lg p-2 w-full">

<input name="mobile" placeholder="Mobile"
 class="border rounded-lg p-2 w-full">

</div>
</section>

{{-- ================= ACADEMIC ================= --}}
<section>
<h3 class="font-semibold mb-4">Academic Information</h3>
<div class="grid md:grid-cols-3 gap-4">

<input name="roll_number" placeholder="Roll Number"
 class="border rounded-lg p-2 w-full">

<input name="college_roll_number" placeholder="College Roll Number"
 class="border rounded-lg p-2 w-full">

<select name="department_id" class="border rounded-lg p-2 w-full">
<option value="">Department</option>
@foreach($departments as $d)
<option value="{{ $d->id }}">{{ $d->name }}</option>
@endforeach
</select>

<select name="course_id" class="border rounded-lg p-2 w-full">
<option value="">Course</option>
@foreach($courses as $c)
<option value="{{ $c->id }}">{{ $c->name }}</option>
@endforeach
</select>

<input name="current_semester" placeholder="Current Semester"
 class="border rounded-lg p-2 w-full">

<input name="section" placeholder="Section"
 class="border rounded-lg p-2 w-full">

</div>
</section>

{{-- ================= PARENTS ================= --}}
<section>
<h3 class="font-semibold mb-4">Parent Details</h3>
<div class="grid md:grid-cols-2 gap-4">

<input name="father_name" placeholder="Father Name"
 class="border rounded-lg p-2 w-full">

<input name="mother_name" placeholder="Mother Name"
 class="border rounded-lg p-2 w-full">

<input name="parents_contact_number" placeholder="Parent Mobile"
 class="border rounded-lg p-2 w-full">

<input name="parents_email_id" placeholder="Parent Email"
 class="border rounded-lg p-2 w-full">

</div>
</section>

{{-- ================= PAPERS ================= --}}
<section>
<h3 class="text-xl font-semibold">📚 Student Papers</h3>

<div class="bg-gray-50 p-4 rounded-xl mb-6">
<div class="flex justify-between mb-2">
<strong>Current Papers</strong>
<button type="button" onclick="addPaper(false)">➕ Add</button>
</div>
<div id="current-papers"></div>
</div>

<div class="bg-red-50 p-4 rounded-xl">
<div class="flex justify-between mb-2">
<strong>Backlog Papers</strong>
<button type="button" onclick="addPaper(true)">➕ Add</button>
</div>
<div id="backlog-papers"></div>
</div>
</section>

<div class="flex justify-end">
<button class="bg-indigo-600 text-white px-6 py-2 rounded-lg">
Create Student
</button>
</div>

</form>
</div>

@include('pages.students.partials.paper-js')
@endsection
