@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm font-semibold text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-800">{{ session('error') }}</div>
    @endif

    <div class="border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Outstanding Actions</h2>
                <p class="text-sm text-gray-500">Late held class requests and pending approvals.</p>
            </div>
            <span class="rounded bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700">
                Pending: {{ $pendingRequests->count() }}
            </span>
        </div>
    </div>

    @if(auth('teacher')->check())
        <div class="border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Request Missed Held Mark</h3>
            <form method="POST" action="{{ route('outstanding-actions.late-held.store') }}" class="grid grid-cols-1 gap-4 lg:grid-cols-4">
                @csrf
                <div class="lg:col-span-2">
                    <label class="mb-2 block text-sm font-medium text-gray-700">Slot</label>
                    <select name="paper_timetable_id" required class="w-full rounded-lg border border-gray-300 px-3 py-2">
                        <option value="">Select slot</option>
                        @foreach($teacherSlots as $slot)
                            <option value="{{ $slot->id }}" @selected(old('paper_timetable_id') == $slot->id)>
                                {{ $slot->day_name }} {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}
                                | {{ $slot->course?->name }} Sem {{ $slot->semester }}
                                | {{ $slot->paper?->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('paper_timetable_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Date</label>
                    <input type="date" name="held_date" value="{{ old('held_date') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-2">
                    @error('held_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Reason</label>
                    <input type="text" name="reason" value="{{ old('reason') }}" class="w-full rounded-lg border border-gray-300 px-3 py-2" placeholder="Optional">
                    @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="lg:col-span-4">
                    <button type="submit" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">Submit Request</button>
                </div>
            </form>
        </div>
    @endif

    @if($pendingRequests->isNotEmpty())
        <div class="border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-semibold text-gray-800">Pending TIC Actions</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3">Teacher</th>
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Reason</th>
                            <th class="px-4 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingRequests as $requestItem)
                            <tr class="border-b">
                                <td class="px-4 py-3">{{ $requestItem->teacher?->name }}</td>
                                <td class="px-4 py-3">
                                    {{ $requestItem->timetable?->course?->name }} Sem {{ $requestItem->timetable?->semester }}
                                    | {{ $requestItem->timetable?->paper?->name }}
                                    <div class="text-xs text-gray-500">{{ $requestItem->timetable?->day_name }} {{ substr((string) $requestItem->timetable?->start_time, 0, 5) }}-{{ substr((string) $requestItem->timetable?->end_time, 0, 5) }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $requestItem->held_date?->format('d M Y') }}</td>
                                <td class="px-4 py-3">{{ $requestItem->reason ?: '-' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-2">
                                        <form method="POST" action="{{ route('outstanding-actions.approve', $requestItem) }}">
                                            @csrf
                                            <button class="rounded bg-green-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-green-700">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('outstanding-actions.reject', $requestItem) }}">
                                            @csrf
                                            <input type="hidden" name="tic_remark" value="Rejected by TIC">
                                            <button class="rounded bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Reject</button>
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
            <h3 class="mb-4 text-lg font-semibold text-gray-800">My Requests</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b bg-gray-50 text-left text-gray-600">
                            <th class="px-4 py-3">Class</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Remark</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($myRequests as $requestItem)
                            <tr class="border-b">
                                <td class="px-4 py-3">{{ $requestItem->timetable?->course?->name }} | {{ $requestItem->timetable?->paper?->name }}</td>
                                <td class="px-4 py-3">{{ $requestItem->held_date?->format('d M Y') }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded px-2 py-1 text-xs font-semibold {{ $requestItem->status === 'approved' ? 'bg-green-50 text-green-700' : ($requestItem->status === 'rejected' ? 'bg-red-50 text-red-700' : 'bg-yellow-50 text-yellow-700') }}">
                                        {{ ucfirst($requestItem->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $requestItem->tic_remark ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-gray-500">No requests submitted.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection
