@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl space-y-6 p-4 sm:p-6 lg:p-8">
    <header class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-wide text-indigo-700">Notifications</p>
            <h1 class="mt-2 text-2xl font-bold text-gray-900">Create notification</h1>
            <p class="mt-1 text-sm text-gray-600">Add the notice, choose its audience, and set its status.</p>
        </div>
        <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-2 self-start rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 sm:self-auto">
            <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
            All notifications
        </a>
    </header>

    @if($errors->any())
        <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Please review the following:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.notifications.store') }}" enctype="multipart/form-data"
          class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @csrf

        <section class="space-y-5 border-b border-gray-200 p-5 sm:p-7" aria-labelledby="noticeDetailsTitle">
            <div>
                <p class="text-xs font-bold uppercase text-gray-500">01 · Content</p>
                <h2 id="noticeDetailsTitle" class="mt-1 text-lg font-bold text-gray-900">Notice details</h2>
            </div>

            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label for="title" class="mb-1.5 block text-sm font-semibold text-gray-700">Title <span class="text-red-600">*</span></label>
                    <input id="title" type="text" name="title" value="{{ old('title') }}" required maxlength="255" placeholder="e.g. Semester examination schedule"
                           class="w-full rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-600 focus:ring-indigo-600">
                </div>

                <div class="md:col-span-2">
                    <label for="description" class="mb-1.5 block text-sm font-semibold text-gray-700">Description <span class="font-normal text-gray-400">Optional</span></label>
                    <textarea id="description" name="description" rows="3" placeholder="Add a short note for students"
                              class="w-full resize-y rounded-lg border-gray-300 px-3.5 py-2.5 text-sm shadow-sm focus:border-indigo-600 focus:ring-indigo-600">{{ old('description') }}</textarea>
                </div>

                <div class="md:col-span-2">
                    <label for="notificationFile" class="mb-1.5 block text-sm font-semibold text-gray-700">Attachment <span class="text-red-600">*</span></label>
                    <input id="notificationFile" type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                           class="block w-full cursor-pointer rounded-lg border border-gray-300 bg-gray-50 text-sm text-gray-700 file:mr-4 file:border-0 file:bg-indigo-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                    <p class="mt-1.5 text-xs text-gray-500">PDF, Word document, or image · Maximum 10 MB</p>
                </div>
            </div>
        </section>

        <section class="space-y-5 border-b border-gray-200 p-5 sm:p-7" aria-labelledby="audienceTitle">
            <div>
                <p class="text-xs font-bold uppercase text-gray-500">02 · Recipients</p>
                <h2 id="audienceTitle" class="mt-1 text-lg font-bold text-gray-900">Choose audience</h2>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2" role="group" aria-label="Notification audience">
                <label id="allStudentsChoice" class="audience-choice flex cursor-pointer items-start gap-3 rounded-lg border border-gray-200 p-4 transition hover:border-indigo-300 hover:bg-indigo-50/40">
                    <input type="checkbox" name="target_all_active_students" value="1" id="targetAll"
                           class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" {{ old('target_all_active_students') ? 'checked' : '' }}>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">All active students</span>
                        <span class="mt-1 block text-xs text-gray-500">Send to every currently enrolled student.</span>
                    </span>
                </label>

                <button id="specificStudentsChoice" type="button" aria-pressed="{{ old('target_all_active_students') ? 'false' : 'true' }}"
                        class="audience-choice flex items-start gap-3 rounded-lg border border-gray-200 p-4 text-left transition hover:border-indigo-300 hover:bg-indigo-50/40">
                    <span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full border border-gray-400" aria-hidden="true">
                        <span class="audience-radio-dot h-2 w-2 rounded-full bg-transparent"></span>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold text-gray-900">Selected groups</span>
                        <span class="mt-1 block text-xs text-gray-500">Choose departments, courses, papers, or semesters.</span>
                    </span>
                </button>
            </div>

            <div id="targetFields" class="space-y-5 border-t border-gray-100 pt-5">
                <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
                    <div class="min-w-0">
                        <label for="departmentTargets" class="mb-1.5 block text-sm font-semibold text-gray-700">Departments</label>
                        <select id="departmentTargets" name="department_ids[]" multiple class="notification-target-select w-full">
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected(in_array($department->id, old('department_ids', [])))}>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-0">
                        <label for="courseTargets" class="mb-1.5 block text-sm font-semibold text-gray-700">Courses</label>
                        <select id="courseTargets" name="course_ids[]" multiple class="notification-target-select w-full">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" @selected(in_array($course->id, old('course_ids', [])))}>
                                    {{ $course->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="min-w-0">
                        <label for="paperTargets" class="mb-1.5 block text-sm font-semibold text-gray-700">Papers</label>
                        <select id="paperTargets" name="paper_ids[]" multiple class="notification-target-select w-full">
                            @foreach($papers as $paper)
                                <option value="{{ $paper->id }}" @selected(in_array($paper->id, old('paper_ids', [])))}>
                                    {{ $paper->name }} ({{ $paper->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <fieldset>
                    <legend class="mb-2 block text-sm font-semibold text-gray-700">Semesters</legend>
                    <div class="grid grid-cols-2 gap-2 sm:grid-cols-4 lg:grid-cols-8">
                        @for($semester = 1; $semester <= 8; $semester++)
                            <label class="semester-choice cursor-pointer">
                                <input type="checkbox" name="semester_ids[]" value="{{ $semester }}"
                                       class="peer sr-only" @checked(in_array($semester, old('semester_ids', [])))>
                                <span class="flex min-h-10 items-center justify-center rounded-lg border border-gray-200 px-2 text-sm font-medium text-gray-600 transition hover:border-indigo-300 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 peer-checked:text-indigo-800 peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-indigo-600">
                                    Semester {{ $semester }}
                                </span>
                            </label>
                        @endfor
                    </div>
                </fieldset>
            </div>
        </section>

        <section class="flex flex-col justify-between gap-4 bg-gray-50/70 p-5 sm:flex-row sm:items-center sm:px-7" aria-labelledby="deliveryTitle">
            <div class="flex items-start gap-3">
                <input type="checkbox" name="is_active" value="1" id="isActive" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600" {{ old('is_active', '1') ? 'checked' : '' }}>
                <div>
                    <label for="isActive" id="deliveryTitle" class="block text-sm font-semibold text-gray-900">Publish notification</label>
                    <p class="mt-0.5 text-xs text-gray-500">Inactive notifications are saved but hidden from students.</p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row">
                <a href="{{ route('admin.notifications.index') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex min-h-10 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2">
                    <svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                    Save notification
                </button>
            </div>
        </section>
    </form>
</div>

<style>
    #targetFields .ts-control {
        min-height: 44px;
        padding: 7px 10px;
        border-color: #d1d5db;
        border-radius: 8px;
        box-shadow: none;
        font-size: 13px;
    }

    #targetFields .ts-wrapper.focus .ts-control {
        border-color: #4f46e5;
        box-shadow: 0 0 0 2px rgb(79 70 229 / 15%);
    }

    #targetFields .ts-dropdown {
        border-color: #d1d5db;
        border-radius: 8px;
        font-size: 13px;
    }

    #targetFields .ts-control .item {
        border-radius: 5px;
        background: #eef2ff;
        color: #3730a3;
    }

    .audience-choice.is-selected {
        border-color: #6366f1;
        background: #eef2ff;
        box-shadow: 0 0 0 1px #6366f1;
    }

    .audience-choice.is-selected .audience-radio-dot {
        background: #4f46e5;
    }
