@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <h1 class="text-2xl font-bold mb-6">Attendance Configuration</h1>

    @if($setting)
        {{-- VIEW MODE --}}
        <div class="bg-white rounded-xl shadow border p-6">
            <p class="text-green-600 font-semibold mb-4">
                ✔ Attendance is locked for this semester
            </p>

            <div class="grid grid-cols-2 gap-4 text-sm">
                <p><strong>Academic Session:</strong> {{ $setting->academic_session }}</p>
                <p><strong>Attendance Type:</strong> {{ ucfirst($setting->attendance_type) }}</p>
                <p><strong>Semester Type:</strong> {{ ucfirst($setting->semester_type) }}</p>
                <p><strong>Month Range:</strong>
                   {{ \Carbon\Carbon::createFromDate(
                                    now()->year,
                                    $setting->start_month,
                                    1
                                )->format('M Y')}}  -  {{\Carbon\Carbon::createFromDate(
                                    now()->year,
                                    $setting->end_month,
                                    1
                                )->format('M Y')}}
                </p>
                <p><strong>Status:</strong> Active</p>
            </div>

            <div class="mt-4 text-xs text-gray-500">
                This configuration cannot be changed once applied.
            </div>
        </div>
    @else
        {{-- CREATE MODE --}}
        <form method="POST" action="{{ route('admin.attendance.settings.store') }}"
              class="bg-white rounded-xl shadow border p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                <select name="attendance_type" required class="form-select">
                    <option value="">Attendance Type</option>
                    <option value="monthly">Monthly</option>
                    <option value="daily">Daily</option>
                </select>

                <select name="semester_type" required class="form-select">
                    <option value="">Semester Type</option>
                    <option value="odd">Odd</option>
                    <option value="even">Even</option>
                </select>

               <div>
                    <label class="block text-sm mb-1">Academic Session</label>
                    <select name="academic_session" id="edit_academic_session" required class="modern-input w-full">
                    <option value="">Academic Session</option>
                    @php
                        $year = date('Y');
                        $next = $year + 1;
                        $prev = $year - 1;
                    @endphp
                    <option value="{{ $year.'-'.substr($next,-2) }}">{{ $year.'-'.substr($next,-2) }}</option>
                    <option value="{{ $prev.'-'.substr($year,-2) }}">{{ $prev.'-'.substr($year,-2) }}</option>
                </select>
                </div>

            <div>
                <label class="block text-sm mb-1">Start Month</label>
                <select name="start_month" class="form-select w-full">
                    @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}">{{ date('F', mktime(0,0,0,$i,1)) }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm mb-1">End Month</label>
                <select name="end_month" class="form-select w-full">
                    @for($i=1;$i<=12;$i++)
                        <option value="{{ $i }}">{{ date('F', mktime(0,0,0,$i,1)) }}</option>
                    @endfor
                </select>
            </div>
            </div>

            <button class="mt-6 px-6 py-2 bg-blue-600 text-white rounded-lg">
                Save & Lock Attendance Rules
            </button>
        </form>
    @endif
</div>
@endsection
