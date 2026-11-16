<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>APAR {{ $apar->academic_year }}</title>
    <style>
        @page { margin: 30px 34px 46px; }
        body { margin: 0; color: #172033; font-family: DejaVu Sans, sans-serif; font-size: 9px; line-height: 1.4; }
        p { margin: 0; }
        .cover { padding: 22px 24px; background: #172554; color: #fff; }
        .cover h1, .cover p, .cover span { color: #fff; }
        .brand { width: 100%; border-collapse: collapse; }
        .brand-mark { width: 42px; height: 42px; border: 1px solid #a5b4fc; border-radius: 21px; color: #fff; font-size: 14px; font-weight: bold; text-align: center; vertical-align: middle; }
        .brand-name { padding-left: 12px; color: #e0e7ff; font-size: 9px; font-weight: bold; letter-spacing: .5px; }
        .brand-subtitle { padding-left: 12px; color: #c7d2fe; font-size: 8px; }
        .cover-label { margin-top: 21px; color: #c7d2fe; font-size: 8px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
        h1 { margin: 5px 0 3px; color: #fff; font-size: 23px; line-height: 1.2; }
        .cover-subtitle { color: #e0e7ff; font-size: 10px; }
        .summary { width: 100%; margin-top: 12px; border-collapse: separate; border-spacing: 6px 0; }
        .summary td { width: 25%; padding: 9px 10px; border: 1px solid #e2e8f0; background: #f8fafc; vertical-align: top; }
        .summary-label { color: #64748b; font-size: 7px; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; }
        .summary-value { margin-top: 3px; color: #172033; font-size: 9px; font-weight: bold; overflow-wrap: anywhere; }
        .status { display: inline-block; padding: 3px 7px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .status-draft { background: #e2e8f0; color: #334155; }
        .status-submitted { background: #dbeafe; color: #1e40af; }
        .status-changes { background: #fef3c7; color: #92400e; }
        .status-reviewed { background: #d1fae5; color: #065f46; }
        .grades { width: 100%; margin: 10px 0 18px; border-collapse: collapse; }
        .grades td { width: 33.33%; padding: 9px 11px; border: 1px solid #c7d2fe; background: #eef2ff; }
        .grade-label { color: #4b5563; font-size: 7px; font-weight: bold; letter-spacing: .5px; text-transform: uppercase; }
        .grade-value { margin-top: 3px; color: #312e81; font-size: 10px; font-weight: bold; }
        .step { margin-top: 17px; }
        .step-heading { width: 100%; margin-bottom: 9px; border-collapse: collapse; page-break-after: avoid; }
        .step-number { width: 36px; color: #4338ca; font-size: 17px; font-weight: bold; vertical-align: top; }
        .step-subtitle { color: #64748b; font-size: 7px; font-weight: bold; letter-spacing: .8px; text-transform: uppercase; }
        .step-title { margin-top: 2px; color: #172554; font-size: 14px; font-weight: bold; }
        .block { margin: 8px 0 12px; page-break-inside: avoid; }
        .block-title { padding: 6px 8px; border-left: 3px solid #4f46e5; background: #eef2ff; color: #312e81; font-size: 9px; font-weight: bold; page-break-after: avoid; }
        .fields { width: 100%; border-collapse: collapse; }
        .fields td { width: 50%; padding: 6px 8px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .label { color: #64748b; font-size: 7px; font-weight: bold; letter-spacing: .35px; text-transform: uppercase; }
        .value { margin-top: 2px; color: #172033; font-size: 8.5px; white-space: pre-wrap; overflow-wrap: anywhere; }
        .entry { margin: 7px 0; border: 1px solid #dbe2ea; page-break-inside: avoid; }
        .entry-title { padding: 5px 8px; background: #f8fafc; color: #475569; font-size: 8px; font-weight: bold; }
        .entry .fields { padding: 0 4px; }
        .entry .fields td { padding: 5px 7px; }
        .entry-result { padding: 5px 8px; background: #f5f3ff; color: #4338ca; font-size: 8px; font-weight: bold; }
        .empty { padding: 8px; color: #64748b; font-style: italic; }
        .totals { margin-top: 5px; padding: 6px 8px; background: #f1f5f9; color: #334155; font-size: 8px; font-weight: bold; }
        .evidence, .notes { margin-top: 15px; padding: 10px 12px; border: 1px solid #e2e8f0; page-break-inside: avoid; }
        .evidence h2, .notes h2 { margin: 0 0 6px; color: #312e81; font-size: 10px; }
        .evidence p { margin: 2px 0; color: #334155; }
        .notes p { color: #334155; white-space: pre-wrap; }
        .signature { width: 100%; margin-top: 38px; border-collapse: collapse; page-break-inside: avoid; }
        .signature td { width: 33.33%; padding: 24px 8px 0; border-top: 1px solid #64748b; color: #475569; font-size: 8px; text-align: center; }
        .footer { position: fixed; right: 0; bottom: -27px; left: 0; color: #64748b; font-size: 7px; }
        .footer-right { float: right; }
        .footer-right:after { content: "Page " counter(page); }
    </style>
</head>
<body>
    @php
        $formData = $apar->form_data ?? [];
        $statusLabels = [
            'draft' => 'Draft',
            'submitted' => 'Awaiting review',
            'changes_requested' => 'Changes requested',
            'reviewed' => 'Reviewed',
        ];
        $statusClasses = [
            'draft' => 'status-draft',
            'submitted' => 'status-submitted',
            'changes_requested' => 'status-changes',
            'reviewed' => 'status-reviewed',
        ];
        $displayValue = function ($value) {
            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }
            if (is_array($value)) {
                return implode(', ', $value);
            }

            return filled($value) ? (string) $value : '—';
        };
    @endphp

    <header class="cover">
        <table class="brand">
            <tr>
                <td class="brand-mark">SR</td>
                <td>
                    <div class="brand-name">SHAHEED RAJGURU COLLEGE OF APPLIED SCIENCES FOR WOMEN</div>
                    <div class="brand-subtitle">University of Delhi</div>
                </td>
            </tr>
        </table>
        <p class="cover-label">Faculty annual record · CAS 2018</p>
        <h1>Annual Performance Appraisal</h1>
        <p class="cover-subtitle">Academic year {{ $apar->academic_year }}</p>
    </header>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Faculty member</div>
                <div class="summary-value">{{ $apar->faculty?->name ?? data_get($formData, 'personal.name', '—') }}</div>
            </td>
            <td>
                <div class="summary-label">Department</div>
                <div class="summary-value">{{ $apar->faculty?->department?->name ?? data_get($formData, 'personal.department', '—') }}</div>
            </td>
            <td>
                <div class="summary-label">Report status</div>
                <div class="summary-value">
                    <span class="status {{ $statusClasses[$apar->status] ?? 'status-draft' }}">{{ $statusLabels[$apar->status] ?? ucfirst($apar->status) }}</span>
                </div>
            </td>
            <td>
                <div class="summary-label">Published on</div>
                <div class="summary-value">{{ $apar->submitted_at?->format('d M Y, h:i A') ?? 'Not published' }}</div>
            </td>
        </tr>
    </table>

    <table class="grades">
        <tr>
            <td>
                <div class="grade-label">HoD grading · Teaching (B.1)</div>
                <div class="grade-value">{{ $apar->hod_teaching_grade ?? 'Pending review' }}</div>
            </td>
            <td>
                <div class="grade-label">HoD grading · Student / research (B.2)</div>
                <div class="grade-value">{{ $apar->hod_b2_grade ?? 'Pending review' }}</div>
            </td>
            <td>
                <div class="grade-label">Overall grade</div>
                <div class="grade-value">{{ $apar->hod_overall_grade ?? 'Pending review' }}</div>
            </td>
        </tr>
    </table>

    @foreach($steps as $stepIndex => $step)
        <section class="step">
            <table class="step-heading">
                <tr>
                    <td class="step-number">0{{ $stepIndex + 1 }}</td>
                    <td>
                        <div class="step-subtitle">{{ $step['subtitle'] }}</div>
                        <div class="step-title">{{ $step['title'] }}</div>
                    </td>
                </tr>
            </table>

            @foreach($step['blocks'] as $block)
                <div class="block">
                    <div class="block-title">{{ $block['title'] }}</div>
                    @if($block['type'] === 'fields')
                        <table class="fields">
                            @foreach(array_chunk($block['fields'], 2) as $fieldPair)
                                <tr>
                                    @foreach($fieldPair as $field)
                                        @php
                                            $value = $field['type'] === 'readonly' ? $field['value'] : data_get($formData, $field['key']);
                                        @endphp
                                        <td>
                                            <div class="label">{{ $field['label'] }}</div>
                                            <div class="value">{{ $displayValue($value) }}</div>
                                        </td>
                                    @endforeach
                                    @if(count($fieldPair) === 1)<td></td>@endif
                                </tr>
                            @endforeach
                        </table>
                    @else
                        @php $rows = data_get($formData, $block['key'], []); @endphp
                        @forelse($rows as $rowIndex => $row)
                            <div class="entry">
                                <div class="entry-title">Entry {{ $rowIndex + 1 }}</div>
                                <table class="fields">
                                    @foreach(array_chunk($block['fields'], 2) as $fieldPair)
                                        <tr>
                                            @foreach($fieldPair as $field)
                                                <td>
                                                    <div class="label">{{ $field['label'] }}</div>
                                                    <div class="value">{{ $displayValue($row[$field['key']] ?? null) }}</div>
                                                </td>
                                            @endforeach
                                            @if(count($fieldPair) === 1)<td></td>@endif
                                        </tr>
                                    @endforeach
                                </table>
                                @if($block['key'] === 'teaching_activities')
                                    @php
                                        $assigned = (float) ($row['classes_assigned'] ?? 0);
                                        $taught = (float) ($row['classes_taught'] ?? 0);
                                        $percentage = $assigned > 0 ? round(($taught / $assigned) * 100, 1) : 0;
                                        $grade = $percentage >= 80 ? 'Good' : ($percentage >= 70 ? 'Satisfactory' : 'Not Satisfactory');
                                    @endphp
                                    <div class="entry-result">Classes taught: {{ $percentage }}% · {{ $grade }}</div>
                                @endif
                            </div>
                        @empty
                            <div class="empty">No entries provided.</div>
                        @endforelse
                        @if(count($rows))
                            @php
                                $hasScore = in_array('research_score', array_column($block['fields'], 'key'), true);
                                $scoreTotal = array_sum(array_map(fn ($row) => (float) ($row['research_score'] ?? 0), $rows));
                            @endphp
                            <div class="totals">
                                Total entries: {{ count($rows) }}
                                @if($hasScore) · Total academic research score: {{ number_format($scoreTotal, 2) }}@endif
                            </div>
                        @endif
                    @endif
                </div>
            @endforeach
        </section>
    @endforeach

    @if(count(data_get($formData, 'enclosures', [])))
        <section class="evidence">
            <h2>Supporting evidence</h2>
            @foreach(data_get($formData, 'enclosures', []) as $file)
                <p>{{ $file['name'] ?? 'Evidence file' }}</p>
            @endforeach
        </section>
    @endif

    @if($apar->review_notes)
        <section class="notes">
            <h2>Administration review notes</h2>
            <p>{{ $apar->review_notes }}</p>
        </section>
    @endif

    <table class="signature">
        <tr>
            <td>Faculty member</td>
            <td>Head of Department</td>
            <td>Administration</td>
        </tr>
    </table>

    <div class="footer">
        Anuvartan · APAR {{ $apar->academic_year }} · Generated {{ now()->format('d M Y') }}
        <span class="footer-right"></span>
    </div>
</body>
</html>
