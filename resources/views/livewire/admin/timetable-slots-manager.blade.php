<div class="space-y-6">
    <!-- Header -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Timetable Slots Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Configure weekly timetable slots once before a semester starts
                </p>
            </div>
            <div>
                <span class="rounded-xl bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-700">
                    System Configuration
                </span>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Add Time Slot Card -->
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm h-fit">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Add Time Slot</h3>
            <form wire:submit.prevent="save" class="space-y-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">Start Time</label>
                    <input type="time" wire:model="start_time" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('start_time') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">End Time</label>
                    <input type="time" wire:model="end_time" class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    @error('end_time') <span class="text-xs text-red-600 font-medium">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white transition hover:bg-blue-700">
                    Add Slot
                </button>
            </form>
        </div>

        <!-- Time Slots List -->
        <div class="lg:col-span-2 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Configured Time Slots</h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Slot #</th>
                            <th class="px-6 py-4 text-left font-semibold">Start Time</th>
                            <th class="px-6 py-4 text-left font-semibold">End Time</th>
                            <th class="px-6 py-4 text-left font-semibold">Display Label</th>
                            <th class="px-6 py-4 text-right font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($timeSlots as $index => $slot)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-mono">
                                    {{ date('h:i A', strtotime($slot->start_time)) }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-mono">
                                    {{ date('h:i A', strtotime($slot->end_time)) }}
                                </td>
                                <td class="px-6 py-4 text-gray-600 font-medium">
                                    <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs text-gray-800">
                                        {{ $slot->formatted_slot }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button 
                                        type="button" 
                                        wire:click="deleteSlot({{ $slot->id }})" 
                                        onclick="return confirm('Are you sure you want to delete this slot? Timetable schedules aligned with this slot will no longer display.')" 
                                        class="rounded-lg border border-red-200 bg-red-50 p-2 text-red-600 hover:bg-red-100 transition">
                                        🗑️ Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                    No time slots configured. Add your first time slot above.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
