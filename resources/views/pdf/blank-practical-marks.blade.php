<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blank Practical Marks</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #333; font-size: 18px; margin-bottom: 4px; }
        .subtitle { margin: 0 0 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #eee; text-align: left; }
        td.mark { height: 28px; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $paper->code }} - {{ $paper->name }}</h1>
    <p class="subtitle">Blank Practical Marks Sheet | Maximum marks: {{ $breakup['total'] }}</p>
    <table>
        <thead>
            <tr>
                <th>S No</th>
                <th>Student Name</th>
                <th>Program / Semester</th>
                <th>Exam Roll Number</th>
                <th>College Roll Number</th>
                @if($onlyTotal)
                    <th class="center">Practical Total (max: {{ $breakup['total'] }})</th>
                    <th class="center">Grand Total</th>
                @else
                    <th class="center">CA (max: {{ $breakup['ca'] }})</th>
                    <th class="center">ESP (max: {{ $breakup['written_exam'] }})</th>
                    <th class="center">Viva (max: {{ $breakup['viva_voce'] }})</th>
                    <th class="center">Grand Total (max: {{ $breakup['total'] }})</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $student->name }}</td>
                    <td>{{ $student->academic?->course?->program_code ?? 'N/A' }} / {{ $student->academic?->current_semester ?? $paper->semester ?? '-' }}</td>
                    <td>{{ $student->academic?->roll_number ?? '-' }}</td>
                    <td>{{ $student->academic?->college_roll_number ?? '-' }}</td>
                    @if($onlyTotal)
                        <td class="mark"></td>
                        <td class="mark"></td>
                    @else
                        <td class="mark"></td>
                        <td class="mark"></td>
                        <td class="mark"></td>
                        <td class="mark"></td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
