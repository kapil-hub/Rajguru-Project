@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 p-4">
    <div class="border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Request Late Held Mark</h2>
                <p class="mt-1 text-sm text-gray-500">Use this when you missed marking a class as held on its scheduled date.</p>
            </div>
            <a href="{{ route('outstanding-actions.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Back to Requests
            </a>
        </div>
    </div>

    <div class="border border-gray-200 bg-white p-6 shadow-sm">
        @if(session('error'))
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                Please correct the highlighted fields and submit again.
            </div>
        @endif

        <form method="POST" action="{{ route('outstanding-actions.late-held.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Timetable Slot</label>
                <select name="paper_timetable_id" required class="w-full rounded-lg border border-gray-300 px-3 py-3">
                    <option value="">Select the missed class slot</option>
                    @foreach($teacherSlots as $slot)
                        <option value="{{ $slot->id }}" @selected(old('paper_timetable_id') == $slot->id)>
                            {{ $slot->day_name }} {{ substr($slot->start_time, 0, 5) }}-{{ substr($slot->end_time, 0, 5) }}
                            | {{ $slot->course?->name }} Sem {{ $slot->semester }}
                            | {{ $slot->paper?->name }}
                            @if($slot->room)
                                | Room {{ $slot->room?->room_number }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('paper_timetable_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Class Held Date</label>
                <input type="date" name="held_date" value="{{ old('held_date') }}" required class="w-full rounded-lg border border-gray-300 px-3 py-3">
                @error('held_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Reason for Late Request</label>
                <textarea name="reason" rows="6" required class="w-full rounded-lg border border-gray-300 px-3 py-3" placeholder="Explain why this class could not be marked held on the same day.">{{ old('reason') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Minimum 10 characters. This reason will be visible to TIC.</p>
                @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('outstanding-actions.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Submit to TIC
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
