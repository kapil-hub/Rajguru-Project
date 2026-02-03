@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto px-4 py-6">

    <h1 class="text-2xl font-bold mb-6">Edit Registration Window</h1>
        <div class="bg-white shadow rounded-xl p-6">
            <form method="POST" action="{{ route('admin.registration-window.update', $window) }}">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block font-medium mb-1">Department</label>
                        <select name="department_id" class="w-full border rounded px-3 py-2" disabled>
                            <option value="{{ $window->department_id }}">{{ $window->department->name }}</option>
                        </select>
                        <small class="text-gray-500">Department cannot be changed</small>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Course</label>
                        <select name="course_id" class="w-full border rounded px-3 py-2" disabled>
                            <option value="{{ $window->course_id }}">{{ $window->course->name }}</option>
                        </select>
                        <small class="text-gray-500">Course cannot be changed</small>
                    </div>

                    <div>
                        <label class="block font-medium mb-1">Start Date</label>
                        <input type="date" name="start_date" value="{{ $window->start_date }}" class="w-full border rounded px-3 py-2">
                    </div>

                    <div>
                        <label class="block font-medium mb-1">End Date</label>
                        <input type="date" name="end_date" value="{{ $window->end_date }}" class="w-full border rounded px-3 py-2">
                    </div>
                </div>

                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700">
                    Update Window
                </button>
            </form>
        </div>
</div>
@endsection
