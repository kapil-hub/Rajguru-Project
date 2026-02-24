@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto  bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-semibold text-gray-700">Faculty Management</h2>
        <a href="{{ route('admin.faculty.create') }}" 
           class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            Add Faculty
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-2 bg-green-200 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 text-left">#</th>
                    <th class="px-4 py-2 text-left">Name</th>
                    <th class="px-4 py-2 text-left">Designation</th>
                    <th class="px-4 py-2 text-left">Department</th>
                    <th class="px-4 py-2 text-left">Courses/Papers</th>
                    <th class="px-4 py-2 text-left">Email</th>
                    <th class="px-4 py-2 text-left">Status</th>
                    <th class="px-4 py-2 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($facultyUsers as $index => $f)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2 font-medium">{{ $f->name }}</td>
                    <td class="px-4 py-2">{{ $f->designation }}</td>
                    <td class="px-4 py-2">{{ $f->department->name ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm">
                        @foreach($f->details as $d)
                            <div>{{ $d->course->name ?? '-' }} - {{ $d->paperMaster->name ?? '-' }}</div>
                        @endforeach
                    </td>
                    <td class="px-4 py-2">{{ $f->email }}</td>
                    <td class="px-4 py-2">
                        @if($f->status) 
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">Active</span>
                        @else 
                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-center space-x-2">
                        <a href="{{ route('admin.faculty.edit', $f->id) }}" 
                           class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 text-sm">Edit</a>
                        <form action="{{ route('admin.faculty.delete', $f->id) }}" method="POST" class="inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 text-sm"
                                    onclick="return confirm('Are you sure?')">
                                Trash
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @if($facultyUsers->isEmpty())
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                            No faculty found.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

</div>
@endsection
