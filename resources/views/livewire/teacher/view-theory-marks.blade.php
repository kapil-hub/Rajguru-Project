<div>
    <h2 class="text-3xl font-bold text-indigo-600 mb-6">Filled Theory Papers</h2>

    @if(!$showStudents)
        <div class="grid md:grid-cols-3 gap-6">
            @forelse($papers as $paper)
                <div class="bg-white p-6 rounded-2xl shadow-md mb-6 border-l-8 border-indigo-600">
                    <h3 class="text-lg font-semibold text-gray-800">{{ $paper->code }}</h3>
                    <p class="text-gray-600 mt-1">{{ $paper->name }}</p>
                    <button wire:click="viewStudents({{ $paper->id }})"
                        class="mt-4 px-5 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                        View Students
                    </button>
                </div>
            @empty
                <div class="col-span-3 text-center text-gray-500 py-10">
                    No theory marks entered yet.
                </div>
            @endforelse
        </div>
    @else
        <div class="bg-white p-6 rounded-2xl shadow-md mb-6 border-l-8 border-indigo-600">
            <div class="flex flex-wrap justify-between items-start gap-3 mb-6">
                <div>
                    <h3 class="text-2xl font-bold text-indigo-600">{{ $selectedSubject->code }}</h3>
                    <p class="text-gray-600 text-lg">{{ $selectedSubject->name }}</p>
                    <p class="mt-2 text-sm text-gray-500">
                        Maximum theory marks: {{ lectureMarksBreakup((int) $selectedSubject->number_of_lectures)['theory'] }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <button wire:click="downloadPdf" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                        Download PDF
                    </button>
                    <button wire:click="downloadExcel" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        Download Excel
                    </button>
                    <button wire:click="$set('showStudents', false)"
                        class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Back
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-indigo-100 uppercase text-xs text-gray-700">
                        <tr>
                            <th class="px-4 py-3">S No</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Program</th>
                            <th class="px-4 py-3">Semester</th>
                            <th class="px-4 py-3">Exam Roll No</th>
                            <th class="px-4 py-3">College Roll No</th>
                            <th class="px-4 py-3 text-center">Theory Marks</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($students as $index => $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">{{ $record->student?->name ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $record->student?->academic?->course?->program_code ?? 'N/A' }}</td>
                                <td class="px-4 py-3">{{ $record->student?->academic?->current_semester ?? $selectedSubject->semester ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $record->student?->academic?->roll_number ?? '-' }}</td>
                                <td class="px-4 py-3">{{ $record->student?->academic?->college_roll_number ?? '-' }}</td>
                                <td class="px-4 py-3 text-center font-semibold text-indigo-600">{{ $record->theory_marks ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">No marks found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
