@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4">

    <h1 class="text-2xl font-bold mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold">Total Students</h3>
            <p class="text-3xl mt-2">{{ $totalStudents ?? 0 }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold">Total Teachers</h3>
            <p class="text-3xl mt-2">{{ $totalTeachers ?? 0 }}</p>
        </div>

        <div class="bg-white p-6 rounded shadow">
            <h3 class="font-semibold">Subjects</h3>
            <p class="text-3xl mt-2">{{ $totalSubjects ?? 0 }}</p>
        </div>
    </div>
</div>
@endsection
