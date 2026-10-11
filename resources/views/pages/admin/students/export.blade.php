@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 p-4 sm:p-6 lg:p-8">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase text-emerald-700">Student records</p>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Student data export</h1>
            <p class="mt-1 text-sm text-gray-600">Export student details, academic records and current semester papers.</p>
        </div>
        <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
            <span class="font-semibold">{{ number_format($matchedStudents) }}</span> students match these filters
        </div>
    </header>

    <section class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <form method="GET" action="{{ route('admin.students.export.index') }}" id="exportFilters" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-7">
            <div>
                <label for="department_id" class="mb-1 block text-sm font-semibold text-gray-700">Department</label>
                <select id="department_id" name="department_id" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="course_id" class="mb-1 block text-sm font-semibold text-gray-700">Course</label>
                <select id="course_id" name="course_id" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                    <option value="">All courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" data-department="{{ $course->dept_id }}" @selected(request('course_id') == $course->id)>{{ $course->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="semester" class="mb-1 block text-sm font-semibold text-gray-700">Current semester</label>
                <select id="semester" name="semester" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                    <option value="">All semesters</option>
                    @for($semester = 1; $semester <= 8; $semester++)
                        <option value="{{ $semester }}" @selected(request('semester') == $semester)>Semester {{ $semester }}</option>
                    @endfor
                </select>
            </div>
            <div>
                <label for="section" class="mb-1 block text-sm font-semibold text-gray-700">Section</label>
                <select id="section" name="section" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                    <option value="">All sections</option>
                    @foreach($sections as $section)
                        <option value="{{ $section }}" @selected(request('section') === $section)>{{ $section }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="student_status" class="mb-1 block text-sm font-semibold text-gray-700">Student status</label>
                <select id="student_status" name="student_status" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
                    <option value="all" @selected(request('student_status', 'all') === 'all')>All students</option>
                    <option value="active" @selected(request('student_status') === 'active')>Current students</option>
                    <option value="exited" @selected(request('student_status') === 'exited')>Old students</option>
                </select>
            </div>
            <div>
                <label for="search" class="mb-1 block text-sm font-semibold text-gray-700">Search students</label>
                <input id="search" name="search" value="{{ request('search') }}" type="search" placeholder="Name, roll or email" class="w-full rounded-md border-gray-300 text-sm focus:border-emerald-700 focus:ring-emerald-700">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="min-h-10 flex-1 rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Apply</button>
                <a href="{{ route('admin.students.export.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </section>

    <section class="flex flex-col justify-between gap-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:p-6">
        <div>
            <h2 class="font-semibold text-gray-900">Ready to export</h2>
            <p class="mt-1 text-sm text-gray-600">The spreadsheet will contain one row per student and their registered papers for the current semester.</p>
        </div>
        <button id="startExport" type="button" @disabled($matchedStudents === 0) class="inline-flex min-h-11 items-center justify-center gap-2 rounded-md bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-gray-300">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg>
            Export {{ number_format($matchedStudents) }} students
        </button>
    </section>

    <section id="exportProgress" class="hidden rounded-lg border border-gray-200 bg-white p-5 shadow-sm sm:p-6" aria-live="polite">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 id="progressTitle" class="font-semibold text-gray-900">Preparing export</h2>
            <span id="progressLabel" class="text-sm font-semibold text-emerald-800">0%</span>
        </div>
        <div class="mt-4 h-2.5 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-label="Export progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
            <div id="progressBar" class="h-full w-0 rounded-full bg-emerald-600 transition-all duration-300"></div>
        </div>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-sm text-gray-600">
            <p id="progressMessage">Waiting for the export worker...</p>
            <a id="downloadExport" href="#" class="hidden rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800">Download spreadsheet</a>
        </div>
    </section>
</div>

<script>
    (() => {
        const form = document.getElementById('exportFilters');
        const department = document.getElementById('department_id');
        const course = document.getElementById('course_id');
        const startButton = document.getElementById('startExport');
        const progressSection = document.getElementById('exportProgress');
        const progressTitle = document.getElementById('progressTitle');
        const progressLabel = document.getElementById('progressLabel');
        const progressMessage = document.getElementById('progressMessage');
        const progressBar = document.getElementById('progressBar');
        const progressTrack = progressSection.querySelector('[role="progressbar"]');
        const downloadLink = document.getElementById('downloadExport');
        let pollTimer;

        function filterCourses() {
            const selectedDepartment = department.value;
            for (const option of course.options) {
                if (!option.value) continue;
                option.hidden = Boolean(selectedDepartment && option.dataset.department !== selectedDepartment);
                if (option.hidden && option.selected) course.value = '';
            }
        }

        department.addEventListener('change', filterCourses);
        filterCourses();

        async function poll(statusUrl) {
            try {
                const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                if (!response.ok) throw new Error('Could not retrieve export status.');
                const data = await response.json();
                const progress = Number(data.progress || 0);

                progressBar.style.width = `${progress}%`;
                progressLabel.textContent = `${progress}%`;
                progressTrack.setAttribute('aria-valuenow', progress);

                if (data.status === 'completed') {
                    progressTitle.textContent = 'Export complete';
                    progressMessage.textContent = `${Number(data.total).toLocaleString()} students exported.`;
                    downloadLink.href = data.download_url;
                    downloadLink.classList.remove('hidden');
                    startButton.disabled = false;
                    return;
                }

                if (data.status === 'failed') {
                    progressTitle.textContent = 'Export could not be completed';
                    progressMessage.textContent = 'Please try again. If the issue continues, contact the system administrator.';
                    startButton.disabled = false;
                    return;
                }

                progressTitle.textContent = data.status === 'queued' ? 'Export queued' : 'Building spreadsheet';
                progressMessage.textContent = `${Number(data.processed).toLocaleString()} of ${Number(data.total).toLocaleString()} students processed`;
                pollTimer = window.setTimeout(() => poll(statusUrl), 1000);
            } catch (error) {
                progressTitle.textContent = 'Unable to check progress';
                progressMessage.textContent = error.message;
                startButton.disabled = false;
            }
        }

        startButton.addEventListener('click', async () => {
            startButton.disabled = true;
            downloadLink.classList.add('hidden');
            progressSection.classList.remove('hidden');
            progressTitle.textContent = 'Starting export';
            progressMessage.textContent = 'Sending your filtered request...';
            progressBar.style.width = '0%';
            progressLabel.textContent = '0%';
            progressTrack.setAttribute('aria-valuenow', 0);

            try {
                const response = await fetch(@json(route('admin.students.export.start')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: new FormData(form),
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Could not start the export.');
                poll(data.status_url);
            } catch (error) {
                progressTitle.textContent = 'Export could not be started';
                progressMessage.textContent = error.message;
                startButton.disabled = false;
            }
        });

        window.addEventListener('beforeunload', () => window.clearTimeout(pollTimer));
    })();
</script>
@endsection