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
        <header class="flex flex-col gap-5 rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-xl sm:flex-row sm:items-end sm:justify-between sm:p-8">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-100">Faculty services</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">My APAR submissions</h1>
                <p class="mt-3 text-sm leading-6 text-indigo-100">View your annual reports, resume saved drafts, and download a PDF copy.</p>
            </div>
            <a href="{{ route('teacher.apar.create') }}" class="inline-flex shrink-0 items-center justify-center rounded-xl bg-white px-5 py-3 text-sm font-bold text-indigo-700 shadow-sm transition hover:bg-indigo-50">
                Create new APAR
            </a>
        </header>

        @if(session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ session('error') }}</div>
        @endif

        <form method="GET" action="{{ route('teacher.apar') }}" class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:grid-cols-2 lg:grid-cols-3">
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
            <div class="flex items-end gap-2">
                <button class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Apply filters</button>
                <a href="{{ route('teacher.apar') }}" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Clear</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div>
                    <h2 class="font-bold text-slate-900">Your annual reports</h2>
                    <p class="mt-1 text-sm text-slate-500">{{ $submissions->count() }} record(s)</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-5 py-3">Academic year</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Last updated</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($submissions as $submission)
                            <tr class="hover:bg-slate-50/70">
                                <td class="px-5 py-4 font-semibold text-slate-900">{{ $submission->academic_year }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$submission->status] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $statusLabels[$submission->status] ?? ucfirst($submission->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ $submission->updated_at?->format('d M Y, h:i A') ?? '—' }}</td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-3">
                                        <a href="{{ route('teacher.apar.resume', $submission) }}" class="font-semibold text-indigo-700 hover:underline">
                                            {{ in_array($submission->status, ['draft', 'changes_requested'], true) ? 'Resume' : 'View' }}
                                        </a>
                                        <a href="{{ route('teacher.apar.pdf', $submission) }}" class="font-semibold text-slate-600 hover:text-slate-900">PDF</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center">
                                    <p class="font-semibold text-slate-700">No APAR submissions yet</p>
                                    <p class="mt-1 text-sm text-slate-500">Create a report to get started. Drafts will appear here so you can resume them later.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection
