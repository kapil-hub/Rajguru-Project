@extends('layouts.app')

@section('content')
<div class="p-6 space-y-4">

    <h2 class="text-xl font-bold">Attendance Master Report</h2>

    {{-- Filters --}}
        {{-- MODERN FILTER CARD --}}
<div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-100">

    <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">

        {{-- Month --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Month</label>
            <input type="month"
                   name="month_year"
                   value="{{ $year }}-{{ str_pad($month,2,'0',STR_PAD_LEFT) }}"
                   class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 px-3 py-2">
        </div>

        {{-- Department --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Department</label>
            <select name="department_id"
                    id="department"
                    class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-purple-500 px-3 py-2">
                <option value="">All</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}"
                        {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Course --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Course</label>
            <select name="course_id"
                    id="course"
                    class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-pink-500 px-3 py-2">
                <option value="">All</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}"
                        {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Semester --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Semester</label>
            <select name="semester"
                    class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-green-500 px-3 py-2">
                <option value="">All</option>
                @for($i=1;$i<=8;$i++)
                    <option value="{{ $i }}"
                        {{ request('semester') == $i ? 'selected' : '' }}>
                        {{ $i }}
                    </option>
                @endfor
            </select>
        </div>

        {{-- Search --}}
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1">Search</label>
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Name or Roll No"
                   class="w-full rounded-xl border-gray-300 focus:ring-2 focus:ring-blue-500 px-3 py-2">
        </div>

        {{-- Buttons --}}
        <div class="flex gap-2">

            <button 
                class="flex-1 bg-blue-600 hover:bg-blue-700 
                    text-white font-semibold 
                    rounded-xl px-4 py-2 
                    shadow-md transition duration-200">
                Apply
            </button>

            <a href="{{ route('admin.attendance.master') }}"
               class="flex-1 bg-gray-200 text-gray-700 rounded-xl px-4 py-2 
                      text-center hover:bg-gray-300 transition">
                Reset
            </a>

        </div>

    </form>
</div>

    <button onclick="generateExcel()" 
        class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
        ⬇ Generate Excel
    </button>
    {{-- LIST --}}
    <div class="space-y-3">
        @foreach($students as $s)
            <div class="bg-white rounded-xl shadow p-4 student-card">

                <div class="grid grid-cols-9 gap-2 items-center cursor-pointer"
                    onclick="Livewire.dispatch('loadBreakup', { id: {{ $s->student_id }} })">

                    <div class="col-span-2">
                        <div class="font-semibold">{{ $s->student_name }}</div>
                        <div class="text-sm text-gray-500">{{ $s->roll_number }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Department</div>
                        <div class="text-green-600">{{ $s->department_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Course</div>
                        <div class="text-green-600">{{ $s->course_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Semester</div>
                        <div class="text-green-600">{{ $s->current_semester ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium">Lecture Avg. %</div>
                        <div class="text-green-600">{{ round($s->lecture_avg,2) ?? 0 }}%</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium">Tutorial Avg. %</div>
                        <div class="text-yellow-600">{{ round($s->tutorial_avg,2) ?? 0 }}%</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium">Practical Avg. %</div>
                        <div class="text-blue-600">{{ round($s->practical_avg,2) ?? 0 }}%</div>
                    </div>

                    <div class="text-indigo-600 font-semibold text-sm">
                        View Papers ▾
                    </div>
                </div>

                <livewire:admin.studentattendance-breakup 
                    :student="$s"
                    :key="'student-'.$s->student_id"
                />

            </div>
        @endforeach
    </div>

    {{-- PAGINATION --}}
    <div class="mt-6">
        {{ $students->withQueryString()->links() }}
    </div>

</div>

<script>
    function toggle(id) {
        document.getElementById('papers-' + id).classList.toggle('hidden');
    }

    document.getElementById('search').addEventListener('keyup', e => {
        let v = e.target.value.toLowerCase();
        document.querySelectorAll('.student-card').forEach(card => {
            card.style.display = card.innerText.toLowerCase().includes(v) ? '' : 'none';
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function generateExcel() {

    Swal.fire({
        title: 'Generating File...',
        text: 'Please do not refresh your page. We will notify you once ready.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading()
        }
    });

    fetch("{{ route('admin.attendance.generate') }}", {
        method: "POST",
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            month: "{{ $month }}",
            year: "{{ $year }}"
        })
    });

    checkStatus();
}

function checkStatus() {

    let interval = setInterval(() => {

        fetch("{{ route('admin.attendance.check') }}")
            .then(res => res.json())
            .then(data => {

                if (data.ready) {

                    clearInterval(interval);

                    Swal.fire({
                        icon: 'success',
                        title: 'File Ready!',
                        text: 'Click below to download',
                        confirmButtonText: 'Download'
                    }).then(() => {
                        window.open(data.download_route, '_blank');
                    });
                }
            });

    }, 5000); // check every 5 seconds
}
</script>


@endsection
