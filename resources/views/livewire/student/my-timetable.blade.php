<div class="space-y-6">
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">My Timetable</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ $student?->name ?? 'Student' }}
                    @if($academic)
                        <span class="mx-1">•</span>
                        {{ $academic->course?->name ?? 'Course' }}
                        <span class="mx-1">•</span>
                        Semester {{ $academic->current_semester }}
                        <span class="mx-1">•</span>
                        Section {{ strtoupper(trim((string) ($academic->section ?? 'A'))) ?: 'A' }}
                    @endif
                </p>
            </div>
            <div>
                <span class="rounded-xl bg-blue-50 px-4 py-2 text-sm font-medium text-blue-700">
                    Student Schedule
                </span>
            </div>
        </div>
    </div>

    @if(!$academic)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm font-semibold text-amber-800">
            Your academic details are not available yet.
        </div>
    @else
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wider text-gray-700">Filter Timetable</h3>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Paper</label>
                    <select wire:model.live="paper_id" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">All Papers</option>
                        @foreach($papers as $paper)
                            <option value="{{ $paper->id }}">{{ $paper->name }} ({{ $paper->code }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Day</label>
                    <select wire:model.live="day_name" class="w-full rounded-xl border border-gray-300 px-4 py-3">
                        <option value="">All Days</option>
                        @foreach($days as $day)
                            <option value="{{ $day }}">{{ $day }}</option>
                        @endforeach
                    </select>
                </div>

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

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-800">Weekly Schedule</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="sticky left-0 z-10 border-b border-r bg-gray-50 px-6 py-4 text-left font-semibold text-gray-700">
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
                                <td class="sticky left-0 z-10 border-b border-r bg-white px-6 py-5 font-semibold text-gray-700">
                                    {{ $day }}
                                </td>
                                @foreach($timeSlots as $slot)
                                    @php
                                        $slotKey = $day . '|' . $slot;
                                        $hasSlot = isset($timetableGrid[$slotKey]);
                                    @endphp
                                    <td class="h-32 min-w-[220px] border-b border-r bg-white p-2 align-top">
                                        @if($hasSlot)
                                            <div class="flex h-full flex-col gap-2">
                                                @foreach($timetableGrid[$slotKey] as $occupiedSlot)
                                                    <div class="flex h-full flex-col justify-between rounded-xl border border-blue-200 bg-blue-50 p-3 text-blue-800 shadow-sm">
                                                        <div>
                                                            <div class="mb-1 text-xs font-bold uppercase tracking-wider text-blue-600">
                                                                {{ $occupiedSlot->paper?->code }}
                                                            </div>
                                                            <div class="line-clamp-2 text-sm font-bold leading-snug">
                                                                {{ $occupiedSlot->paper?->name }}
                                                            </div>
                                                            <div class="mt-1 text-xs text-gray-600">
                                                                {{ $occupiedSlot->teacher?->name ?? 'Faculty' }}
                                                            </div>
                                                            @if($occupiedSlot->batches)
                                                                <div class="mt-1">
                                                                    <span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-bold text-blue-800">
                                                                        Batches: {{ $occupiedSlot->batches }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="mt-2 border-t border-blue-100 pt-2 text-xs">
                                                            <div class="font-medium text-gray-600">
                                                                Room: {{ $occupiedSlot->room?->building_name }} - {{ $occupiedSlot->room?->room_number }}
                                                            </div>
                                                            <div class="mt-2 flex flex-wrap gap-1">
                                                                @if($occupiedSlot->is_lecture)
                                                                    <span class="rounded bg-indigo-100 px-1.5 py-0.5 text-[10px] font-bold text-indigo-800">L</span>
                                                                @endif
                                                                @if($occupiedSlot->is_tutorial)
                                                                    <span class="rounded bg-yellow-100 px-1.5 py-0.5 text-[10px] font-bold text-yellow-800">T</span>
                                                                @endif
                                                                @if($occupiedSlot->is_practical)
                                                                    <span class="rounded bg-green-100 px-1.5 py-0.5 text-[10px] font-bold text-green-800">P</span>
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
    @endif
</div>
