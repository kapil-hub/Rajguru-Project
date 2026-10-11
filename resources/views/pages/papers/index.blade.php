@extends('layouts.app')

@section('content')
    <div class="max-w-8xl mx-auto rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Papers Management</h2>
                <p class="text-sm text-gray-500">
                    Manage papers, import via Excel or add manually
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('papers.create') }}"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    ➕ Add Paper
                </a>

                <a href="{{ route('papers.template') }}"
                    class="px-4 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition">
                    ⬇ Download Template
                </a>
            </div>
        </div>


        <!-- IMPORT BOX (RESTORED ✅) -->
        <div class="bg-white shadow rounded-xl p-5 mb-6">
            <form action="{{ route('papers.import') }}" method="POST" enctype="multipart/form-data"
                class="flex flex-col md:flex-row md:items-end gap-4">
                @csrf

                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Import Papers (Excel)
                    </label>
                    <input type="file" name="file" required class="w-full border rounded-lg px-3 py-2
                                      focus:ring-2 focus:ring-indigo-500 focus:outline-none">
                </div>

                <button type="submit" class="px-6 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
                    📤 Upload
                </button>
            </form>
        </div>

        <section class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <form method="GET" action="{{ route('papers.index') }}" id="paperFilters" class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-7">
                <div class="xl:col-span-2">
                    <label for="paperSearch" class="mb-1 block text-xs font-semibold text-gray-600">Search papers</label>
                    <input id="paperSearch" type="search" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Name, code, department, course" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                </div>

                <div>
                    <label for="departmentFilter" class="mb-1 block text-xs font-semibold text-gray-600">Department</label>
                    <select id="departmentFilter" name="department_id" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">All departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected(($filters['department_id'] ?? '') == $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="courseFilter" class="mb-1 block text-xs font-semibold text-gray-600">Course</label>
                    <select id="courseFilter" name="course_id" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">All courses</option>
                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" data-department="{{ $course->dept_id }}" @selected(($filters['course_id'] ?? '') == $course->id)>{{ $course->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="semesterFilter" class="mb-1 block text-xs font-semibold text-gray-600">Semester</label>
                    <select id="semesterFilter" name="semester" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">Any semester</option>
                        @foreach($semesters as $semester)
                            <option value="{{ $semester }}" @selected(($filters['semester'] ?? '') == $semester)>Semester {{ $semester }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="semesterParityFilter" class="mb-1 block text-xs font-semibold text-gray-600">Semester group</label>
                    <select id="semesterParityFilter" name="semester_filter" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">All</option>
                        <option value="odd" @selected(($filters['semester_filter'] ?? '') === 'odd')>Odd semesters</option>
                        <option value="even" @selected(($filters['semester_filter'] ?? '') === 'even')>Even semesters</option>
                    </select>
                </div>

                <div>
                    <label for="paperTypeFilter" class="mb-1 block text-xs font-semibold text-gray-600">Paper type</label>
                    <select id="paperTypeFilter" name="paper_type" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">All types</option>
                        @foreach($paperTypes as $type)
                            <option value="{{ $type }}" @selected(($filters['paper_type'] ?? '') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="paperStatusFilter" class="mb-1 block text-xs font-semibold text-gray-600">Status</label>
                    <select id="paperStatusFilter" name="status" class="w-full rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-600 focus:ring-indigo-600">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $status }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-2 sm:col-span-2 lg:col-span-4 xl:col-span-7">
                    <button type="submit" class="inline-flex min-h-10 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Apply filters</button>
                    <a href="{{ route('papers.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Reset</a>
                </div>
            </form>
        </section>

        <section class="mb-5 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h3 class="font-semibold text-gray-900">Export paper details</h3>
                    <p class="mt-1 text-sm text-gray-500">Export all fields for the {{ number_format($matchedPapers) }} papers matching your filters.</p>
                </div>
                <button id="startPaperExport" type="button" @disabled($matchedPapers === 0) class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:bg-gray-300">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v3h16v-3"/></svg>
                    Export {{ number_format($matchedPapers) }} papers
                </button>
            </div>

            <div id="paperExportProgress" class="mt-5 hidden border-t border-gray-100 pt-4" aria-live="polite">
                <div class="flex items-center justify-between gap-3">
                    <p id="paperExportTitle" class="text-sm font-semibold text-gray-800">Preparing export</p>
                    <span id="paperExportPercent" class="text-sm font-semibold text-emerald-800">0%</span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-gray-100" role="progressbar" aria-label="Paper export progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                    <div id="paperExportBar" class="h-full w-0 rounded-full bg-emerald-600 transition-all duration-300"></div>
                </div>
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                    <p id="paperExportMessage" class="text-sm text-gray-500">Waiting for the export worker...</p>
                    <a id="paperExportDownload" href="#" class="hidden rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Download spreadsheet</a>
                </div>
            </div>
        </section>

        <!-- SUMMARY -->
        <div class="text-sm text-gray-600 mb-2">
            Showing {{ $papers->firstItem() ?? 0 }} – {{ $papers->lastItem() ?? 0 }}
            of {{ $papers->total() }} papers
        </div>

        <!-- TABLE -->
        <div class="bg-white shadow rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse">
                    <thead class="bg-gray-50 sticky top-0 z-10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Department</th> 
                            <th class="px-4 py-3 text-left text-xs font-semibold">Course</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Semester</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Paper Code</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Paper Name</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Paper Type</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Credit Of Lectures</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Credit Of Tutorials</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Credit Of Practicals</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold">Number Of Students</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($papers as $paper) 
                                        <tr class="hover:bg-gray-50 transition">
                                            <td class="px-4 py-3">{{ $paper->department->name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $paper->course->name ?? '-' }}</td>
                                            <td class="px-4 py-3">{{ $paper->semester }}</td>
                                            <td class="px-4 py-3 font-medium">{{ $paper->code }}</td>
                                            <td class="px-4 py-3">{{ $paper->name }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                                    {{ $paper->paper_type }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-1 text-xs rounded-full
                                                                                                    {{ $paper->status === 'Active'
                            ? 'bg-green-100 text-green-700'
                            : 'bg-red-100 text-red-700' }}">
                                                    {{ $paper->status }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">{{ $paper->number_of_lectures }}</td>
                                            <td class="px-4 py-3 font-medium">{{ $paper->number_of_tutorials }}</td>
                                            <td class="px-4 py-3">{{ $paper->number_of_practicals }}</td>
                                            <td class="px-4 py-3 text-center font-medium">{{ $paper->students_count }}</td>
                                            <td class="px-4 py-3 text-center space-x-1 whitespace-nowrap">
                                                <a href="/teacher/marksBreakup/{{ $paper->id }}"
                                                    class="inline-flex items-center px-2.5 py-1 bg-amber-500 hover:bg-amber-600 text-white rounded text-xs font-semibold transition">Breakup</a>
                                                <a href="/paper/edit/{{ $paper->id }}"
                                                    class="inline-flex items-center px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-xs font-semibold transition">Edit</a>
                                                <a href="{{ route('papers.batches', $paper->id) }}"
                                                    class="inline-flex items-center px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-xs font-semibold transition">Batches</a>
                                            </td>
                                        </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-4 py-6 text-center text-gray-500">
                                    No papers found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($papers->hasPages())
                <div class="p-4 border-t">
                    {{ $papers->links() }}
                </div>
            @endif
        </div>

    </div>

    <script>
        (() => {
            const filters = document.getElementById('paperFilters');
            const department = document.getElementById('departmentFilter');
            const course = document.getElementById('courseFilter');
            const semester = document.getElementById('semesterFilter');
            const semesterGroup = document.getElementById('semesterParityFilter');
            const startButton = document.getElementById('startPaperExport');
            const progressPanel = document.getElementById('paperExportProgress');
            const progressTitle = document.getElementById('paperExportTitle');
            const progressMessage = document.getElementById('paperExportMessage');
            const progressPercent = document.getElementById('paperExportPercent');
            const progressBar = document.getElementById('paperExportBar');
            const progressTrack = progressPanel.querySelector('[role="progressbar"]');
            const downloadLink = document.getElementById('paperExportDownload');
            let pollTimer;

            function filterCourses() {
                for (const option of course.options) {
                    if (!option.value) continue;
                    option.hidden = Boolean(department.value && option.dataset.department !== department.value);
                    if (option.hidden && option.selected) course.value = '';
                }
            }

            department.addEventListener('change', filterCourses);
            semester.addEventListener('change', () => {
                if (semester.value) semesterGroup.value = '';
            });
            semesterGroup.addEventListener('change', () => {
                if (semesterGroup.value) semester.value = '';
            });
            filterCourses();

            async function poll(statusUrl) {
                try {
                    const response = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) throw new Error('Could not retrieve export progress.');
                    const data = await response.json();
                    const progress = Number(data.progress || 0);

                    progressBar.style.width = `${progress}%`;
                    progressPercent.textContent = `${progress}%`;
                    progressTrack.setAttribute('aria-valuenow', progress);

                    if (data.status === 'completed') {
                        progressTitle.textContent = 'Export ready';
                        progressMessage.textContent = `${Number(data.total).toLocaleString()} papers exported.`;
                        downloadLink.href = data.download_url;
                        downloadLink.classList.remove('hidden');
                        startButton.disabled = false;
                        return;
                    }

                    if (data.status === 'failed') {
                        progressTitle.textContent = 'Export failed';
                        progressMessage.textContent = 'Please try again. If the problem continues, contact the system administrator.';
                        startButton.disabled = false;
                        return;
                    }

                    progressTitle.textContent = data.status === 'queued' ? 'Export queued' : 'Building spreadsheet';
                    progressMessage.textContent = `${Number(data.processed).toLocaleString()} of ${Number(data.total).toLocaleString()} papers processed.`;
                    pollTimer = window.setTimeout(() => poll(statusUrl), 1000);
                } catch (error) {
                    progressTitle.textContent = 'Could not check export progress';
                    progressMessage.textContent = error.message;
                    startButton.disabled = false;
                }
            }

            startButton.addEventListener('click', async () => {
                startButton.disabled = true;
                downloadLink.classList.add('hidden');
                progressPanel.classList.remove('hidden');
                progressTitle.textContent = 'Starting export';
                progressMessage.textContent = 'Sending the selected filters...';
                progressPercent.textContent = '0%';
                progressBar.style.width = '0%';
                progressTrack.setAttribute('aria-valuenow', 0);

                try {
                    const response = await fetch(@json(route('papers.export.start')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: new FormData(filters),
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