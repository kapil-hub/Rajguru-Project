<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Practical Marks</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            border-left: 6px solid #4f46e5;
            padding: 15px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #4f46e5;
        }

        .subtitle {
            color: #555;
            margin-top: 5px;
        }

        .meta {
            font-size: 11px;
            color: #777;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #e0e7ff;
            padding: 8px;
            text-align: left;
            font-size: 11px;
            border: 1px solid #000;
            /* 🔥 dark border */
        }

        td {
            padding: 8px;
            border: 1px solid #000;
            /* 🔥 dark border */
        }

        .center {
            text-align: center;
        }

        .total {
            font-weight: bold;
            color: #4f46e5;
        }
    </style>
</head>

<body>

    @php
        $logo = public_path('/images/logo/logo.png');
        $logoBase64 = base64_encode(file_get_contents($logo));
    @endphp

    <div style="display: flex; align-items: center; margin-bottom: 15px;">

        <center><img src="data:image/png;base64,{{ $logoBase64 }}" style="height: 60px; margin-right: 15px;">
            <br>
            <h3 style="color: #4f46e5;">
                Shaheed Rajguru College of Applied <br> Sciences for Women<br>(University Of Delhi)
            </h3>
        </center>

        <div>
            <div style="font-size:14px; font-weight:bold; color:#4f46e5">
                UPC : {{ $subject->code }}
            </div>

            <div style="color:#4f46e5;">
                Subject Name : {{ $subject->name }}
            </div>
            {{-- <div style="color:#555;">
                Teacher : <strong>{{ auth('teacher')->user()->name }}</strong>
            </div> --}}
            <div class="meta">
                Filled: {{ \Carbon\Carbon::parse($subject->created_at)->format('d M Y') }}
                &nbsp;&nbsp;
                Last Modified: {{ \Carbon\Carbon::parse($subject->updated_at)->format('d M Y') }}
            </div>



        </div>

    </div>

    <table>
        <thead>
            <tr>
                <th>S No</th>
                <th>Name</th>
                <th>Program </th>
                <th>Semester</th>
                <th>Roll No</th>
                <th>College Roll</th>
                @if($showTotalOnly)
                    <th class="center">Total</th>
                    <th class="center">Grand Total</th>
                @else
                    <th class="center">CA</th>
                    <th class="center">ESP</th>
                    <th class="center">Viva</th>
                    <th class="center">Total</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @foreach($students as $index => $record)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $record->student->name }}</td>

                    <td>
                        {{ $record->student->academic->course->program_code ?? 'N/A' }}

                    </td>
                    <td>
                        {{ $assignment->semester_id ?? 'N/A' }}

                    </td>
                    <td>{{ $record->student->academic->roll_number ?? '-' }}</td>
                    <td>{{ $record->student->academic->college_roll_number ?? '-' }}</td>
                    @if($showTotalOnly)
                        <td class="center total">{{ $record->total_marks }}</td>
                        <td class="center total">{{ $record->total_marks }}</td>
                    @else

                        <td class="center">{{ $record->continuous_assessment }}</td>
                        <td class="center">{{ $record->end_sem_practical }}</td>
                        <td class="center">{{ $record->viva_voce }}</td>

                        <td class="center total">{{ $record->total_marks }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td style="padding-top:30px;">Internal Examiner</td>
                <td colspan="4" style="padding-top:30px;">_________________</td>

                <td style="padding-top:30px;">External Examiner</td>
                <td colspan="4" style="padding-top:30px;">_________________</td>
            </tr>
        </tfoot>
    </table>

</body>

</html>