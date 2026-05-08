<!DOCTYPE html>
<html>

<head>

    <meta charset="utf-8">

    <title>
        IA Marks PDF
    </title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h2 {
            margin: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
        }

        th {
            background: #f0f0f0;
            text-align: center;
        }

        td {
            text-align: center;
        }

        .text-left {
            text-align: left;
        }
    </style>

</head>

<body>

    <div class="header">

        <h2>
            IA Marks Details
        </h2>

        <p>
            {{ $paper->code }} - {{ $paper->name }}
            |
            Semester {{ $semesterId }}
            |
            Section {{ $section }}
        </p>

    </div>

    <table>

        <thead>

            <tr>

                <th>#</th>

                <th>
                    Student
                </th>

                <th>
                    Exam Roll Number
                </th>

                <th>
                    College Roll Number
                </th>

                @if(
                        (($paper->paper_type == 'SEC'
                            || $paper->paper_type == 'VAC'
                            || $paper->paper_type == 'AEC')
                            && ($paper->number_of_lectures == 1
                                && $paper->number_of_tutorials == 0
                                && $paper->number_of_practicals == 1))
                        || ($paper->paper_type == 'AEC'
                            && $paper->number_of_lectures == 2
                            && $paper->number_of_tutorials == 0
                            && $paper->number_of_practicals == 0)
                    )

                    <th>
                        Total IA Marks
                    </th>

                @else

                    <th>
                        Attendance
                    </th>

                    <th>
                        Class Test
                    </th>

                    <th>
                        Assignment
                    </th>

                    <th>
                        Total IA Marks
                    </th>

                @endif

                <th>
                    Tutorial Activities
                </th>

                <th>
                    Tutorial Attendance
                </th>

                <th>
                    Total Tutorial Marks
                </th>

                <th>
                    Grand Total
                </th>

            </tr>

        </thead>

        <tbody>

            @foreach($marks as $i => $m)

                <tr>

                    <td>
                        {{ $i + 1 }}
                    </td>

                    <td class="text-left">
                        {{ $m->student->name }}
                    </td>

                    <td>
                        {{ optional($m->student->academic)->roll_number }}
                    </td>

                    <td>
                        {{ optional($m->student->academic)->college_roll_number }}
                    </td>

                    @if(
                            (($paper->paper_type == 'SEC'
                                || $paper->paper_type == 'VAC'
                                || $paper->paper_type == 'AEC')
                                && ($paper->number_of_lectures == 1
                                    && $paper->number_of_tutorials == 0
                                    && $paper->number_of_practicals == 1))
                            || ($paper->paper_type == 'AEC'
                                && $paper->number_of_lectures == 2
                                && $paper->number_of_tutorials == 0
                                && $paper->number_of_practicals == 0)
                        )

                        <td>
                            {{ $m->total }}
                        </td>

                    @else

                        <td>
                            {{ $m->attendance }}
                        </td>

                        <td>
                            {{ $m->class_test }}
                        </td>

                        <td>
                            {{ $m->assignment }}
                        </td>

                        <td>
                            {{ $m->total }}
                        </td>

                    @endif

                    <td>
                        {{ $m->tute_ca ?? '-' }}
                    </td>

                    <td>
                        {{ $m->tute_attendance ?? '-' }}
                    </td>

                    <td>
                        {{ $m->total_tute_marks ?? '-' }}
                    </td>

                    <td>
                        <strong>
                            {{ $m->grand_total }}
                        </strong>
                    </td>

                </tr>

            @endforeach
            @php

                $colspan =
                    (
                        (($paper->paper_type == 'SEC'
                            || $paper->paper_type == 'VAC'
                            || $paper->paper_type == 'AEC')
                            && ($paper->number_of_lectures == 1
                                && $paper->number_of_tutorials == 0
                                && $paper->number_of_practicals == 1))
                        || ($paper->paper_type == 'AEC'
                            && $paper->number_of_lectures == 2
                            && $paper->number_of_tutorials == 0
                            && $paper->number_of_practicals == 0)
                    )
                    ? 9
                    : 11;

            @endphp
            <tr>

                <td colspan="{{ $colspan }}" style="border: none; padding-top: 60px;">

                    <table style="width:100%; border:none;">

                        <tr>

                            <td style="width:50%; border:none; text-align:left;">

                                ___________________________

                                <br><br>

                                <strong>Internal Examiner</strong>

                            </td>

                            <td style="width:50%; border:none; text-align:right;">

                                ___________________________

                                <br><br>

                                <strong>External Examiner</strong>

                            </td>

                        </tr>

                    </table>

                </td>

            </tr>
        </tbody>

    </table>

</body>

</html>