</style>

<script>
    (() => {
        const targetAll = document.getElementById('targetAll');
        const targetFields = document.getElementById('targetFields');
        const allChoice = document.getElementById('allStudentsChoice');
        const specificChoice = document.getElementById('specificStudentsChoice');
        const targetSelects = Array.from(document.querySelectorAll('.notification-target-select'));
        const selectInstances = targetSelects.map((select) => new TomSelect(select, {
            plugins: ['remove_button'],
            placeholder: 'Search and select...',
            allowEmptyOption: true,
            create: false,
            maxOptions: null,
            closeAfterSelect: false,
            hideSelected: true,
        }));

        function updateAudienceMode() {
            const allActive = targetAll.checked;
            targetFields.hidden = allActive;
            allChoice.classList.toggle('is-selected', allActive);
            specificChoice.classList.toggle('is-selected', !allActive);
            specificChoice.setAttribute('aria-pressed', String(!allActive));

            targetFields.querySelectorAll('input[type="checkbox"]').forEach((input) => {
                input.disabled = allActive;
            });
            selectInstances.forEach((select) => {
                allActive ? select.disable() : select.enable();
            });
        }

        targetAll.addEventListener('change', updateAudienceMode);
        specificChoice.addEventListener('click', () => {
            targetAll.checked = false;
            updateAudienceMode();
        });
        updateAudienceMode();
    })();
</script>
@endsection
