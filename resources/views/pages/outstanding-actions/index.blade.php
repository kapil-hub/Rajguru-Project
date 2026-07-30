@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 p-4">
    <div class="border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Outstanding Actions</h2>
                <p class="mt-1 text-sm text-gray-500">Review late held-class requests and track submitted requests.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth('teacher')->check())
                    <a href="{{ route('outstanding-actions.late-held.create') }}"
                       class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                        New Late Held Request
                    </a>
                    <a href="{{ route('outstanding-actions.substitute-held.create') }}"
                       class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        New Substitute Lecture
                    </a>
                @endif
                <span class="rounded bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700">
                    Pending Approval Actions: {{ $pendingRequests->count() }}
                </span>
            </div>
        </div>
        @if(session('success'))
            <div class="mt-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mt-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
        @endif
    </div>

    @if($pendingRequests->isNotEmpty())
        <div class="border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Pending Approval Requests</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3">Request</th>
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Held Date</th>
                            <th class="px-4 py-3">Reason</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $requestItem)
                            <tr id="request-{{ $requestItem->id }}" class="border-b align-top">
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-800">
                                        {{ $requestItem->request_type === 'substitute_held' ? 'Substitute Lecture' : 'Late Held' }}
                                    </div>
                                    <div class="text-xs text-gray-500">Original: {{ $requestItem->teacher?->name }}</div>
                                    @if($requestItem->substituteTeacher)
                                        <div class="text-xs text-gray-500">Taken by: {{ $requestItem->substituteTeacher?->name }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-800">
                                        {{ $requestItem->timetable?->course?->name }} Sem {{ $requestItem->timetable?->semester }}
                                    </div>
                                    <div class="text-gray-600">{{ $requestItem->timetable?->paper?->name }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $requestItem->timetable?->day_name }}
                                        {{ substr((string) $requestItem->timetable?->start_time, 0, 5) }}-{{ substr((string) $requestItem->timetable?->end_time, 0, 5) }}
                                        @if($requestItem->timetable?->room)
                                            | Room {{ $requestItem->timetable->room?->room_number }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-4">{{ $requestItem->held_date?->format('d M Y') }}</td>
                                <td class="max-w-md px-4 py-4 text-gray-700">{{ $requestItem->reason ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    <div class="grid min-w-[260px] gap-3">
                                        <form method="POST" action="{{ route('outstanding-actions.approve', $requestItem) }}" class="space-y-2">
                                            @csrf
                                            <textarea name="tic_remark" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Approval remark (optional)"></textarea>
                                            <button class="w-full rounded bg-green-600 px-3 py-2 text-xs font-semibold text-white hover:bg-green-700">
                                                Approve Request
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('outstanding-actions.reject', $requestItem) }}" class="space-y-2">
                                            @csrf
                                            <textarea name="tic_remark" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm" placeholder="Rejection reason"></textarea>
                                            <button class="w-full rounded bg-red-600 px-3 py-2 text-xs font-semibold text-white hover:bg-red-700">
                                                Reject Request
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if(auth('teacher')->check())
        <div class="border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">My Late Held Requests</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Reason</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Approval Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myRequests as $requestItem)
                            <tr id="request-{{ $requestItem->id }}" class="border-b align-top">
                                <td class="px-4 py-4">
                                    <div class="font-semibold text-gray-800">
                                        {{ $requestItem->request_type === 'substitute_held' ? 'Substitute Lecture' : 'Late Held' }}
                                    </div>
                                    <div class="text-gray-700">{{ $requestItem->timetable?->course?->name }} | {{ $requestItem->timetable?->paper?->name }}</div>
                                    @if($requestItem->substituteTeacher)
                                        <div class="text-xs text-gray-500">Original: {{ $requestItem->teacher?->name }} | Taken by: {{ $requestItem->substituteTeacher?->name }}</div>
                                    @endif
                                    <div class="text-xs text-gray-500">
                                        {{ $requestItem->timetable?->day_name }}
                                        {{ substr((string) $requestItem->timetable?->start_time, 0, 5) }}-{{ substr((string) $requestItem->timetable?->end_time, 0, 5) }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">{{ $requestItem->held_date?->format('d M Y') }}</td>
                                <td class="max-w-md px-4 py-4 text-gray-700">{{ $requestItem->reason ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    <span class="rounded px-2 py-1 text-xs font-semibold {{ $requestItem->status === 'approved' ? 'bg-green-50 text-green-700' : ($requestItem->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700') }}">
                                        {{ ucfirst($requestItem->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">{{ $requestItem->tic_remark ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-gray-500">No requests submitted.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
