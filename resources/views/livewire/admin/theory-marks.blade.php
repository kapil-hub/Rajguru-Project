<div class="p-4 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Theory Marks</h2>

    <div class="bg-white shadow rounded p-4 mb-6">
        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
            <div>
                <label class="block mb-1 font-medium">Department</label>
                <select wire:model.live="selectedDepartment" class="border p-2 rounded w-full">
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium">Course</label>
                <select wire:model.live="selectedCourse" class="border p-2 rounded w-full"
                    @disabled(!$selectedDepartment)>
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">{{ $course->program_code }} - {{ $course->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium">Semester</label>
                <select wire:model.live="selectedSemester" class="border p-2 rounded w-full">
                    <option value="">All Semesters</option>
                    @foreach($semesters as $label => $semester)
                        <option value="{{ $semester }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium">Paper Type</label>
                <select wire:model.live="selectedPaperType" class="border p-2 rounded w-full">
                    <option value="">All Eligible Types</option>
                    @foreach($paperTypes as $paperType)
                        <option value="{{ $paperType }}">{{ $paperType }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium">Paper</label>
                <select wire:model.live="selectedPaper" class="border p-2 rounded w-full">
                    <option value="">All Eligible Papers</option>
                    @foreach($papers as $paper)
                        <option value="{{ $paper->id }}">{{ $paper->code }} - {{ $paper->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block mb-1 font-medium">Teacher</label>
                <select wire:model.live="selectedTeacher" class="border p-2 rounded w-full">
                    <option value="">All Teachers</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button wire:click="resetFilters"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-5 py-2 rounded">
                    Clear Filters
                </button>
                <button wire:click="exportExcel" wire:loading.attr="disabled"
                    class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded">
                    <span wire:loading.remove wire:target="exportExcel">Export Excel</span>
                    <span wire:loading wire:target="exportExcel">Exporting...</span>
                </button>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded overflow-x-auto">
        <table class="w-full text-sm text-center">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Student Name</th>
                    <th class="p-3">Exam Roll Number</th>
                    <th class="p-3">College Roll Number</th>
                    <th class="p-3">Department</th>
                    <th class="p-3">Course</th>
                    <th class="p-3">Paper Code</th>
                    <th class="p-3">Paper Name</th>
                    <th class="p-3">Type</th>
                    <th class="p-3">Semester</th>
                    <th class="p-3">Teacher</th>
                    <th class="p-3">Maximum Theory Marks</th>
                    <th class="p-3">Obtained Theory Marks</th>
                </tr>
            </thead>
            <tbody wire:loading.class="opacity-50">
                @forelse($marksData as $mark)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3">{{ $mark->student?->name ?? '-' }}</td>
                        <td class="p-3">{{ $mark->student?->academic?->roll_number ?? '-' }}</td>
                        <td class="p-3">{{ $mark->student?->academic?->college_roll_number ?? '-' }}</td>
                        <td class="p-3">{{ $mark->paper?->course?->department?->name ?? '-' }}</td>
                        <td class="p-3">{{ $mark->paper?->course?->name ?? '-' }}</td>
                        <td class="p-3">{{ $mark->paper?->code ?? '-' }}</td>
                        <td class="p-3">{{ $mark->paper?->name ?? '-' }}</td>
                        <td class="p-3">{{ $mark->paper?->paper_type ?? '-' }}</td>
                        <td class="p-3">{{ $mark->paper?->semester ?? '-' }}</td>
                        <td class="p-3">{{ $mark->teacher?->name ?? '-' }}</td>
                        <td class="p-3">
                            {{ lectureMarksBreakup((int) ($mark->paper?->number_of_lectures ?? 0))['theory'] }}
                        </td>
                        <td class="p-3">{{ $mark->theory_marks ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" class="p-6 text-gray-500">No theory marks found for these filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $marksData->links() }}</div>
    </div>
</div>
