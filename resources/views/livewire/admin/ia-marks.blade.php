<div class="p-4 max-w-7xl mx-auto">

    <h2 class="text-2xl font-bold mb-6">
        IA Marks
    </h2>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4">
            {{ session('message') }}
        </div>
    @endif

    {{-- Error Message --}}
    @if (session()->has('error'))
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    {{-- GLOBAL LOADER --}}
    <div wire:loading.flex class="fixed inset-0 bg-black bg-opacity-40 z-50 items-center justify-center">

        <div class="bg-white px-6 py-3 rounded shadow text-lg font-semibold flex items-center gap-2">

            <svg class="animate-spin h-5 w-5 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">

                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>

                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z">
                </path>
            </svg>

            Processing...

        </div>
    </div>

    {{-- FILTER SECTION --}}
    <div class="bg-white shadow rounded p-4 mb-6">

        <div class="grid md:grid-cols-3 gap-4">

            {{-- Department --}}
            <div>
                <label class="block mb-1 font-medium">
                    Select Department
                </label>

                <select wire:model="selectedDepartment" class="border p-2 rounded w-full">

                    <option value="">
                        Select Department
                    </option>

                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">
                            {{ $department->name }}
                        </option>
                    @endforeach

                </select>

                @error('selectedDepartment')
                    <div class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Semester --}}
            <div>
                <label class="block mb-1 font-medium">
                    Select Semester
                </label>

                <select wire:model="selectedSemester" class="border p-2 rounded w-full">

                    <option value="">
                        Select Semester
                    </option>

                    @foreach($semesters as $key => $sem)
                        <option value="{{ $sem }}">
                            {{ $key }}
                        </option>
                    @endforeach

                </select>

                @error('selectedSemester')
                    <div class="text-red-500 text-sm mt-1">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            {{-- Button --}}
            <div class="flex items-end gap-2">
                <button wire:click="loadData" wire:loading.attr="disabled"
                    class="bg-green-500 hover:bg-green-600 text-white px-5 py-2 rounded">

                    <span wire:loading.remove wire:target="loadData">
                        Show
                    </span>

                    <span wire:loading wire:target="loadData">
                        Loading...
                    </span>

                </button>

                {{-- Export Button --}}
                <button wire:click="exportExcel" wire:loading.attr="disabled"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-2 rounded">

                    <span wire:loading.remove wire:target="exportExcel">
                        Export Excel
                    </span>

                    <span wire:loading wire:target="exportExcel">
                        Exporting...
                    </span>

                </button>
            </div>

        </div>
    </div>

    {{-- TABLE --}}
    <div class="bg-white shadow rounded overflow-x-auto">

        <table class="w-full text-sm text-center">

            <thead class="bg-gray-100">

                <tr>
                    <th class="p-3">Student Name</th>
                    <th class="p-3">Exam Roll Number</th>
                    <th class="p-3">Paper Code</th>
                    <th class="p-3">Paper Name</th>
                    <th class="p-3">Semester</th>
                    <th class="p-3">Program Code</th>
                    <th class="p-3">Program Name</th>
                    <th class="p-3">Maximum Marks (IA)</th>
                    <th class="p-3">Obtained Marks (IA)</th>
                    <th class="p-3">Maximum Marks (Tutorial)</th>
                    <th class="p-3">Obtained Marks (Tutorial)</th>
                </tr>

            </thead>

            <tbody wire:loading.class="opacity-50">

                @forelse($marksData as $mark)

                    <tr class="border-b hover:bg-gray-50">

                        <td class="p-3">
                            {{ $mark->student->name ?? '' }}
                        </td>

                        <td class="p-3">
                            {{ $mark->student->academic->roll_number ?? '' }}
                        </td>

                        <td class="p-3">
                            {{ $mark->paper->code ?? '' }}
                        </td>

                        <td class="p-3">
                            {{ $mark->paper->name ?? '' }}
                        </td>

                        <td class="p-3">
                            {{ $mark->semester_id ?? '' }}
                        </td>

                        <td class="p-3">
                            {{ $mark->course->program_code ?? '' }}
                        </td>

                        <td class="p-3">
                            {{ $mark->course->name ?? '' }}
                        </td>

                        <td class="p-3">
                            {{ lectureMarksBreakup($mark->paper->number_of_lectures ?? 0)['ia'] ?? 0 }}
                        </td>

                        <td class="p-3">
                            {{ round($mark->total) ?? 0 }}
                        </td>
                        <td class="p-3">
                            {{ tutorialMarksBreakup($mark->paper->number_of_tutorial ?? 0)['total_tute'] ?? 0 }}
                        </td>

                        <td class="p-3">
                            {{ round($mark->tute_ca + $mark->tute_attendance) ?? 0 }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="11" class="p-6 text-gray-500">
                            No records found
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

        {{-- PAGINATION --}}
        <div class="p-4">
            {{ $marksData->links() }}
        </div>

    </div>

</div>