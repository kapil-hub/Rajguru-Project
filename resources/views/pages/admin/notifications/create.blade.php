@extends('layouts.app')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Add Notification</h1>
        <p class="text-sm text-gray-500">Choose who should see this notification among active students.</p>
    </div>

    <form method="POST" action="{{ route('admin.notifications.store') }}" enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow-md p-6 border-l-8 border-indigo-600 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notification File</label>
            <input type="file" name="file" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
        </div>

        <div class="flex items-center gap-3 p-4 rounded-lg bg-gray-50">
            <input type="checkbox" name="target_all_active_students" value="1" id="targetAll"
                   class="h-4 w-4" {{ old('target_all_active_students') ? 'checked' : '' }}>
            <label for="targetAll" class="text-sm font-medium text-gray-700">Show to all active students</label>
        </div>

        <div id="targetFields" class="grid grid-cols-1 lg:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Departments</label>
                <select name="department_ids[]" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 min-h-40">
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(in_array($department->id, old('department_ids', [])))>
                            {{ $department->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Courses</label>
                <select name="course_ids[]" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 min-h-40">
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" @selected(in_array($course->id, old('course_ids', [])))>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Papers</label>
                <select name="paper_ids[]" multiple class="w-full border border-gray-300 rounded-lg px-3 py-2 min-h-40">
                    @foreach($papers as $paper)
                        <option value="{{ $paper->id }}" @selected(in_array($paper->id, old('paper_ids', [])))>
                            {{ $paper->name }} ({{ $paper->code }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <input type="checkbox" name="is_active" value="1" id="isActive" class="h-4 w-4" {{ old('is_active', '1') ? 'checked' : '' }}>
            <label for="isActive" class="text-sm font-medium text-gray-700">Active</label>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.notifications.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg">Cancel</a>
            <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow">Save Notification</button>
        </div>
    </form>
</div>

<script>
    const targetAll = document.getElementById('targetAll');
    const targetFields = document.getElementById('targetFields');

    function toggleTargetFields() {
        targetFields.classList.toggle('opacity-40', targetAll.checked);
        targetFields.querySelectorAll('select').forEach((select) => {
            select.disabled = targetAll.checked;
        });
    }

    targetAll.addEventListener('change', toggleTargetFields);
    toggleTargetFields();
</script>
@endsection
