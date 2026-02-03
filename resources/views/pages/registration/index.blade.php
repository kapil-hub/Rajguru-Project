@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-600 to-purple-700 py-10 px-4">

    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-2xl p-8">

        <!-- Header -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-gray-800">
                📘 Paper Registration
            </h1>
            <p class="text-gray-500 mt-2">
                Please select <span class="font-semibold">7 papers</span> for the current semester <strong>({{ $student->academic->current_semester }})</strong>
            </p>
        </div>

        <form method="POST"
              action="{{ route('students.registration.store', $student->id) }}">
            @csrf

            <!-- ================= DSC PAPERS ================= -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-indigo-700 mb-4">
                    Discipline Specific Courses (DSC)
                </h2>

                <div class="grid md:grid-cols-3 gap-4">
                    @for ($i = 1; $i <= 3; $i++)
                        <div>
                            <label class="block mb-2 font-medium text-gray-700">
                                DSC_{{ $i }}
                            </label>
                            <select name="papers[DSC_{{ $i }}]"
                                required
                                class="w-full rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 dsc-select">

                                <option value="">Select DSC {{ $i }} </option>
                                @foreach($papers['DSC'] ?? [] as $paper)
                                    <option value="{{ $paper->id }}">
                                        {{ $paper->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endfor
                </div>
            </div>

            <!-- ================= GE ================= -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-purple-700 mb-4">
                    Generic Elective (GE)
                </h2>
                <select name="papers[GE]"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-purple-500 focus:ring-purple-500">
                    <option value=""> Select GE Paper</option>
                    @foreach($papers['GE'] ?? [] as $paper)
                        <option value="{{ $paper->id }}">
                            {{ $paper->name }}
                            ({{ $paper->department->name ?? '' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- ================= AEC ================= -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-green-700 mb-4">
                    Ability Enhancement Course (AEC)
                </h2>
                <select name="papers[AEC]"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-green-500 focus:ring-green-500">
                    <option value="">Select AEC Paper </option>
                    @foreach($papers['AEC'] ?? [] as $paper)
                        <option value="{{ $paper->id }}">
                            {{ $paper->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- ================= SEC ================= -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-orange-700 mb-4">
                    Skill Enhancement Course (SEC)
                </h2>
                <select name="papers[SEC]"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500">
                    <option value="">Select SEC Paper </option>
                    @foreach($papers['SEC'] ?? [] as $paper)
                        <option value="{{ $paper->id }}">
                            {{ $paper->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- ================= VAC ================= -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold text-pink-700 mb-4">
                    Value Added Course (VAC)
                </h2>
                <select name="papers[VAC]"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-pink-500 focus:ring-pink-500">
                    <option value="">Select VAC Paper</option>
                    @foreach($papers['VAC'] ?? [] as $paper)
                        <option value="{{ $paper->id }}">
                            {{ $paper->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- ================= SUBMIT ================= -->
            <div class="text-center mt-10">
                <button type="submit"
                        class="px-12 py-3 text-lg font-semibold text-white bg-indigo-600 rounded-full hover:bg-indigo-700 transition">
                    ✅ Submit Registration
                </button>
            </div>

        </form>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const dscSelects = document.querySelectorAll('.dsc-select');

        function updateDSCOptions() {
            let selectedValues = [];

            dscSelects.forEach(select => {
                if (select.value) {
                    selectedValues.push(select.value);
                }
            });

            dscSelects.forEach(select => {
                const currentValue = select.value;

                Array.from(select.options).forEach(option => {
                    if (
                        option.value !== '' &&
                        option.value !== currentValue &&
                        selectedValues.includes(option.value)
                    ) {
                        option.disabled = true;
                    } else {
                        option.disabled = false;
                    }
                });
            });
        }

        dscSelects.forEach(select => {
            select.addEventListener('change', updateDSCOptions);
        });
    });
</script>

@endsection
