@extends('layouts.app')

@section('content')

    <div class="min-h-screen bg-gradient-to-br from-indigo-100 via-white to-purple-100 p-4 md:p-8">

        <!-- Header -->
        <div class="max-w-7xl mx-auto mb-6">
            <div
                class="bg-white/70 backdrop-blur-md rounded-2xl shadow-lg p-4 md:p-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center gap-2">
                        📄 Notification
                    </h1>
                    <p class="text-sm text-gray-500 mt-1">
                        View and download official notifications
                    </p>
                </div>

                <div class="flex gap-3 flex-wrap">
                    <a href="{{ Storage::url($filePath) }}" target="_blank"
                        class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm transition">
                        🔗 Open
                    </a>

                    <a href="{{ route('students.notification.download') }}"
                        class="px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md transition flex items-center gap-2">
                        ⬇ Download
                    </a>
                </div>

            </div>
        </div>

        <!-- Viewer Section -->
        <div class="max-w-7xl mx-auto">

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">

                <!-- Toolbar -->
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b">
                    <span class="text-sm text-gray-600 font-medium">
                        Preview Document
                    </span>

                    <span class="text-xs text-gray-400 hidden md:block">
                        PDF Viewer
                    </span>
                </div>

                <!-- PDF Viewer -->
                <div class="w-full h-[70vh] md:h-[80vh] bg-gray-200">

                    <iframe src="{{ Storage::url($filePath) }}" class="w-full h-full"></iframe>

                </div>

            </div>

        </div>

    </div>

@endsection