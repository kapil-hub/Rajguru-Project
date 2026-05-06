<div class="p-4 max-w-7xl mx-auto">

    <!-- TomSelect -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>

    <h2 class="text-xl font-bold mb-4">Student Logs</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 p-2 mb-3 rounded">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="bg-red-100 p-2 mb-3 rounded">
            {{ session('error') }}
        </div>
    @endif
    <!-- ✅ GLOBAL LOADER -->
    <div wire:loading.flex wire:target.except="student_user_id"
        class="fixed inset-0 bg-black bg-opacity-40 z-50 items-center justify-center">

        <div class="bg-white px-6 py-3 rounded shadow text-lg font-semibold flex items-center gap-2">
            <svg class="animate-spin h-5 w-5 text-gray-600" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" />
            </svg>
            Processing...
        </div>
    </div>

    <!-- FORM -->
    <div class="bg-white shadow rounded p-4 mb-6">

        <div class="grid md:grid-cols-3 gap-4">

            <!-- Student -->
            <div wire:ignore>
                <select id="studentSelect" class="border p-2 rounded w-full">
                    <option value="">Select Student</option>

                    @foreach($students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->name }} - {{ $student->academic->roll_number ?? '' }}
                        </option>
                    @endforeach
                </select>
                @error('student_user_id')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror
            </div>

            <!-- ✅ Loader when student changes -->


            <!-- Paper -->
            <div class="w-full">

                <!-- 🔄 Loading state -->
                <select wire:loading wire:target="student_user_id" class="border p-2 rounded w-full text-gray-500"
                    disabled>
                    <option>Loading papers...</option>
                </select>

                <!-- ✅ Normal state -->
                <select wire:model="paper_master_id" wire:loading.remove wire:target="student_user_id"
                    class="border p-2 rounded w-full">

                    <option value="">Select Paper</option>

                    @foreach($papers as $sp)
                        <option value="{{ $sp->paper_master_id }}">
                            {{ $sp->paper->name ?? 'Paper-' . $sp->paper_master_id }}
                        </option>
                    @endforeach

                </select>
                @error('paper_master_id')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror

            </div>

            <!-- Log Count -->
            <input type="number" wire:model="log_count" placeholder="Log Count" class="border p-2 rounded w-full">
            @error('log_count')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
            <!-- Remark -->
            <input type="text" wire:model="remark" placeholder="Remark" class="border p-2 rounded w-full md:col-span-3">

            @error('remark')
                <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
            @enderror
        </div>

        <div class="mt-4 flex gap-2">
            @if($isEdit)

                <!-- UPDATE BUTTON -->
                <button wire:click="update" wire:loading.attr="disabled" class="bg-blue-500 text-white px-4 py-2 rounded">

                    <span wire:loading.remove wire:target="update">Update</span>
                    <span wire:loading wire:target="update">Updating...</span>

                </button>

                <button wire:click="resetForm" class="bg-gray-400 text-white px-4 py-2 rounded">
                    Cancel
                </button>

            @else

                <!-- SAVE BUTTON -->
                <button wire:click="store" wire:loading.attr="disabled" class="bg-green-500 text-white px-4 py-2 rounded">

                    <span wire:loading.remove wire:target="store">Save</span>
                    <span wire:loading wire:target="store">Saving...</span>

                </button>

            @endif
        </div>
    </div>

    <!-- TABLE -->
    <div class="bg-white shadow rounded overflow-x-auto">

        <table class="w-full text-sm text-center">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-2">ID</th>
                    <th>Student</th>
                    <th>Paper</th>
                    <th>Count</th>
                    <th>Remark</th>
                    <th>Action</th>
                </tr>
            </thead>

            <!-- Optional fade while loading -->
            <tbody wire:loading.class="opacity-50">
                @foreach($logs as $log)
                    <tr class="border-b">
                        <td class="p-2">{{ $log->id }}</td>
                        <td>{{ $log->student->name ?? '' }}</td>
                        <td>{{ $log->paper->name ?? '' }}</td>
                        <td>{{ $log->log_count }}</td>
                        <td>{{ $log->remark ?? 'N/A' }}</td>
                        <td>
                            <button wire:click="edit({{ $log->id }})" class="text-blue-600">
                                Edit
                            </button>

                            <button wire:click="delete({{ $log->id }})" wire:loading.attr="disabled"
                                class="text-red-600 ml-2">
                                Delete
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>
    <script>
        document.addEventListener('livewire:load', function () {

            let select = new TomSelect("#studentSelect", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                }
            });

            // Update Livewire when changed
            select.on('change', function (value) {
                @this.set('student_user_id', value);
            });
            Livewire.on('setStudentSelect', (data) => {
                select.setValue(data.studentId);
            });
            // Listen for reset (optional)
            Livewire.on('resetStudentSelect', () => {
                select.clear();
            });

        });
        document.addEventListener('DOMContentLoaded', initTomSelect);

        function initTomSelect() {

            let el = document.getElementById('studentSelect');

            // جلوگیری duplicate init
            if (!el || el.tomselect) return;

            let select = new TomSelect(el, {
                create: false,
                maxOptions: 5000,
                searchField: ['text'], // enables search
            });

            select.on('change', function (value) {
                @this.set('student_user_id', value);
            });

        }
    </script>
</div>