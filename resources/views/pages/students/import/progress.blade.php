@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12">

    <div class="bg-white shadow rounded-xl p-8 text-center">

        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            Importing Students
        </h1>

        <p class="text-gray-500 mb-6">
            Please wait while we import student records.
            Do not refresh the page.
        </p>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-full h-5 overflow-hidden mb-4">
            <div id="progressBar"
                 class="bg-indigo-600 h-5 rounded-full transition-all duration-500"
                 style="width: 0%">
            </div>
        </div>

        <p id="progressText"
           class="text-sm font-medium text-gray-700">
            0% Completed
        </p>

        <!-- Success Message -->
        <div id="successBox"
             class="hidden mt-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg">
            ✅ Import completed successfully!
        </div>

        <!-- Action Buttons -->
        <div id="actionButtons"
             class="hidden mt-6 flex justify-center gap-4">
            <a href="{{ route('students.index') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                Import More
            </a>

            <a href="{{ route('dashboard') }}"
               class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">
                Go to Dashboard
            </a>
        </div>

    </div>

</div>

<!-- Progress Script -->
<script>
    let interval = setInterval(() => {
        fetch("{{ route('students.import.progress.status') }}")
            .then(res => res.json())
            .then(data => {

                let progress = data.progress ?? 0;

                document.getElementById('progressBar').style.width = progress + '%';
                document.getElementById('progressText').innerText =
                    progress + '% Completed';

                if (progress >= 100) {
                    clearInterval(interval);

                    document.getElementById('successBox').classList.remove('hidden');
                    document.getElementById('actionButtons').classList.remove('hidden');
                }
            })
            .catch(err => console.error('Progress error:', err));
    }, 2000);
</script>

@endsection
