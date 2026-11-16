@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <a href="{{ route('teacher.apar') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-700 hover:underline">
            <span aria-hidden="true">←</span> Back to my APARs
        </a>

        <header class="rounded-3xl bg-gradient-to-br from-indigo-700 via-indigo-600 to-violet-600 p-7 text-white shadow-xl sm:p-9">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-100">Faculty services</p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight">Create a new APAR</h1>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-indigo-100">Choose the academic year for this report. Your draft will be available from My APAR submissions whenever you want to resume it.</p>
        </header>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
            @if(count($availableYears))
                <form method="POST" action="{{ route('teacher.apar.create.select') }}" class="space-y-5">
                    @csrf
                    <div>
                        <label for="academic-year" class="mb-1.5 block text-sm font-semibold text-slate-700">Academic year</label>
                        <select id="academic-year" name="academic_year" required class="w-full rounded-xl border-slate-300 px-3.5 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="" disabled @selected(!in_array($currentAcademicYear, $availableYears, true))>Select an academic year</option>
                            @foreach($availableYears as $year)
                                <option value="{{ $year }}" @selected($year === $currentAcademicYear)>{{ $year }}{{ $year === $currentAcademicYear ? ' · Current year' : '' }}</option>
                            @endforeach
                        </select>
                        @error('academic_year')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button class="rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Continue to APAR form</button>
                        <a href="{{ route('teacher.apar') }}" class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancel</a>
                    </div>
                </form>
            @else
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-900">
                    An APAR already exists for every available academic year. You can resume an existing draft or view a published report from your submissions.
                </div>
                <a href="{{ route('teacher.apar') }}" class="mt-4 inline-flex rounded-xl bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700">View my APARs</a>
            @endif
        </section>
    </div>
@endsection
