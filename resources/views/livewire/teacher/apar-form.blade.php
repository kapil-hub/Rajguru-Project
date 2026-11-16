<div class="mx-auto max-w-7xl space-y-6">
    @php
        $activeStep = $steps[$step];
        $readOnly = $this->isReadOnly();
        $completion = (int) round((($step + 1) / count($steps)) * 100);
        $statusStyles = [
            'draft' => 'bg-slate-100 text-slate-700',
            'submitted' => 'bg-blue-100 text-blue-800',
            'changes_requested' => 'bg-amber-100 text-amber-800',
            'reviewed' => 'bg-emerald-100 text-emerald-800',
        ];
        $statusLabels = [
            'draft' => 'Draft',
            'submitted' => 'Awaiting review',
            'changes_requested' => 'Changes requested',
            'reviewed' => 'Reviewed',
        ];
    @endphp

    <header class="overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-6 text-white shadow-xl sm:p-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-100">Faculty services · Annual cycle</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">Annual Performance Appraisal</h1>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-indigo-100">
                    Complete the APAR in short sections. Continue to save your progress, or save the draft and return to your submissions.
                </p>
            </div>
            <div class="min-w-[220px] rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur">
                <p class="text-xs font-medium uppercase tracking-wide text-indigo-100">Academic year</p>
                <p class="mt-2 text-xl font-bold text-white">{{ $academicYear }}</p>
                <span class="mt-3 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $statusStyles[$status] ?? $statusStyles['draft'] }}">
                    {{ $statusLabels[$status] ?? ucfirst($status) }}
                </span>
                @if($readOnly)
                    <a href="{{ route('teacher.apar') }}" class="mt-3 block text-sm font-semibold text-white underline decoration-white/50 underline-offset-4 hover:decoration-white">Back to my APARs</a>
                @else
                    <button type="button" wire:click="saveDraft" wire:loading.attr="disabled"
                        class="mt-3 block text-left text-sm font-semibold text-white underline decoration-white/50 underline-offset-4 hover:decoration-white disabled:opacity-50">Save and return to my APARs</button>
                @endif
            </div>
        </div>
    </header>

    @if (session()->has('apar_message'))
        <div role="status" class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('apar_message') }}
        </div>
    @endif
    @if ($status === 'changes_requested')
        <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
            <p class="font-semibold">The administrator requested changes.</p>
            <p class="mt-1 whitespace-pre-line">{{ $reviewNotes }}</p>
        </div>
    @endif
    @if ($readOnly && !session()->has('apar_message'))
        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
            This APAR has been published. The form is now read-only; contact the administrator if it needs to be reopened.
        </div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Step {{ $step + 1 }} of {{ count($steps) }}</p>
                <h2 class="mt-1 text-xl font-bold text-slate-900">{{ $activeStep['title'] }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $activeStep['subtitle'] }}</p>
            </div>
            <span class="hidden text-sm font-semibold text-slate-500 sm:block">{{ $completion }}% of steps</span>
        </div>
        <div class="mt-5 h-2 overflow-hidden rounded-full bg-slate-100">
            <div class="h-full rounded-full bg-indigo-600 transition-all" style="width: {{ $completion }}%"></div>
        </div>
        <nav aria-label="APAR steps" class="mt-5 grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
            @foreach($steps as $index => $navStep)
                <button type="button" wire:click="goToStep({{ $index }})"
                    @disabled($index > $step)
                    aria-current="{{ $index === $step ? 'step' : 'false' }}"
                    class="min-h-16 rounded-xl px-3 py-2 text-left text-xs font-semibold leading-4 transition {{ $index === $step ? 'bg-indigo-600 text-white' : ($index < $step ? 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100' : 'bg-slate-50 text-slate-500') }}">
                    <span class="block">0{{ $index + 1 }}</span>
                    <span class="mt-1 block whitespace-normal">{{ $navStep['title'] }}</span>
                </button>
            @endforeach
        </nav>
    </section>

    <main class="space-y-5">
        @foreach($activeStep['blocks'] as $block)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">{{ $block['title'] }}</h3>
                        @if($block['type'] === 'table')
                            <p class="mt-1 text-sm text-slate-500">Add one card for each applicable activity. Leave this section empty when it does not apply.</p>
                        @endif
                    </div>
                    @if($block['type'] === 'table' && !$readOnly)
                        <button type="button" wire:click="addRow('{{ $block['key'] }}')"
                            class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                            <span class="text-lg leading-none">+</span> Add entry
                        </button>
                    @endif
                </div>

                @if($block['type'] === 'fields')
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach($block['fields'] as $field)
                            @php
                                $fieldPath = 'formData.' . $field['key'];
                                $fieldValue = data_get($formData, $field['key']);
                                $wide = in_array($field['type'], ['textarea', 'readonly', 'checkbox'], true);
                            @endphp
                            <div class="{{ $wide ? 'sm:col-span-2 xl:col-span-3' : '' }}">
                                @if($field['type'] === 'checkbox')
                                    <label class="flex items-start gap-3 rounded-xl border border-indigo-100 bg-indigo-50/60 p-4 text-sm leading-6 text-slate-700">
                                        <input type="checkbox" wire:model="{{ $fieldPath }}" @disabled($readOnly)
                                            class="mt-1 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                        <span>{{ $field['label'] }} @if(!empty($field['required']))<b class="text-rose-600">*</b>@endif</span>
                                    </label>
                                @else
                                    <label class="mb-1.5 block text-sm font-semibold text-slate-700" for="{{ str_replace('.', '-', $fieldPath) }}">
                                        {{ $field['label'] }}
                                        @if(!empty($field['required']))<span class="text-rose-600">*</span>@endif
                                    </label>
                                    @if($field['type'] === 'readonly')
                                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-3 text-sm leading-6 text-slate-600">{{ $field['value'] }}</div>
                                    @elseif($field['type'] === 'textarea')
                                        <textarea id="{{ str_replace('.', '-', $fieldPath) }}" wire:model="{{ $fieldPath }}"
                                            rows="{{ $field['rows'] ?? 3 }}" maxlength="{{ $field['maxlength'] ?? 5000 }}"
                                            @disabled($readOnly)
                                            class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:bg-slate-50"></textarea>
                                        @if($field['key'] === 'other.future_plans')
                                            <p class="mt-1 text-right text-xs text-slate-500">{{ count(preg_split('/\s+/u', trim((string) $fieldValue), -1, PREG_SPLIT_NO_EMPTY) ?: []) }}/150 words</p>
                                        @endif
                                    @elseif($field['type'] === 'select')
                                        <select id="{{ str_replace('.', '-', $fieldPath) }}" wire:model="{{ $fieldPath }}" @disabled($readOnly)
                                            class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-3 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:bg-slate-50">
                                            <option value="">Select an option</option>
                                            @foreach($field['options'] as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <input id="{{ str_replace('.', '-', $fieldPath) }}" type="{{ $field['type'] }}"
                                            wire:model="{{ $fieldPath }}" @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
                                            @if(isset($field['maxlength'])) maxlength="{{ $field['maxlength'] }}" @endif
                                            @disabled($readOnly)
                                            class="w-full rounded-xl border border-slate-300 px-3.5 py-3 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:bg-slate-50">
                                    @endif
                                @endif
                                @error($fieldPath)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </div>
                @else
                    @php $rows = data_get($formData, $block['key'], []); @endphp
                    @forelse($rows as $rowIndex => $row)
                        <article wire:key="apar-{{ $block['key'] }}-{{ $rowIndex }}" class="mb-4 rounded-2xl border border-slate-200 bg-slate-50/70 p-4 sm:p-5">
                            <div class="mb-4 flex items-center justify-between">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Entry {{ $rowIndex + 1 }}</span>
                                @if(!$readOnly)
                                    <button type="button" wire:click="removeRow('{{ $block['key'] }}', {{ $rowIndex }})"
                                        class="rounded-lg px-3 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50">Remove</button>
                                @endif
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                @foreach($block['fields'] as $field)
                                    @php
                                        $fieldPath = 'formData.' . $block['key'] . '.' . $rowIndex . '.' . $field['key'];
                                        $fieldValue = $row[$field['key']] ?? '';
                                        $wide = $field['type'] === 'textarea';
                                    @endphp
                                    <div class="{{ $wide ? 'sm:col-span-2 xl:col-span-3' : '' }}">
                                        <label class="mb-1.5 block text-sm font-medium text-slate-700" for="{{ str_replace('.', '-', $fieldPath) }}">{{ $field['label'] }}</label>
                                        @if($field['type'] === 'textarea')
                                            <textarea id="{{ str_replace('.', '-', $fieldPath) }}" wire:model="{{ $fieldPath }}" rows="3" @disabled($readOnly)
                                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:bg-slate-100"></textarea>
                                        @elseif($field['type'] === 'select')
                                            <select id="{{ str_replace('.', '-', $fieldPath) }}" wire:model="{{ $fieldPath }}" @disabled($readOnly)
                                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:bg-slate-100">
                                                <option value="">Select an option</option>
                                                @foreach($field['options'] as $option)<option value="{{ $option }}">{{ $option }}</option>@endforeach
                                            </select>
                                        @else
                                            <input id="{{ str_replace('.', '-', $fieldPath) }}" type="{{ $field['type'] }}" wire:model="{{ $fieldPath }}"
                                                @if(isset($field['step'])) step="{{ $field['step'] }}" @endif @disabled($readOnly)
                                                class="w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 disabled:bg-slate-100">
                                        @endif
                                        @error($fieldPath)<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                                    </div>
                                @endforeach
                            </div>
                            @if($block['key'] === 'teaching_activities')
                                @php
                                    $assigned = (float) ($row['classes_assigned'] ?? 0);
                                    $taught = (float) ($row['classes_taught'] ?? 0);
                                    $percentage = $assigned > 0 ? round(($taught / $assigned) * 100, 1) : 0;
                                    $grade = $percentage >= 80 ? 'Good' : ($percentage >= 70 ? 'Satisfactory' : 'Not Satisfactory');
                                @endphp
                                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                                    <span class="rounded-full bg-white px-3 py-1.5 font-semibold text-slate-700">Classes taught: {{ $percentage }}%</span>
                                    <span class="rounded-full bg-white px-3 py-1.5 font-semibold {{ $grade === 'Good' ? 'text-emerald-700' : ($grade === 'Satisfactory' ? 'text-amber-700' : 'text-rose-700') }}">{{ $grade }}</span>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-xl border border-dashed border-slate-300 px-5 py-8 text-center text-sm text-slate-500">
                            No entries yet. Use <b>Add entry</b> if this section applies to you.
                        </div>
                    @endforelse
                    @if(count($rows))
                        @php
                            $hasScore = in_array('research_score', array_column($block['fields'], 'key'), true);
                            $scoreTotal = array_sum(array_map(fn ($row) => (float) ($row['research_score'] ?? 0), $rows));
                        @endphp
                        <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 rounded-xl bg-indigo-50 px-4 py-3 text-xs font-semibold text-indigo-800">
                            <span>Total entries: {{ count($rows) }}</span>
                            @if($hasScore)<span>Total academic research score: {{ number_format($scoreTotal, 2) }}</span>@endif
                        </div>
                    @endif
                @endif
            </section>
        @endforeach

        @if($step === count($steps) - 1)
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <label for="apar-evidence" class="mb-1.5 block text-sm font-semibold text-slate-700">Supporting documents</label>
                        <p class="mb-3 text-sm text-slate-500">Optional: upload certificates, timetable, reports, or other evidence (PDF/JPG/PNG, up to 10 MB each).</p>
                        @if(!$readOnly)
                            <input id="apar-evidence" type="file" wire:model="evidenceFiles" multiple accept=".pdf,.jpg,.jpeg,.png"
                                class="block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-600 file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-3 file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                            @error('evidenceFiles')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            @error('evidenceFiles.*')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            <button type="button" wire:click="uploadEvidence" wire:loading.attr="disabled" wire:target="uploadEvidence"
                                class="mt-3 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 disabled:opacity-50">
                                Upload to draft
                            </button>
                        @endif
                    </div>
                    @if(count(data_get($formData, 'enclosures', [])))
                        <div class="w-full rounded-xl bg-slate-50 p-4 sm:max-w-md">
                            <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Uploaded evidence</p>
                            <ul class="space-y-2">
                                @foreach(data_get($formData, 'enclosures', []) as $index => $file)
                                    <li class="flex items-center justify-between gap-3 text-sm">
                                        @if($submissionId)
                                            <a class="truncate font-medium text-indigo-700 hover:underline" href="{{ route('apar.evidence.download', ['apar' => $submissionId, 'index' => $index]) }}">{{ $file['name'] }}</a>
                                        @else
                                            <span class="truncate">{{ $file['name'] }}</span>
                                        @endif
                                        @if(!$readOnly)
                                            <button type="button" wire:click="removeEvidence({{ $index }})" class="shrink-0 text-xs font-semibold text-rose-600 hover:underline">Remove</button>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    </main>

    <footer class="sticky bottom-4 z-10 rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-xl backdrop-blur">
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                @if($step > 0)
                    <button type="button" wire:click="previousStep" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Back</button>
                @endif
                @if(!$readOnly)
                    <button type="button" wire:click="saveDraft" wire:loading.attr="disabled"
                        class="rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 disabled:opacity-50">Save draft & return to list</button>
                @endif
            </div>
            <div class="flex flex-wrap justify-end gap-2">
                @if(!$readOnly && $step < count($steps) - 1)
                    <button type="button" wire:click="nextStep" wire:loading.attr="disabled"
                        class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">Continue to next step</button>
                @elseif(!$readOnly)
                    <button type="button" wire:click="publish" wire:loading.attr="disabled" wire:confirm="Publish this APAR for administration review? You will not be able to edit it unless changes are requested."
                        class="rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50">Publish APAR</button>
                @else
                    <span class="self-center text-sm font-medium text-slate-500">Your published APAR is read-only.</span>
                @endif
            </div>
        </div>
    </footer>
</div>
