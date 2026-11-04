<div>
    <div class="bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">
        <h2 class="text-2xl font-bold text-indigo-600">Theory Marks Entry</h2>

        <div class="mt-4">
            <label for="theory-paper" class="font-medium text-gray-700">Select Paper</label>
            <select id="theory-paper" wire:model.live="selectedPaper"
                class="block w-full max-w-xl mt-2 p-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-indigo-400">
                <option value="">-- Select Paper --</option>
                @forelse($papers as $paper)
                    <option value="{{ $paper->id }}">{{ $paper->code }} - {{ $paper->name }}</option>
                @empty
                    <option value="" disabled>No eligible coordinator papers assigned</option>
                @endforelse
            </select>

            <button wire:click="loadStudents" wire:loading.attr="disabled" @disabled(!$selectedPaper)
                class="mt-4 px-6 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition shadow-md disabled:opacity-50">
                View Students
            </button>
            <button wire:click="downloadBlankPdf" wire:loading.attr="disabled" wire:target="downloadBlankPdf"
                @disabled(!$selectedPaper)
                class="mt-4 ml-2 px-6 py-2 bg-gray-700 text-white rounded-xl hover:bg-gray-800 transition shadow-md disabled:opacity-50">
                Download Blank PDF
            </button>
        </div>

        @if(session()->has('success'))
            <div class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg">
                Please enter theory marks from 0 to {{ $maxMarks }}.
            </div>
        @endif
    </div>

    @if($showStudents)
        <form wire:submit="saveMarks" class="bg-white p-6 rounded-2xl shadow-md mb-6 border-l-8 border-indigo-600">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left border rounded-lg">
                    <thead class="bg-indigo-100 text-gray-700 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3">S No</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Program / Sem</th>
                            <th class="px-4 py-3">Roll No</th>
                            <th class="px-4 py-3">College Roll No</th>
                            <th class="px-4 py-3 text-center">Theory Marks (max: {{ $maxMarks }})</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($students as $index => $student)
                            <tr wire:key="theory-student-{{ $student->id }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">{{ $student->name }}</td>
                                <td class="px-4 py-3">
                                    {{ $student->academic?->course?->program_code ?? 'N/A' }}
                                    / {{ $student->academic?->current_semester ?? '-' }}
                                </td>
                                <td class="px-4 py-3">{{ $student->academic?->roll_number ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $student->academic?->college_roll_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-center">
                                    <input type="number" min="0" max="{{ $maxMarks }}" step="0.01"
                                        wire:model="marks.{{ $student->id }}"
                                        class="w-24 p-2 border rounded-lg text-center">
                                    @error("marks.{$student->id}")
                                        <span class="block text-red-600 text-xs">{{ $message }}</span>
                                    @enderror
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    No students are registered for this paper.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="saveMarks"
                class="mt-5 px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition disabled:opacity-50">
                Save Theory Marks
            </button>
        </form>
    @endif
</div>
