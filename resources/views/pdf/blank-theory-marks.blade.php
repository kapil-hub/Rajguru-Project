<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Blank Theory Marks</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { color: #333; font-size: 18px; margin-bottom: 4px; }
        .subtitle { margin: 0 0 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background: #eee; text-align: left; }
        td.mark { height: 32px; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>{{ $paper->code }} - {{ $paper->name }}</h1>
    <p class="subtitle">Blank Theory Marks Sheet | Maximum marks: {{ $maxMarks }}</p>
    <table>
        <thead>
            <tr>
                <th>S No</th>
                <th>Student Name</th>
                <th>Program / Semester</th>
                <th>Exam Roll Number</th>
                <th>College Roll Number</th>
                <th class="center">Theory Marks (max: {{ $maxMarks }})</th>
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
                    <td class="mark"></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
