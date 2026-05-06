<div class="p-4 max-w-7xl mx-auto">
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
    <h2 class="text-xl font-bold mb-4">Student Logs</h2>

    @if (session()->has('message'))
        <div class="bg-green-100 p-2 mb-3 rounded">
            {{ session('message') }}
        </div>
    @endif

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

                    // Listen for reset (optional)
                    Livewire.on('resetStudentSelect', () => {
                        select.clear();
                    });

                });
                document.addEventListener('livewire:navigated', initTomSelect);
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

            <!-- Paper -->
            <select wire:model="paper_master_id" class="border p-2 rounded w-full">
                <option value="">Select Paper</option>
                @foreach($papers as $sp)
                    <option value="{{ $sp->paper_master_id }}">
                        {{ $sp->paper->name ?? 'Paper-' . $sp->paper_master_id }}
                    </option>
                @endforeach
            </select>

            <!-- Log Count -->
            <input type="number" wire:model="log_count" placeholder="Log Count" class="border p-2 rounded w-full">

            <!-- Remark -->
            <input type="text" wire:model="remark" placeholder="Remark" class="border p-2 rounded w-full md:col-span-3">

        </div>

        <div class="mt-4">
            @if($isEdit)
                <button wire:click="update" class="bg-blue-500 text-white px-4 py-2 rounded">Update</button>
                <button wire:click="resetForm" class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
            @else
                <button wire:click="store" class="bg-green-500 text-white px-4 py-2 rounded">Save</button>
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

            <tbody>
                @foreach($logs as $log)
                    <tr class="border-b">
                        <td class="p-2">{{ $log->id }}</td>
                        <td>{{ $log->student->name ?? '' }}</td>
                        <td>{{ $log->paper->name ?? '' }}</td>
                        <td>{{ $log->log_count }}</td>
                        <td>{{ $log->remark ?? 'N/A' }}</td>
                        <td>
                            <button wire:click="edit({{ $log->id }})" class="text-blue-600">Edit</button>

                            <button wire:click="delete({{ $log->id }})" class="text-red-600 ml-2">Delete</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>

</div>