@extends('layouts.app')

@section('content')
<div class="p-6 space-y-4">

    <h2 class="text-xl font-bold">Attendance Master Report</h2>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <input type="month"
               value="{{ $year }}-{{ str_pad($month,2,'0',STR_PAD_LEFT) }}"
               onchange="let v=this.value.split('-');location.href='?month='+v[1]+'&year='+v[0];"
               class="border rounded px-3 py-2">

        <input id="search"
               placeholder="Search student or roll no"
               class="border rounded px-3 py-2 w-64">
    </div>
    <a href="/admin/student-attendance-master/excel/{{ $month }}/{{ $year }}" class="mt-4 md:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg shadow hover:bg-indigo-700">
            ⬇ Download Excel Template
    </a>
    {{-- LIST --}}
    <div class="space-y-3">
        @foreach($students as $s)
            <div class="bg-white rounded-xl shadow p-4 student-card">

                <div class="grid grid-cols-9 gap-2 items-center cursor-pointer"
                     onclick="toggle({{ $s->student_id }})">

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
                        <div class="text-sm font-medium">Lecture</div>
                        <div class="text-green-600">{{ round($s->lecture_avg,2) ?? 0 }}%</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium">Tutorial</div>
                        <div class="text-yellow-600">{{ round($s->tutorial_avg,2) ?? 0 }}%</div>
                    </div>

                    <div>
                        <div class="text-sm font-medium">Practical</div>
                        <div class="text-blue-600">{{ round($s->practical_avg,2) ?? 0 }}%</div>
                    </div>

                    <div class="text-indigo-600 font-semibold text-sm">
                        View Papers ▾
                    </div>
                </div>

                {{-- PAPERS --}}
                <div id="papers-{{ $s->student_id }}" class="hidden mt-4 border-t pt-4">
                    <table class="w-full text-sm">
                        <thead class="text-gray-500">
                        <tr>
                            <th class="text-left">Paper</th>
                            <th>Lecture %</th>
                            <th>Tutorial %</th>
                            <th>Practical %</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($papers[$s->student_id] ?? [] as $p)
                            <tr class="border-t">
                                <td class="py-2 font-medium">{{ $p->paper_name }}</td>
                                <td class="text-center">
                                    {{ $p->lecture_working_days > 0 ? round(($p->lecture_present_days/$p->lecture_working_days)*100,2).'%' : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $p->tute_working_days > 0 ? round(($p->tute_present_days/$p->tute_working_days)*100,2).'%' : '-' }}
                                </td>
                                <td class="text-center">
                                    {{ $p->practical_working_days > 0 ? round(($p->practical_present_days/$p->practical_working_days)*100,2).'%' : '-' }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

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
@endsection
