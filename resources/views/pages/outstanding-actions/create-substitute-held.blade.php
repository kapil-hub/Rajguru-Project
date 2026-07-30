@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6 p-4">
    <div class="border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Request Substitute Lecture</h2>
                <p class="mt-1 text-sm text-gray-500">Use this when you took another teacher's scheduled class for a day.</p>
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

        @php
            $oldSlot = $teacherSlots->firstWhere('id', (int) old('paper_timetable_id'));
            $selectedTeacherId = old('original_teacher_id', $oldSlot?->teacher_id);
        @endphp

        <form method="POST" action="{{ route('outstanding-actions.substitute-held.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Assigned Teacher</label>
                <select name="original_teacher_id" id="originalTeacherSelect" required class="w-full rounded-lg border border-gray-300 px-3 py-3">
                    <option value="">Select teacher</option>
                    @foreach($teachers as $slotTeacher)
                        <option value="{{ $slotTeacher->id }}" @selected((string) $selectedTeacherId === (string) $slotTeacher->id)>
                            {{ $slotTeacher->name }}
                        </option>
                    @endforeach
                </select>
                @error('original_teacher_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold text-gray-700">Timetable Slot</label>
                <select name="paper_timetable_id" id="coveredSlotSelect" required class="w-full rounded-lg border border-gray-300 px-3 py-3" disabled>
                    <option value="">First select assigned teacher</option>
                    @foreach($teacherSlots as $slot)
                        <option value="{{ $slot->id }}" data-teacher-id="{{ $slot->teacher_id }}" @selected(old('paper_timetable_id') == $slot->id)>
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
                <label class="mb-2 block text-sm font-semibold text-gray-700">Reason / Confirmation Note</label>
                <textarea name="reason" rows="6" required class="w-full rounded-lg border border-gray-300 px-3 py-3" placeholder="Example: I took this lecture on behalf of the assigned teacher.">{{ old('reason') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Minimum 10 characters. This note will be visible to approvers.</p>
                @error('reason') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="{{ route('outstanding-actions.index') }}" class="rounded-lg border border-gray-300 px-5 py-2.5 text-center text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Submit for Approval
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const teacherSelect = document.getElementById('originalTeacherSelect');
        const slotSelect = document.getElementById('coveredSlotSelect');
        const options = Array.from(slotSelect.options);

        function filterSlots() {
            const teacherId = teacherSelect.value;
            let visibleCount = 0;

            options.forEach((option, index) => {
                if (index === 0) {
                    option.hidden = false;
                    option.textContent = teacherId ? 'Select the class you covered' : 'First select assigned teacher';
                    return;
                }

                const isVisible = option.dataset.teacherId === teacherId;
                option.hidden = !isVisible;
                option.disabled = !isVisible;
                visibleCount += isVisible ? 1 : 0;
            });

            slotSelect.disabled = !teacherId || visibleCount === 0;

            if (!teacherId || slotSelect.selectedOptions[0]?.hidden) {
                slotSelect.value = '';
            }
        }

        teacherSelect.addEventListener('change', filterSlots);
        filterSlots();
    });
</script>
@endsection
