@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Notification Management</h1>
            <p class="text-sm text-gray-500">Upload notices and target active students by department, course, paper, or everyone.</p>
        </div>
        <a href="{{ route('admin.notifications.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow text-sm">
            + Add Notification
        </a>
    </div>

    <div class="overflow-x-auto bg-white rounded-2xl shadow-md p-6 border-l-8 border-indigo-600">
        <table class="min-w-full border-collapse">
            <thead class="bg-gray-100 text-gray-600 uppercase text-sm">
                <tr>
                    <th class="p-3 text-left">Title</th>
                    <th class="p-3 text-left">Target</th>
                    <th class="p-3 text-center">Status</th>
                    <th class="p-3 text-center">Date</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-3">
                            <div class="font-semibold text-gray-800">{{ $notification->title }}</div>
                            <div class="text-xs text-gray-500 truncate max-w-md">{{ $notification->description }}</div>
                        </td>
                        <td class="p-3 text-sm text-gray-600">
                            @if($notification->target_all_active_students)
                                All active students
                            @else
                                Departments: {{ count($notification->department_ids ?? []) }},
                                Courses: {{ count($notification->course_ids ?? []) }},
                                Papers: {{ count($notification->paper_ids ?? []) }}
                            @endif
                        </td>
                        <td class="p-3 text-center">
                            <span class="{{ $notification->is_active ? 'text-green-600' : 'text-red-600' }} font-semibold">
                                {{ $notification->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-3 text-center text-sm text-gray-600">{{ $notification->created_at->format('d M Y') }}</td>
                        <td class="p-3">
                            <div class="flex justify-center gap-2">
                                <a href="{{ Storage::url($notification->file_path) }}" target="_blank"
                                   class="px-3 py-1 bg-gray-600 text-white rounded text-sm">Open</a>
                                <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}" onsubmit="return confirm('Delete this notification?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1 bg-red-600 text-white rounded text-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">No notifications created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    </div>
</div>
@endsection
