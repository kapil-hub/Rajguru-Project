@extends('layouts.app')

@section('content')
<div class="p-6">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Registration Windows</h1>
    </div>

<a href="{{ route('admin.registration-window.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
    + New Window
</a>
    <div class="overflow-x-auto bg-white shadow rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Department</th>
                    <th class="p-3 text-left">Course</th>
                    <th class="p-3">From</th>
                    <th class="p-3">To</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($windows as $win)
                    <tr class="border-t">
                        <td class="p-3">{{ $win->department->name }}</td>
                        <td class="p-3">{{ $win->course->name }}</td>
                        <td class="p-3">{{ $win->start_date }}</td>
                        <td class="p-3">{{ $win->end_date }}</td>
                        <td class="p-3 text-center">
                            @if($win->is_active)
                                <span class="text-green-600 font-semibold">OPEN</span>
                            @else
                                <span class="text-red-600 font-semibold">CLOSED</span>
                            @endif
                        </td>
                        <td class="p-3 flex gap-2 justify-center">
                            <a href="{{ route('admin.registration-window.edit',$win) }}"
                               class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
                                Edit
                            </a>

                            <form method="POST"
                                  action="{{ route('admin.registration-window.toggle',$win) }}">
                                @csrf
                                @method('PATCH')
                                <button class="px-3 py-1 bg-gray-700 text-white rounded text-sm">
                                    {{ $win->is_active ? 'Close' : 'Open' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach

                @if($windows->count() === 0)
                    <tr>
                        <td colspan="6" class="text-center p-4 text-gray-500">
                            No registration windows created yet
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
