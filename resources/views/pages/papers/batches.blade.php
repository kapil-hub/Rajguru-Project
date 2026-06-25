@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <a href="{{ route('papers.index') }}" class="hover:text-indigo-600 transition">Papers</a>
                    <span>&rsaquo;</span>
                    <span class="text-gray-900 font-medium">Manage Batches</span>
                </div>
                <h1 class="text-2xl font-bold text-gray-800 mt-2">Manage Batches</h1>
                <p class="text-sm text-gray-500">
                    Assign student groups (A, B, C...) to practicals and tutorials for <strong>{{ $paper->name }}</strong> (Code: <code>{{ $paper->code }}</code>).
                </p>
            </div>
            <div>
                <a href="{{ route('papers.index') }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
                    &larr; Back to Papers
                </a>
            </div>
        </div>
    </div>

    <!-- Batch Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Enrolled -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-gray-500">Total Enrolled Students</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">{{ count($studentPapers) }}</p>
        </div>

        <!-- Assigned Distribution -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm col-span-1 sm:col-span-3">
            <p class="text-sm font-medium text-gray-500 mb-2">Current Batch Distribution</p>
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-800 border">
                    Unassigned: {{ $counts->get('') ?? 0 }}
                </span>
                @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $b)
                    @if($counts->get($b) > 0)
                        <span class="inline-flex items-center rounded-xl bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 border border-indigo-200">
                            Batch {{ $b }}: {{ $counts->get($b) }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    </div>

    <!-- Main Table Form -->
    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('papers.batches.save', $paper->id) }}">
            @csrf

            <!-- Bulk Assign Utility -->
            <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-gray-100 pb-6">
                <div>
                    <h3 class="text-base font-bold text-gray-800">Assign Batches in Bulk</h3>
                    <p class="text-xs text-gray-500">Check students below, select a batch, and click Apply.</p>
                </div>
                <div class="flex items-center gap-2">
                    <select id="bulk_batch_select" class="rounded-xl border border-gray-300 px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <option value="">Choose Batch...</option>
                        <option value="">Unassign</option>
                        <option value="A">Batch A</option>
                        <option value="B">Batch B</option>
                        <option value="C">Batch C</option>
                        <option value="D">Batch D</option>
                        <option value="E">Batch E</option>
                        <option value="F">Batch F</option>
                    </select>
                    <button type="button" onclick="assignBulkBatch()" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 transition">
                        Apply to Selected
                    </button>
                </div>
            </div>

            <!-- Students List Table -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left text-sm text-gray-500">
                    <thead class="bg-gray-50 text-xs font-semibold uppercase text-gray-600 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-4 text-center w-12">
                                <input type="checkbox" onclick="toggleSelectAll(this)" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </th>
                            <th class="px-6 py-4">#</th>
                            <th class="px-6 py-4">Control / Roll No</th>
                            <th class="px-6 py-4">Student Name</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4 w-48 text-right">Assigned Batch</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($studentPapers as $index => $sp)
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <td class="px-6 py-4 text-center">
                                    <input type="checkbox" data-student-id="{{ $sp->id }}" class="student-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                </td>
                                <td class="px-6 py-4 text-gray-900 font-medium">
                                    {{ $index + 1 }}
                                </td>
                                <td class="px-6 py-4 text-gray-700 font-mono">
                                    {{ $sp->student->control_number ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-gray-900 font-semibold">
                                    {{ $sp->student->name ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    {{ $sp->student->email ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <select id="student_batch_{{ $sp->id }}"
                                            name="batches[{{ $sp->id }}]"
                                            class="rounded-xl border border-gray-300 px-3 py-1.5 text-xs text-gray-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition">
                                        <option value="">Unassigned</option>
                                        <option value="A" {{ $sp->batch === 'A' ? 'selected' : '' }}>Batch A</option>
                                        <option value="B" {{ $sp->batch === 'B' ? 'selected' : '' }}>Batch B</option>
                                        <option value="C" {{ $sp->batch === 'C' ? 'selected' : '' }}>Batch C</option>
                                        <option value="D" {{ $sp->batch === 'D' ? 'selected' : '' }}>Batch D</option>
                                        <option value="E" {{ $sp->batch === 'E' ? 'selected' : '' }}>Batch E</option>
                                        <option value="F" {{ $sp->batch === 'F' ? 'selected' : '' }}>Batch F</option>
                                    </select>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 font-medium">
                                    No students currently registered for this paper.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Form Submit -->
            @if(count($studentPapers) > 0)
                <div class="mt-6 flex justify-end gap-3 border-t border-gray-100 pt-6">
                    <a href="{{ route('papers.index') }}"
                       class="rounded-xl border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                        Save Batch Assignments
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
function toggleSelectAll(masterCb) {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = masterCb.checked;
    });
}

function assignBulkBatch() {
    const selectedBatch = document.getElementById('bulk_batch_select').value;
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Please select at least one student to apply bulk batch assignment.');
        return;
    }
    
    checkboxes.forEach(cb => {
        const id = cb.dataset.studentId;
        const selectEl = document.getElementById('student_batch_' + id);
        if (selectEl) {
            selectEl.value = selectedBatch;
        }
    });
}
</script>
@endsection
