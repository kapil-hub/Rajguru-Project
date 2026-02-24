<div>

    <h2 class="text-3xl font-bold text-indigo-600 mb-6">
        Filled Practical Subjects
    </h2>

    <!-- Subject Cards -->
    @if(!$showStudents)

        <div class="grid md:grid-cols-3 gap-6">

            @forelse($subjects as $subject)

                <div class="bg-white p-6 rounded-2xl shadow-lg hover:shadow-2xl transition">

                    <h3 class="text-lg font-semibold text-gray-800">
                        {{ $subject->code }}
                    </h3>

                    <p class="text-gray-600 mt-1">
                        {{ $subject->name }}
                    </p>

                    <button
                        wire:click="viewStudents({{ $subject->id }})"
                        class="mt-4 px-5 py-2 bg-indigo-600 text-white 
                               rounded-xl hover:bg-indigo-700 transition">

                        View Students
                    </button>

                </div>

            @empty

                <div class="col-span-3 text-center text-gray-500 py-10">
                    No practical marks entered yet.
                </div>

            @endforelse

        </div>

    @endif


    <!-- Students Table -->
    @if($showStudents)

    <div class="bg-white p-6 rounded-2xl shadow-xl">

        <!-- HEADER WITH PAPER DETAILS -->
        <div class="flex justify-between items-start mb-6">

            <div>

                <h3 class="text-2xl font-bold text-indigo-600">
                    {{ $selectedSubject->code ?? '' }}
                </h3>

                <p class="text-gray-600 text-lg">
                    {{ $selectedSubject->name ?? '' }}
                </p>

                <div class="mt-2 text-sm text-gray-500 space-x-4">

                    <span>
                        <strong>Filled:</strong>
                        {{ \Carbon\Carbon::parse($selectedSubject->created_at ?? now())->format('d M Y') }}
                    </span>

                    <span>
                        <strong>Last Modified:</strong>
                        {{ \Carbon\Carbon::parse($selectedSubject->updated_at ?? now())->format('d M Y') }}
                    </span>

                </div>

            </div>

            <button
                wire:click="$set('showStudents', false)"
                class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">

                ← Back
            </button>

        </div>

        <!-- STUDENTS TABLE -->
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left">

                <thead class="bg-indigo-100 uppercase text-xs text-gray-700">
                    <tr>
                        <th class="px-4 py-3">S No</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Student Program Code / Semester</th>
                        <th class="px-4 py-3">Roll No</th>
                         <th class="px-4 py-3">College Roll No</th>
                        <th class="px-4 py-3 text-center">CA</th>
                        <th class="px-4 py-3 text-center">ESP</th>
                        <th class="px-4 py-3 text-center">Viva</th>
                        <th class="px-4 py-3 text-center">Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y">

                    @foreach($students as $index => $record)

                        <tr class="hover:bg-gray-50 transition">

                            <td class="px-4 py-3">{{ $index + 1 }}</td>

                            <td class="px-4 py-3">
                                {{ $record->student->name }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $record->student->academic->course->program_code ?? 'N/A' . ' / ' . $record->student->academic->current_semester }}
                            </td>

                            <td class="px-4 py-3">
                                {{ $record->student->academic->roll_number ?? '-' }}
                            </td>
                             <td class="px-4 py-3">
                                {{ $record->student->academic->college_roll_number ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                {{ $record->continuous_assessment }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $record->end_sem_practical }}
                            </td>

                            <td class="px-4 py-3 text-center">
                                {{ $record->viva_voce }}
                            </td>

                            <td class="px-4 py-3 text-center font-bold text-indigo-600">
                                {{ $record->total_marks }}
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>
        </div>

    </div>

@endif

</div>
