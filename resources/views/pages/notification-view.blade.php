@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-100 p-4 md:p-8">
    <div class="max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-md p-5 md:p-6 mb-6 border-l-8 border-indigo-600">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Notifications</h1>
            <p class="text-sm text-gray-500 mt-1">View and download official notifications available for your department, course, or papers.</p>
        </div>

        @if($notifications->isEmpty())
            <div class="bg-white rounded-2xl shadow-md p-8 text-center text-gray-500">
                No notifications are available for you right now.
            </div>
        @else
            <div class="space-y-5">
                @foreach($notifications as $notification)
                    <div class="bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100">
                        <div class="p-5 md:p-6 flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800">{{ $notification->title }}</h2>
                                @if($notification->description)
                                    <p class="text-sm text-gray-600 mt-2">{{ $notification->description }}</p>
                                @endif
                                <p class="text-xs text-gray-400 mt-3">Published {{ $notification->created_at->format('d M Y') }}</p>
                            </div>

                            <div class="flex gap-3 shrink-0">
                                <a href="{{ Storage::url($notification->file_path) }}" target="_blank"
                                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                                    Open
                                </a>
                                <a href="{{ route('students.notification.download', $notification) }}"
                                    class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md transition text-sm">
                                    Download
                                </a>
                            </div>
                        </div>

                        @if(Str::endsWith(strtolower($notification->file_path), '.pdf'))
                            <div class="w-full h-[65vh] bg-gray-200 border-t">
                                <iframe src="{{ Storage::url($notification->file_path) }}" class="w-full h-full"></iframe>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
