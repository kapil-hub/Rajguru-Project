<div class="space-y-6">

    <div
        wire:loading.flex
        wire:target="loadCalendar,save,openTimetableCalendar,editSlot"
        class="pointer-events-none fixed inset-0 z-50 items-center justify-center bg-white/70 backdrop-blur-sm">

        <div class="flex flex-col items-center gap-3 rounded-2xl border border-blue-200 bg-white px-6 py-5 shadow-lg">

            <svg class="h-8 w-8 animate-spin text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>

            <div class="text-center">
                <p class="text-sm font-semibold text-gray-800">Please wait</p>
                <p class="text-xs text-gray-500">Loading timetable data...</p>
            </div>

        </div>

    </div>

    <!-- Header -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="flex items-center justify-between">

            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Timetable Management
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Create and manage class schedules
                </p>
            </div>

            <div>
                <span
                    class="rounded-xl bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700">
                    Academic Timetable
                </span>
            </div>

        </div>

    </div>

    <div class="rounded-2xl border border-gray-200 bg-white p-2 shadow-sm">

        <div class="grid grid-cols-2 gap-2">

            <button
                wire:click="showCreateSection"
                wire:target="showCreateSection"
                class="rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeSection === 'create' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">

                <span wire:loading.remove wire:target="showCreateSection">
                    Create Timetable
                </span>

                <span wire:loading wire:target="showCreateSection" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Loading
                </span>

            </button>

            <button
                wire:click="showCreatedSection"
                wire:target="showCreatedSection"
                class="rounded-xl px-4 py-3 text-sm font-semibold transition {{ $activeSection === 'created' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">

                <span wire:loading.remove wire:target="showCreatedSection">
                    Created Timetables
                </span>

                <span wire:loading wire:target="showCreatedSection" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Loading
                </span>

            </button>

        </div>

    </div>

    @if (session('success'))

        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">

            {{ session('success') }}

        </div>

    @endif

    @if($activeSection === 'created')

    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="mb-4 flex items-center justify-between gap-4">

            <div>

                <h3 class="text-lg font-semibold text-gray-800">
                    Created Timetables
                </h3>

                <p class="text-sm text-gray-500">
                    View or edit any saved timetable entry.
                </p>

            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-gray-200 text-sm">

                <thead class="bg-gray-50 text-gray-700">

                <tr>

                    <th class="px-4 py-3 text-left font-semibold">Paper</th>
                    <th class="px-4 py-3 text-left font-semibold">Department</th>
                    <th class="px-4 py-3 text-left font-semibold">Course</th>
                    <th class="px-4 py-3 text-left font-semibold">Semester</th>
                    <th class="px-4 py-3 text-left font-semibold">Day</th>
                    <th class="px-4 py-3 text-left font-semibold">Time</th>
                    <th class="px-4 py-3 text-left font-semibold">Room</th>
                    <th class="px-4 py-3 text-left font-semibold">Teacher</th>
                    <th class="px-4 py-3 text-right font-semibold">Actions</th>

                </tr>

                </thead>

                <tbody class="divide-y divide-gray-200 bg-white">

                @forelse($timetables as $timetable)

                    <tr>

                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $timetable->paper?->name ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ $timetable->department?->name ?? $timetable->department_id }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ $timetable->course?->name ?? $timetable->course_id }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ $timetable->semester }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ $timetable->day_name }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ substr($timetable->start_time, 0, 5) }} - {{ substr($timetable->end_time, 0, 5) }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ $timetable->room?->building_name }} - {{ $timetable->room?->floor_no }} - {{ $timetable->room?->room_number }}
                        </td>

                        <td class="px-4 py-3 text-gray-600">
                            {{ $timetable->teacher?->name ?? 'N/A' }}
                        </td>

                        <td class="px-4 py-3 text-right">

                            <div class="inline-flex gap-2">

                                <button
                                    wire:click="openTimetableCalendar({{ $timetable->id }}, 'view')"
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-100">

                                    View

                                </button>

                                <button
                                    wire:click="openTimetableCalendar({{ $timetable->id }}, 'edit')"
                                    class="rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700 hover:bg-amber-100">

                                    Edit

                                </button>

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">
                            No timetable entries found.
                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>

    @endif

    @if($activeSection === 'create')

    <!-- Filters -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-5">

            <!-- Department -->
            <div>

                <label
                    class="mb-2 flex items-center justify-between text-sm font-medium text-gray-700">

                    <span>
                        Department
                    </span>

                    <span wire:loading wire:target="department_id,course_id,semester,paper_id" class="inline-flex items-center gap-1 text-xs text-blue-600">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Loading
                    </span>

                </label>

                <select
                    wire:model.live="department_id"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">

                    <option value="">
                        Select Department
                    </option>

                    @foreach($departments as $department)

                        <option value="{{ $department->id }}">
                            {{ $department->name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Course -->
            <div>

                <label
                    class="mb-2 block text-sm font-medium text-gray-700">

                    Course

                </label>

                <select
                    wire:model.live="course_id"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">
                        Select Course
                    </option>

                    @foreach($courses as $course)

                        <option value="{{ $course->id }}">
                            {{ $course->name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Semester -->
            <div>

                <label
                    class="mb-2 block text-sm font-medium text-gray-700">

                    Semester

                </label>

                <select
                    wire:model.live="semester"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">
                        Select Semester
                    </option>

                    @foreach($semesters as $semester)

                        <option value="{{ $semester }}">
                            Semester {{ $semester }}
                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Paper -->
            <div>

                <label
                    class="mb-2 flex items-center justify-between text-sm font-medium text-gray-700">

                    <span>
                        Paper
                    </span>

                    <span wire:loading wire:target="department_id,course_id,semester,paper_id" class="inline-flex items-center gap-1 text-xs text-blue-600">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Loading
                    </span>

                </label>

                <select
                    wire:model="paper_id"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">
                        Select Paper
                    </option>

                    @foreach($papers as $paper)

                        <option value="{{ $paper->id }}">
                            {{ $paper->name }}
                        </option>

                    @endforeach

                </select>

            </div>

            <!-- Button -->
            <div class="flex items-end">

                <button
                    wire:click="loadCalendar"
                    class="w-full rounded-xl bg-blue-600 px-4 py-3 font-medium text-white transition hover:bg-blue-700">

                    <span wire:loading.remove wire:target="loadCalendar">
                        Load Timetable
                    </span>

                    <span wire:loading wire:target="loadCalendar" class="inline-flex items-center gap-2">
                        <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        Loading
                    </span>

                </button>

            </div>

        </div>

    </div>

    @if($showCalendar)

    <div
        class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

        <div
            class="border-b border-gray-200 px-6 py-4">

            <div class="flex items-center justify-between">

                <div>

                    <h3
                        class="text-lg font-semibold text-gray-800">

                        Weekly Timetable

                    </h3>

                    <p
                        class="text-sm text-gray-500">

                        @if($calendarMode === 'view')
                            View mode - slot editing is disabled
                        @elseif($calendarMode === 'edit')
                            Edit mode - click occupied slots to edit them
                        @else
                            Click any slot to assign a lecture
                        @endif

                    </p>

                </div>

            </div>

        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full border-collapse">

                <thead>

                <tr>

                    <th
                        class="sticky left-0 bg-gray-50 border-b border-r px-6 py-4 text-left font-semibold text-gray-700">

                        Day

                    </th>

                    @foreach($timeSlots as $slot)

                        <th
                            class="border-b border-r bg-gray-50 px-4 py-4 text-center text-sm font-semibold text-gray-700">

                            {{ $slot }}

                        </th>

                    @endforeach

                </tr>

                </thead>

                <tbody>

                @foreach($days as $day)

                    <tr>

                        <td
                            class="sticky left-0 border-r border-b bg-white px-6 py-5 font-semibold text-gray-700">

                            {{ $day }}

                        </td>

                        @foreach($timeSlots as $slot)

                            @php
                                $slotKey = $day . '|' . $slot;
                            @endphp

                            <td
                                @if(isset($occupiedSlots[$slotKey]))

                                    @if($calendarMode === 'edit')

                                        wire:click="editSlot({{ $occupiedSlots[$slotKey]['id'] }})"
                                        class="cursor-pointer border-r border-b p-2 bg-green-50 transition hover:bg-green-100"

                                    @else

                                        class="border-r border-b p-2 bg-green-50"

                                    @endif

                                @else

                                    @if($calendarMode !== 'view')

                                        wire:click="
                                            openSlotModal(
                                                '{{ $day }}',
                                                '{{ $slot }}'
                                            )
                                        "
                                        class="cursor-pointer border-r border-b p-2 hover:bg-blue-50 transition"

                                    @else

                                        class="border-r border-b p-2"

                                    @endif

                                @endif
                            >

                                @if(isset($occupiedSlots[$slotKey]))

                                    @php
                                        $occupiedSlot = $occupiedSlots[$slotKey];
                                    @endphp

                                    <div
                                        class="flex h-24 flex-col items-center justify-center rounded-xl border-2 border-green-400 bg-green-100 px-2 text-center text-green-700">

                                        <span class="text-sm font-semibold">
                                            Occupied
                                        </span>

                                        <span class="text-xs font-medium">
                                            Paper: {{ $occupiedSlot['paper_name'] ?? 'N/A' }}
                                        </span>

                                        <span class="text-xs">
                                            Room: {{ $occupiedSlot['room_label'] ?: 'N/A' }}
                                        </span>

                                    </div>

                                @else

                                    <div
                                        class="flex h-24 items-center justify-center rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-500">

                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            class="h-7 w-7 text-blue-500"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            stroke="currentColor">

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M12 4v16m8-8H4" />

                                        </svg>

                                    </div>

                                @endif

                            </td>

                        @endforeach

                    </tr>

                @endforeach

                </tbody>

            </table>

        </div>

    </div>

    @endif
<div
    wire:key="timetable-modal-{{ $modalKey }}"
    class="{{ $showModal ? 'fixed inset-0 z-99999 flex items-center justify-center bg-black/50' : 'hidden' }}">

    <div
        class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl">

        <div
            class="mb-4 flex items-center justify-between">

            <h3
                class="text-xl font-bold">

                        {{ $selectedTimetableId ? 'Edit Timetable Slot' : 'Add Timetable Slot' }}

            </h3>

            <button
                type="button"
                wire:click="closeModal"
                class="text-gray-500">

                ✕

            </button>

        </div>

        <div class="space-y-4">

            <div>

                <label
                    class="mb-1 block text-sm font-medium">

                    Day

                </label>

                <input
                    type="text"
                    value="{{ $day_name }}"
                    readonly
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

            </div>

            <div>

                <label
                    class="mb-1 block text-sm font-medium">

                    Start Time

                </label>

                <input
                    type="text"
                    wire:model="start_time"
                    readonly
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

            </div>

            <div>

                <label
                    class="mb-1 block text-sm font-medium">

                    End Time

                </label>

                <input
                    type="time"
                    wire:model="end_time"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

            </div>

            <div>

                <label
                    class="mb-1 block text-sm font-medium">

                    Room

                </label>

                <p class="mb-2 text-xs text-gray-500">
                    Only rooms with enough capacity and no overlapping booking for this slot are shown.
                </p>

                <select
                    wire:model="room_id"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">
                        Select Room
                    </option>

                    @forelse($rooms as $room)

                        <option value="{{ $room->id }}">
                            {{ $room->building_name }} - {{ $room->floor_no }} - {{ $room->room_number }}
                        </option>

                    @empty

                        <option value="" disabled>
                            No available rooms for this slot
                        </option>

                    @endforelse
                </select>

                @if(collect($rooms)->isEmpty())

                    <p class="mt-2 text-xs font-medium text-red-600">
                        No room is available for this time slot.
                    </p>

                @endif

            </div>
            <div>

                <label
                    class="mb-1 block text-sm font-medium">

                    Teacher

                </label>

                <select
                    wire:model="teacher_id"
                    class="w-full rounded-xl border border-gray-300 px-4 py-3">

                    <option value="">
                        Select Teacher
                    </option>
                    @foreach($faculty as $teacher)

                        <option value="{{ $teacher->id }}">
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>

            </div>
            <div class="flex flex-wrap items-center gap-6">

                <label class="inline-flex items-center gap-2 text-sm font-medium">

                    <input
                        type="checkbox"
                        wire:model="is_lecture"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                    <span>
                        Is Lecture?
                    </span>

                </label>

                <label class="inline-flex items-center gap-2 text-sm font-medium">

                    <input
                        type="checkbox"
                        wire:model="is_tutorial"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                    <span>
                        Is Tutorial?
                    </span>

                </label>

                <label class="inline-flex items-center gap-2 text-sm font-medium">

                    <input
                        type="checkbox"
                        wire:model="is_practical"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                    <span>
                        Is Practical?
                    </span>

                </label>

                <label class="inline-flex items-center gap-2 text-sm font-medium">

                    <input
                        type="checkbox"
                        wire:model="is_coordinator"
                        class="h-5 w-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500">

                    <span>
                        Is Coordinator?
                    </span>

                </label>

            </div>

        <div
            class="mt-6 flex justify-end gap-2">

            <button
                type="button"
                wire:click="closeModal"
                class="rounded-xl border px-4 py-2">

                Cancel

            </button>

            <button
                type="button"
                wire:click="save"
                class="rounded-xl bg-blue-600 px-4 py-2 text-white">

                <span wire:loading.remove wire:target="save">
                    {{ $selectedTimetableId ? 'Update' : 'Save' }}
                </span>

                <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                    <svg class="h-4 w-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    Saving
                </span>

            </button>

        </div>

    </div>

</div>

    @endif
</div>
