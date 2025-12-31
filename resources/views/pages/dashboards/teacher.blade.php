@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">

    <h1 class="text-2xl font-bold mb-6">Teacher Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold">My Subjects</h3>
            <p class="text-3xl mt-2">{{ $teacherSubjects ?? 0 }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold">Number of classes (Assigned me for attendance)</h3>
            <p class="text-3xl mt-2">{{ $assignedClasses ?? 0 }}</p>
        </div>
    </div>
</div>
@endsection
