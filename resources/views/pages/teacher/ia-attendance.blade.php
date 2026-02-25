@extends('layouts.app')

@section('content')
<div class="max-w-8xl mx-auto px-4 py-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Marks Breakup -- {{$paper->code}} - {{$paper->name}}</h2>
        </div>
    </div>

    <!-- Success / Error -->
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-4 bg-red-100 text-red-700 rounded-lg">
            {{ $errors->first() }}
        </div>
    @endif


    <!-- TABLE -->
    <div class="bg-white shadow-xl rounded-2xl overflow-hidden border">

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">

            <!-- HEADER ROW 1 (GROUP TITLES) -->
            <thead>
                <tr class="text-white text-xs uppercase tracking-wider">
                    <th colspan="2" class="bg-indigo-600 px-4 py-3 text-center" style="color:#fff">Theory</th>
                    <th colspan="4" class="bg-blue-600 px-4 py-3 text-center" style="color:#fff">Internal Assessment (IA)</th>
                    <th colspan="3" class="bg-purple-600 px-4 py-3 text-center" style="color:#fff">Tutorial</th>
                    <th colspan="4" class="bg-orange-500 px-4 py-3 text-center" style="color:#fff">Practical</th>
                    <th class="bg-green-600 px-4 py-3 text-center" style="color:#fff">Grand</th>
                </tr>

                <!-- HEADER ROW 2 (SUB TITLES) -->
                <tr class="bg-gray-50 text-gray-700 text-xs font-semibold">
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Theory</th>

                    <th class="px-4 py-2">IA Total</th>
                    <th class="px-4 py-2">Class Test</th>
                    <th class="px-4 py-2">Assignment</th>
                    <th class="px-4 py-2">Attendance</th>

                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">Activities</th>
                    <th class="px-4 py-2">Attendance</th>

                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2">CA</th>
                    <th class="px-4 py-2">Written</th>
                    <th class="px-4 py-2">Viva</th>

                    <th class="px-4 py-2">Total</th>
                </tr>
            </thead>

            <tbody class="text-center font-medium">

                @php 
                    $lecture = lectureMarksBreakup($paper->number_of_lectures);
                    $tute = tutorialMarksBreakup($paper->number_of_tutorials);
                    $practical  = practicalMarksBreakup($paper->number_of_practicals);
                    $practicalTotal = $practical["ca"] + $practical["written_exam"] + $practical["viva_voce"];
                    $grandTotal = ($paper->number_of_lectures + $paper->number_of_tutorials +$paper->number_of_practicals ) * 40;
                @endphp

                <tr class="hover:bg-gray-50 transition">

                    <!-- THEORY -->
                    <td class="px-4 py-3 bg-indigo-50">{{ $lecture["total"] ?? '-' }}</td>
                    <td class="px-4 py-3 bg-indigo-100 text-indigo-700 font-semibold">
                        {{ $lecture["theory"] ?? '-' }}
                    </td>

                    <!-- IA -->
                    <td class="px-4 py-3 bg-blue-50 font-semibold">
                        {{ $lecture["ia"] }}
                    </td>
                    <td class="px-4 py-3 bg-blue-50">{{ $lecture["ia_breakup"]["class_test"] }}</td>
                    <td class="px-4 py-3 bg-blue-50">{{ $lecture["ia_breakup"]["assignment"] }}</td>
                    <td class="px-4 py-3 bg-blue-100 text-blue-700 font-semibold">
                        {{ $lecture["ia_breakup"]["attendance"] }}
                    </td>

                    <!-- TUTORIAL -->
                    <td class="px-4 py-3 bg-purple-50 font-semibold">{{ $tute["ca"] }}</td>
                    <td class="px-4 py-3 bg-purple-50">{{ $tute["activities"] }}</td>
                    <td class="px-4 py-3 bg-purple-100 text-purple-700 font-semibold">
                        {{ $tute["attendance"] }}
                    </td>

                    <!-- PRACTICAL -->
                    <td class="px-4 py-3 bg-orange-50 font-semibold">
                        {{ $practicalTotal }}
                    </td>
                    <td class="px-4 py-3 bg-orange-50">{{ $practical["ca"] }}</td>
                    <td class="px-4 py-3 bg-orange-50">{{ $practical["written_exam"] }}</td>
                    <td class="px-4 py-3 bg-orange-100 text-orange-700 font-semibold">
                        {{ $practical["viva_voce"] }}
                    </td>

                    <!-- GRAND -->
                    <td class="px-4 py-3 bg-green-100 text-green-700 text-lg font-bold">
                        {{ $grandTotal }}
                    </td>

                </tr>

            </tbody>
        </table>
    </div>
</div>

</div>
@endsection
