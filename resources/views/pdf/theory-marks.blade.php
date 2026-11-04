<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Theory Marks</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { color: #4f46e5; margin-bottom: 4px; }
        .subtitle { color: #555; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #e0e7ff; text-align: left; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $subject->code }} - {{ $subject->name }}</h1>
    <p class="subtitle">Theory Marks (maximum: {{ $maxMarks }})</p>
    <table>
        <thead>
            <tr>
                <th>S No</th>
                <th>Student Name</th>
                <th>Program</th>
                <th>Semester</th>
                <th>Exam Roll Number</th>
                <th>College Roll Number</th>
                <th class="center">Theory Marks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $record->student?->name ?? '-' }}</td>
                    <td>{{ $record->student?->academic?->course?->program_code ?? 'N/A' }}</td>
                    <td>{{ $record->student?->academic?->current_semester ?? $subject->semester ?? '-' }}</td>
                    <td>{{ $record->student?->academic?->roll_number ?? '-' }}</td>
                    <td>{{ $record->student?->academic?->college_roll_number ?? '-' }}</td>
                    <td class="center">{{ $record->theory_marks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
