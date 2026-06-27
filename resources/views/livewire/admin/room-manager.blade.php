<div class="max-w-7xl mx-auto space-y-6">

    {{-- ══════════════════════════════════════════════════════
         HEADER
    ══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl shadow-xl p-6 border-l-8 border-indigo-600">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Room Management</h2>
                <p class="text-gray-500 mt-1">Add, edit and manage classrooms &amp; labs</p>
            </div>
            <button wire:click="openModal"
                class="inline-flex items-center gap-2 px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-2xl shadow-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Room
            </button>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         FLASH MESSAGES
    ══════════════════════════════════════════════════════ --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-2xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-700 shadow-sm">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414L8.414 15l-4.121-4.121a1 1 0 011.414-1.414L8.414 12.172l7.879-7.879a1 1 0 011.414 0z" clip-rule="evenodd"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-medium text-red-700 shadow-sm">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         STATS CARDS
    ══════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total Rooms</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['classrooms'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Classrooms</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ $stats['labs'] }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Labs</p>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-800">{{ number_format($stats['capacity']) }}</p>
                <p class="text-xs text-gray-500 mt-0.5">Total Capacity</p>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         SEARCH & FILTER BAR
    ══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <div class="flex flex-col sm:flex-row gap-3">
            {{-- Search --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by building, floor or room number…"
                    class="w-full pl-9 pr-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition"
                >
            </div>

            {{-- Type filter --}}
            <select wire:model.live="filterType"
                class="px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition bg-white">
                <option value="">All Types</option>
                <option value="classroom">Classrooms</option>
                <option value="lab">Labs</option>
            </select>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════
         ROOMS TABLE
    ══════════════════════════════════════════════════════ --}}
    <div class="bg-white rounded-3xl shadow-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold text-gray-600">#</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-600">Building</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-600">Floor</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-600">Room No.</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-600">Type</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-600">Capacity</th>
                        <th class="px-6 py-4 text-left font-semibold text-gray-600">Remarks</th>
                        <th class="px-6 py-4 text-center font-semibold text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rooms as $room)
                        <tr class="hover:bg-indigo-50/30 transition">
                            <td class="px-6 py-4 text-gray-400 font-medium">
                                {{ $rooms->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-800">
                                {{ $room->building_name }}
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $room->floor_no }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 font-mono font-medium">
                                {{ $room->room_number }}
                            </td>
                            <td class="px-6 py-4">
                                @if($room->is_lab)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        Lab
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                        Classroom
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-600">
                                {{ $room->capacity ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-gray-500 max-w-xs truncate">
                                {{ $room->remarks ?: '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-2">
                                    <button wire:click="edit({{ $room->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 font-medium text-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Edit
                                    </button>
                                    <button wire:click="confirmDelete({{ $room->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 font-medium text-xs transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center text-gray-400">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                    <p class="font-medium">No rooms found</p>
                                    <p class="text-sm">Try adjusting the search or filter, or add a new room.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($rooms->hasPages())
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $rooms->links() }}
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════
         ADD / EDIT MODAL
    ══════════════════════════════════════════════════════ --}}
    @if($showModal)
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeModal"></div>

        {{-- Dialog --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg" wire:click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">
                            {{ $editId ? 'Edit Room' : 'Add New Room' }}
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">
                            {{ $editId ? 'Update room details below.' : 'Fill in the details for the new room.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal"
                        class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-red-50 text-gray-400 hover:text-red-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Body --}}
                <div class="p-6 space-y-4">

                    {{-- Building Name --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">
                            Building Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="building_name" placeholder="e.g. Main Block"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition">
                        @error('building_name')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Floor No & Room No side-by-side --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Floor No. <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="floor_no" placeholder="e.g. Ground / 1 / 2"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition">
                            @error('floor_no')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Room Number <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="room_number" placeholder="e.g. 101"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition">
                            @error('room_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Type & Capacity side-by-side --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Room Type</label>
                            <div class="flex items-center gap-3 mt-3">
                                <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                                    <div class="relative">
                                        <input type="checkbox" wire:model="is_lab" class="sr-only peer">
                                        <div class="w-10 h-5 rounded-full bg-gray-300 peer-checked:bg-indigo-600 transition"></div>
                                        <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition peer-checked:translate-x-5"></div>
                                    </div>
                                    <span class="text-sm text-gray-700">Is a Lab</span>
                                </label>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Capacity
                            </label>
                            <input type="number" wire:model="capacity" placeholder="e.g. 60" min="1"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition">
                            @error('capacity')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Remarks --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                        <textarea wire:model="remarks" rows="3" placeholder="Any additional notes…"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 text-sm focus:ring-2 focus:ring-indigo-300 focus:border-indigo-500 outline-none transition resize-none"></textarea>
                        @error('remarks')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Footer actions --}}
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModal"
                            class="w-1/2 py-3 rounded-2xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="save" wire:loading.attr="disabled"
                            class="w-1/2 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-lg transition disabled:opacity-60">
                            <span wire:loading.remove wire:target="save">
                                {{ $editId ? 'Update Room' : 'Save Room' }}
                            </span>
                            <span wire:loading wire:target="save" class="inline-flex items-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                                </svg>
                                Saving…
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ══════════════════════════════════════════════════════
         DELETE CONFIRMATION MODAL
    ══════════════════════════════════════════════════════ --}}
    @if($showDelete)
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="cancelDelete"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm p-6 text-center" wire:click.stop>

                <div class="w-16 h-16 mx-auto rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                    </svg>
                </div>

                <h3 class="text-xl font-bold text-gray-800 mb-2">Delete Room?</h3>
                <p class="text-gray-500 text-sm mb-6">
                    This action cannot be undone. Rooms assigned to active timetable entries cannot be deleted.
                </p>

                <div class="flex gap-3">
                    <button wire:click="cancelDelete"
                        class="flex-1 py-3 rounded-2xl border border-gray-300 text-gray-700 hover:bg-gray-50 font-semibold transition">
                        Cancel
                    </button>
                    <button wire:click="delete" wire:loading.attr="disabled"
                        class="flex-1 py-3 rounded-2xl bg-red-600 hover:bg-red-700 text-white font-semibold shadow-lg transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="delete">Yes, Delete</span>
                        <span wire:loading wire:target="delete">Deleting…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
