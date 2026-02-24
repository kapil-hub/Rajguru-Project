@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto  bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <h2 class="text-xl font-bold mb-4">Registration Window</h2>

    <form method="POST" action = "{{ route('admin.registration-window.store') }}">
        @csrf

        <select name="department_id" required class="mb-3 w-full border p-2">
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>

        <select name="course_id" required class="mb-3 w-full border p-2">
            @foreach($courses as $course)
                <option value="{{ $course->id }}">{{ $course->name }}</option>
            @endforeach
        </select>

        <div class="grid grid-cols-2 gap-4">
            <input type="date" name="start_date" required class="border p-2">
            <input type="date" name="end_date" required class="border p-2">
        </div>

        <button class="mt-4 px-6 py-2 bg-indigo-600 text-white rounded">
            Save
        </button>
    </form>
</div>
@endsection
