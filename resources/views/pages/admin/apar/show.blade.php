@extends('layouts.app')

@section('content')
    @php
        $formData = $apar->form_data ?? [];
        $statusLabels = [
            'draft' => 'Draft',
            'submitted' => 'Awaiting review',
            'changes_requested' => 'Changes requested',
            'reviewed' => 'Reviewed',
        ];
        $canReview = in_array($apar->status, ['submitted', 'changes_requested'], true);
        $displayValue = function ($value) {
            if (is_bool($value)) return $value ? 'Yes' : 'No';
            if (is_array($value)) return implode(', ', $value);
            return filled($value) ? (string) $value : '—';
        };
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">
        <header class="rounded-3xl bg-gradient-to-br from-indigo-700 to-violet-700 p-6 text-white shadow-xl sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <a href="{{ route('admin.apar.index') }}" class="text-sm font-semibold text-indigo-100 hover:text-white">← APAR review desk</a>
                    <h1 class="mt-3 text-3xl font-bold">{{ $apar->faculty?->name ?? 'Faculty APAR' }}</h1>
                    <p class="mt-2 text-sm text-indigo-100">{{ $apar->faculty?->department?->name ?? 'Department unavailable' }} · Academic year {{ $apar->academic_year }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="rounded-full bg-white/15 px-4 py-2 text-sm font-semibold">{{ $statusLabels[$apar->status] ?? ucfirst($apar->status) }}</span>
                    <a href="{{ route('admin.apar.pdf', $apar) }}" class="rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-indigo-700 hover:bg-indigo-50">Download PDF</a>
                </div>
            </div>
        </header>

        @if(session('success'))
            <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <p class="font-semibold">Please correct the review form:</p>
                <ul class="mt-1 list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        @if($canReview)
            <form method="POST" action="{{ route('admin.apar.review', $apar) }}" class="rounded-2xl border border-indigo-100 bg-indigo-50/60 p-5 shadow-sm sm:p-6">
                @csrf
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-slate-900">Review decision</h2>
                    <p class="mt-1 text-sm text-slate-600">Record the HoD gradations, then mark the report reviewed or return it with required changes.</p>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="hod-teaching-grade" class="mb-1 block text-sm font-semibold text-slate-700">HoD grading · Teaching activity (B.1)</label>
                        <select id="hod-teaching-grade" name="hod_teaching_grade" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Not assigned</option>
                            @foreach(['Good', 'Satisfactory', 'Not Satisfactory'] as $grade)
                                <option value="{{ $grade }}" @selected(old('hod_teaching_grade', $apar->hod_teaching_grade) === $grade)>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="hod-b2-grade" class="mb-1 block text-sm font-semibold text-slate-700">HoD grading · Student / research activity (B.2)</label>
                        <select id="hod-b2-grade" name="hod_b2_grade" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Not assigned</option>
                            @foreach(['Good', 'Satisfactory', 'Not Satisfactory'] as $grade)
                                <option value="{{ $grade }}" @selected(old('hod_b2_grade', $apar->hod_b2_grade) === $grade)>{{ $grade }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="review-notes" class="mb-1 block text-sm font-semibold text-slate-700">Review notes</label>
                        <textarea id="review-notes" name="review_notes" rows="3" class="w-full rounded-xl border-slate-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Required when requesting changes.">{{ old('review_notes', $apar->review_notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-4 flex flex-wrap gap-3">
                    <button name="action" value="reviewed" class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700">Mark reviewed</button>
                    <button name="action" value="changes_requested" class="rounded-xl border border-amber-300 bg-white px-5 py-2.5 text-sm font-semibold text-amber-800 hover:bg-amber-50">Request changes</button>
                </div>
                <p class="mt-4 text-xs leading-5 text-slate-600">
                    Overall grade is calculated from the two HoD grades: Good teaching plus Good/Satisfactory B.2 gives Good; Satisfactory teaching plus Good/Satisfactory B.2 gives Satisfactory; otherwise it is Not Satisfactory.
                </p>
            </form>
        @elseif($apar->reviewed_at)
            <section class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-900">
                <p class="font-bold">Review recorded {{ $apar->reviewed_at->format('d M Y, h:i A') }}</p>
                <p class="mt-1">HoD grading · B.1: {{ $apar->hod_teaching_grade ?? 'Not assigned' }} · B.2: {{ $apar->hod_b2_grade ?? 'Not assigned' }} · Overall: {{ $apar->hod_overall_grade ?? 'Not assigned' }}</p>
                @if($apar->review_notes)<p class="mt-2 whitespace-pre-line">{{ $apar->review_notes }}</p>@endif
            </section>
        @endif

        @foreach($steps as $step)
            <section class="space-y-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">{{ $step['subtitle'] }}</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $step['title'] }}</h2>
                </div>
                @foreach($step['blocks'] as $block)
                    <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                        <h3 class="mb-4 font-bold text-slate-900">{{ $block['title'] }}</h3>
                        @if($block['type'] === 'fields')
                            <dl class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($block['fields'] as $field)
                                    @php
                                        $value = $field['type'] === 'readonly' ? $field['value'] : data_get($formData, $field['key']);
                                    @endphp
                                    <div class="{{ in_array($field['type'], ['textarea', 'readonly', 'checkbox'], true) ? 'sm:col-span-2 xl:col-span-3' : '' }}">
                                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $field['label'] }}</dt>
                                        <dd class="mt-1 whitespace-pre-line break-words text-sm text-slate-800">{{ $displayValue($value) }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        @else
                            @php $rows = data_get($formData, $block['key'], []); @endphp
                            @forelse($rows as $rowIndex => $row)
                                <div class="mb-3 rounded-xl bg-slate-50 p-4 last:mb-0">
                                    <p class="mb-3 text-xs font-bold uppercase tracking-wide text-slate-500">Entry {{ $rowIndex + 1 }}</p>
                                    <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                                        @foreach($block['fields'] as $field)
                                            <div>
                                                <dt class="text-xs font-semibold text-slate-500">{{ $field['label'] }}</dt>
                                                <dd class="mt-1 whitespace-pre-line break-words text-sm text-slate-800">{{ $displayValue($row[$field['key']] ?? null) }}</dd>
                                            </div>
                                        @endforeach
                                    </dl>
                                    @if($block['key'] === 'teaching_activities')
                                        @php
                                            $assigned = (float) ($row['classes_assigned'] ?? 0);
                                            $taught = (float) ($row['classes_taught'] ?? 0);
                                            $percentage = $assigned > 0 ? round(($taught / $assigned) * 100, 1) : 0;
                                            $grade = $percentage >= 80 ? 'Good' : ($percentage >= 70 ? 'Satisfactory' : 'Not Satisfactory');
                                        @endphp
                                        <p class="mt-3 text-xs font-semibold text-indigo-700">Classes taught: {{ $percentage }}% · {{ $grade }}</p>
                                    @endif
                                </div>
                            @empty
                                <p class="rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500">No entries provided.</p>
                            @endforelse
                            @if(count($rows))
                                @php
                                    $hasScore = in_array('research_score', array_column($block['fields'], 'key'), true);
                                    $scoreTotal = array_sum(array_map(fn ($row) => (float) ($row['research_score'] ?? 0), $rows));
                                @endphp
                                <p class="mt-3 text-xs font-semibold text-indigo-700">
                                    Total entries: {{ count($rows) }}
                                    @if($hasScore) · Total academic research score: {{ number_format($scoreTotal, 2) }}@endif
                                </p>
                            @endif
                        @endif
                    </article>
                @endforeach
            </section>
        @endforeach

        @if(count(data_get($formData, 'enclosures', [])))
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="mb-3 font-bold text-slate-900">Uploaded evidence</h2>
                <ul class="space-y-2">
                    @foreach(data_get($formData, 'enclosures', []) as $index => $file)
                        <li><a class="text-sm font-semibold text-indigo-700 hover:underline" href="{{ route('apar.evidence.download', ['apar' => $apar->id, 'index' => $index]) }}">{{ $file['name'] }}</a></li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
