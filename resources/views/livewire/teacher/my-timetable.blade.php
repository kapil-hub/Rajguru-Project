<div class="space-y-6">
    <!-- Header -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    My Timetable
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    View your assigned teaching classes and schedules
                </p>
            </div>
            <div>
                <span class="rounded-xl bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700">
                    Faculty Schedule
                </span>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <h3 class="text-sm font-bold text-gray-700 mb-4 uppercase tracking-wider">Filter Timetable</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3 xl:grid-cols-5">
            <!-- Course -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Course</label>
                <select wire:model.live="course_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Semester -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Semester</label>
                <select wire:model.live="semester" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    <option value="">All Semesters</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem }}">Semester {{ $sem }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Paper -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Paper</label>
                <select wire:model.live="paper_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    <option value="">All Papers</option>
                    @foreach($papers as $paper)
                        <option value="{{ $paper->id }}">{{ $paper->name }} ({{ $paper->code }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Day -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Day</label>
                <select wire:model.live="day_name" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    <option value="">All Days</option>
                    @foreach($days as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Slot Type -->
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700">Slot Type</label>
                <select wire:model.live="slot_type" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                    <option value="">All Types</option>
                    <option value="lecture">Lecture</option>
                    <option value="tutorial">Tutorial</option>
                    <option value="practical">Practical</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-semibold text-gray-800">Weekly Schedule Grid</h3>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr>
                        <th class="sticky left-0 bg-gray-50 border-b border-r px-6 py-4 text-left font-semibold text-gray-700">
                            Day
                        </th>
                        @foreach($timeSlots as $slot)
                            <th class="border-b border-r bg-gray-50 px-4 py-4 text-center text-sm font-semibold text-gray-700">
                                {{ $slot }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($days as $day)
                        <tr>
                            <td class="sticky left-0 border-r border-b bg-white px-6 py-5 font-semibold text-gray-700">
                                {{ $day }}
                            </td>
                            @foreach($timeSlots as $slot)
                                @php
                                    $slotKey = $day . '|' . $slot;
                                    $hasSlot = isset($timetableGrid[$slotKey]);
                                @endphp
                                <td class="border-r border-b p-2 min-w-[200px] h-32 align-top bg-white">
                                    @if($hasSlot)
                                        <div class="flex flex-col gap-2 h-full">
                                            @foreach($timetableGrid[$slotKey] as $occupiedSlot)
                                                <div class="flex flex-col justify-between rounded-xl border border-blue-200 bg-blue-50 p-3 text-blue-800 shadow-sm h-full">
                                                    <div>
                                                        <div class="text-xs font-bold uppercase tracking-wider text-blue-600 mb-1">
                                                            {{ $occupiedSlot->paper?->code }}
                                                        </div>
                                                        <div class="text-sm font-bold leading-snug line-clamp-1">
                                                            {{ $occupiedSlot->paper?->name }}
                                                        </div>
                                                        <div class="text-xs text-gray-600 mt-1 line-clamp-1">
                                                            {{ $occupiedSlot->course?->name }} (Sem {{ $occupiedSlot->semester }})
                                                        </div>
                                                        @if($occupiedSlot->batches)
                                                            <div class="mt-1">
                                                                <span class="px-1.5 py-0.5 rounded bg-blue-100 text-blue-800 text-[10px] font-bold">
                                                                    Batches: {{ $occupiedSlot->batches }}
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="mt-2 pt-2 border-t border-blue-100 flex flex-wrap items-center justify-between gap-1 text-xs">
                                                        <span class="font-medium text-gray-600">
                                                            📍 Room: {{ $occupiedSlot->room?->building_name }} - {{ $occupiedSlot->room?->room_number }}
                                                        </span>
                                                        <div class="flex gap-1 mt-1">
                                                            @if($occupiedSlot->is_lecture)
                                                                <span class="px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-800 text-[10px] font-bold">L</span>
                                                            @endif
                                                            @if($occupiedSlot->is_tutorial)
                                                                <span class="px-1.5 py-0.5 rounded bg-yellow-100 text-yellow-800 text-[10px] font-bold">T</span>
                                                            @endif
                                                            @if($occupiedSlot->is_practical)
                                                                <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-800 text-[10px] font-bold">P</span>
                                                            @endif
                                                            @if($occupiedSlot->is_coordinator)
                                                                <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-800 text-[10px] font-bold">C</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="flex h-24 items-center justify-center rounded-xl border border-dashed border-gray-200">
                                            <span class="text-xs text-gray-400">Free</span>
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
</div>
