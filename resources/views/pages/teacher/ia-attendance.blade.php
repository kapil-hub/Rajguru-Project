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
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Theory Total </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Theory</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">IA Total</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Class Test (IA)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Assignment (IA)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Attendance (IA)</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Tute ( CA )</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Practical ( CA )</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Practical ( Written Exam )</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold">Practical ( Viva Voce  )</th>

                        <th class="px-4 py-3 text-left text-xs font-semibold">Grand Total</th>
                    </tr>
                </thead>

                <tbody class="divide-y">
                   @php 
                        $lecture = lectureMarksBreakup($paper->number_of_lectures);
                        $tute = tutorialMarksBreakup($paper->number_of_tutorials);
                        $practical  = practicalMarksBreakup($paper->number_of_practicals)
                   @endphp
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">{{ $lecture["total"] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $lecture["theory"] ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $lecture["ia"] }}</td>
                        <td class="px-4 py-3 font-medium">{{ $lecture["ia_breakup"]["class_test"] }}</td>
                        <td class="px-4 py-3">{{ $lecture["ia_breakup"]["assignment"] }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                {{$lecture["ia_breakup"]["attendance"] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">{{$tute["ca"] }}</td>
                        <td class="px-4 py-3"> {{$practical["ca"]}}</td>
                        <td class="px-4 py-3"> {{$practical["written_exam"]}}</td>
                        <td class="px-4 py-3"> {{$practical["viva_voce"]}}</td>

                        <td class="px-4 py-3"><strong> {{ ($paper->number_of_lectures + $paper->number_of_tutorials +$paper->number_of_practicals ) * 40}} </strong></td>

                    </tr>
            </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
