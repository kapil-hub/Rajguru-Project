@extends('layouts.app')

@section('content')
    @php
        $statusLabels = [
            'draft' => 'Draft',
            'submitted' => 'Awaiting review',
            'changes_requested' => 'Changes requested',
            'reviewed' => 'Reviewed',
        ];
        $statusStyles = [
            'draft' => 'bg-slate-100 text-slate-700',
            'submitted' => 'bg-blue-100 text-blue-800',
            'changes_requested' => 'bg-amber-100 text-amber-800',
            'reviewed' => 'bg-emerald-100 text-emerald-800',
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <header class="rounded-3xl bg-gradient-to-br from-slate-900 via-indigo-950 to-indigo-800 p-7 text-white shadow-xl sm:p-9">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-200">Faculty services</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight">APAR review desk</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-indigo-100">Review published annual appraisal reports, return incomplete submissions for changes, and export a complete PDF record.</p>
        </header>

        @if(session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="GET" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 xl:grid-cols-5">
            <div>
                <label for="apar-year" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Academic year</label>
                <select id="apar-year" name="academic_year" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All years</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" @selected(($filters['academic_year'] ?? '') === $year)>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="apar-status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Status</label>
                <select id="apar-status" name="status" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All statuses</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="apar-department" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Department</label>
                <select id="apar-department" name="department_id" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) ($filters['department_id'] ?? '') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="apar-search" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Faculty member</label>
                <input id="apar-search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email"
                    class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Apply filters</button>
                <a href="{{ route('admin.apar.index') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">Faculty submissions</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $submissions->total() }} record(s)</p>
                </div>
                <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Submitted items appear first</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Faculty member</th>
                            <th class="px-5 py-3">Department</th>
                            <th class="px-5 py-3">Academic year</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Published</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($submissions as $submission)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">{{ $submission->faculty?->name ?? 'Faculty account unavailable' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $submission->faculty?->email }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $submission->faculty?->department?->name ?? '—' }}</td>
                                <td class="px-5 py-4 font-medium text-slate-700">{{ $submission->academic_year }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$submission->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $statusLabels[$submission->status] ?? ucfirst($submission->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $submission->submitted_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('admin.apar.show', $submission) }}" class="font-semibold text-indigo-700 hover:underline">Review</a>
                                        <a href="{{ route('admin.apar.pdf', $submission) }}" class="font-semibold text-slate-600 hover:text-slate-900">PDF</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-5 py-12 text-center text-slate-500">No APAR records match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($submissions->hasPages())
                <div class="border-t border-slate-100 px-5 py-4">{{ $submissions->links() }}</div>
            @endif
        </section>
    </div>
@endsection
