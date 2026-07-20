@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

<h2 class="text-2xl font-bold">Create Student</h2>

@if ($errors->any())
    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4 text-red-700">
        <div class="font-semibold">Please fix the following errors:</div>
        <ul class="mt-2 list-disc pl-5 text-sm">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('students.store') }}"
      class="bg-white rounded-xl shadow p-6 space-y-8">
@csrf

{{-- ================= BASIC INFO ================= --}}
<section>
<h3 class="font-semibold mb-4">Basic Information</h3>
<div class="grid md:grid-cols-3 gap-4">

<div>
<input name="name" value="{{ old('name') }}" placeholder="Student Name"
 class="border rounded-lg p-2 w-full">
@error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>
 <div>
    <input  
        class="border rounded-lg p-2 w-full" name="control_number" value="{{ old('control_number') }}" placeholder="Control Number">
    @error('control_number') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>
<div>
<input name="admission_academic_year" value="{{ old('admission_academic_year') }}" placeholder="Academic Year"
 class="border rounded-lg p-2 w-full">
@error('admission_academic_year') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="email" type="email" value="{{ old('email') }}" placeholder="Email"
 class="border rounded-lg p-2 w-full">
@error('email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="mobile" value="{{ old('mobile') }}" placeholder="Mobile"
 class="border rounded-lg p-2 w-full">
@error('mobile') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

</div>
</section>

{{-- ================= ACADEMIC ================= --}}
<section>
<h3 class="font-semibold mb-4">Academic Information</h3>
<div class="grid md:grid-cols-3 gap-4">

<div>
<input name="roll_number" value="{{ old('roll_number') }}" placeholder="Roll Number"
 class="border rounded-lg p-2 w-full">
@error('roll_number') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="college_roll_number" value="{{ old('college_roll_number') }}" placeholder="College Roll Number"
 class="border rounded-lg p-2 w-full">
@error('college_roll_number') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<select name="department_id" class="border rounded-lg p-2 w-full">
<option value="">Department</option>
@foreach($departments as $d)
<option value="{{ $d->id }}" @selected(old('department_id') == $d->id)>{{ $d->name }}</option>
@endforeach
</select>
@error('department_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<select name="course_id" class="border rounded-lg p-2 w-full">
<option value="">Course</option>
@foreach($courses as $c)
<option value="{{ $c->id }}" @selected(old('course_id') == $c->id)>{{ $c->name }}</option>
@endforeach
</select>
@error('course_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="current_semester" value="{{ old('current_semester') }}" placeholder="Current Semester"
 class="border rounded-lg p-2 w-full">
@error('current_semester') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="section" value="{{ old('section') }}" placeholder="Section"
 class="border rounded-lg p-2 w-full">
@error('section') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

</div>
</section>

{{-- ================= PARENTS ================= --}}
<section>
<h3 class="font-semibold mb-4">Parent Details</h3>
<div class="grid md:grid-cols-2 gap-4">

<div>
<input name="father_name" value="{{ old('father_name') }}" placeholder="Father Name"
 class="border rounded-lg p-2 w-full">
@error('father_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="mother_name" value="{{ old('mother_name') }}" placeholder="Mother Name"
 class="border rounded-lg p-2 w-full">
@error('mother_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="parents_contact_number" value="{{ old('parents_contact_number') }}" placeholder="Parent Mobile"
 class="border rounded-lg p-2 w-full">
@error('parents_contact_number') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

<div>
<input name="parents_email_id" type="email" value="{{ old('parents_email_id') }}" placeholder="Parent Email"
 class="border rounded-lg p-2 w-full">
@error('parents_email_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
</div>

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
@error('papers.*.paper_id') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
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
