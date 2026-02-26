<div>

    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
        <h2 class="text-2xl font-bold text-indigo-600">
            Practical Marks Entry
        </h2>

        <div class="mt-4">
            <label class="font-medium text-gray-700">
                Select Practical Paper
            </label>

            <select wire:model.live="selectedPaper"
                class="w-half mt-2 p-3 rounded-xl border border-gray-300
                       focus:ring-2 focus:ring-indigo-400">

                <option value="">-- Select Paper --</option>
                @if(empty($papers))
                    <option value="" disabled>You have not assigned any subject</option>
                @endif
                @foreach($papers as $paper)
                    <option value="{{ $paper->id }}">
                        {{ $paper->code }} - {{ $paper->name }}
                    </option>
                @endforeach
            </select>

            <button
                wire:click="loadStudents"
                wire:loading.attr="disabled"
                wire:target="loadStudents"
                @disabled(!$selectedPaper)
                class="mt-4 px-6 py-2 bg-indigo-600 text-white
                    rounded-xl hover:bg-indigo-700 transition shadow-md
                    disabled:opacity-50 disabled:cursor-not-allowed">

                <span wire:loading.remove wire:target="loadStudents">
                    View Students
                </span>

                <span wire:loading wire:target="loadStudents">
                    Loading...
                </span>
            </button>
            @if(session()->has('success'))
                <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session()->has('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif
        </div>
    </div>

    @if($showStudents)

<div class="bg-white p-6 rounded-2xl shadow-md mb-6 border-l-8 border-indigo-600">

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border rounded-lg">

            <thead class="bg-indigo-100 text-gray-700 uppercase text-xs">
                <tr>
                    <th class="px-4 py-3">S No</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Program / Sem</th>
                    <th class="px-4 py-3">Roll No</th>
                    <th class="px-4 py-3">College Roll No</th>
                    <th class="px-4 py-3 text-center">
                        CA <br>(max: <b>{{$practicleBreakup['ca']}}</b>)
                    </th>
                    <th class="px-4 py-3 text-center">
                        ESP <br>(max: <b>{{$practicleBreakup['written_exam']}}</b>)
                    </th>
                    <th class="px-4 py-3 text-center">
                        Viva <br>(max: <b>{{$practicleBreakup['viva_voce']}}</b>)
                    </th>
                    <th class="px-4 py-3 text-center">
                        Total <br>(max: <b>{{array_sum($practicleBreakup)}}</b>)
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y">

                @forelse($students as $index => $student)
                   <tr
                        wire:key="student-{{ $student->id }}"
                        x-data="{
                            ca: {{ $marks[$student->id]['ca'] ?? 'null' }},
                            esp: {{ $marks[$student->id]['esp'] ?? 'null' }},
                            viva: {{ $marks[$student->id]['viva'] ?? 'null' }},
                            get total() {
                                return (Number(this.ca) || 0)
                                    + (Number(this.esp) || 0)
                                    + (Number(this.viva) || 0);
                            }
                        }"
                        class="hover:bg-gray-50 transition"
                    >

                    <td class="px-4 py-3">
                        {{ $index + 1 }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $student->name }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $student->academic->course->program_code ?? 'N/A' }}
                        /
                        {{ $student->academic->current_semester ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $student->academic->roll_number ?? '-' }}
                    </td>

                    <td class="px-4 py-3">
                        {{ $student->academic->college_roll_number ?? '-' }}
                    </td>

                    <!-- CA -->
                    <td class="px-4 py-3 text-center">
                        <input type="number"
                            min="0"
                            max="{{ $practicleBreakup['ca'] }}"
                            wire:model.defer="marks.{{ $student->id }}.ca"
                            x-model="ca"
                            class="w-20 p-2 border rounded-lg text-center">
                    </td>

                    <!-- ESP -->
                    <td class="px-4 py-3 text-center">
                        <input type="number"
                            min="0"
                            max="{{ $practicleBreakup['written_exam'] }}"
                            wire:model.defer="marks.{{ $student->id }}.esp"
                            x-model="esp"
                            class="w-20 p-2 border rounded-lg text-center">
                    </td>

                    <!-- Viva -->
                    <td class="px-4 py-3 text-center">
                        <input type="number"
                            min="0"
                            max="{{ $practicleBreakup['viva_voce'] }}"
                            wire:model.defer="marks.{{ $student->id }}.viva"
                            x-model="viva"
                            class="w-20 p-2 border rounded-lg text-center">
                    </td>

                    <!-- Total -->
                    <td class="px-4 py-3 text-center font-semibold text-indigo-600"
                        x-text="total">
                    </td>

                </tr>

                @empty
                <tr>
                    <td colspan="9" class="text-center py-6 text-gray-500">
                        No students found.
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    <!-- Save Button -->
    <div class="mt-6 text-right">
        <button
            wire:click="saveMarks"
            wire:loading.attr="disabled"
            wire:target="saveMarks"
            class="px-8 py-3 bg-green-600 text-white rounded-xl
                   hover:bg-green-700 transition shadow-md">

            <span wire:loading.remove wire:target="saveMarks">
                Save Marks
            </span>

            <span wire:loading wire:target="saveMarks">
                Saving...
            </span>
        </button>
    </div>

</div>

@endif

</div>
