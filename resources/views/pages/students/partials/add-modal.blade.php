<div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50">
    <div class="bg-white w-1/2 mx-auto mt-20 p-6 rounded">
        <h3 class="text-lg font-semibold mb-4">Add Student</h3>

        <form method="POST" action="{{ route('students.store') }}">
            @csrf

            <input class="border p-2 w-full mb-2" name="name" placeholder="Student Name" required>
            <input class="border p-2 w-full mb-2" name="control_number" placeholder="Control Number" required>
            <input class="border p-2 w-full mb-2" name="mobile" placeholder="Mobile" required>

            <select name="course_id" class="border p-2 w-full mb-2" required>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}">{{ $course->name }}</option>
                @endforeach
            </select>

            <select name="department_id" class="border p-2 w-full mb-2" required>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>

            <input class="border p-2 w-full mb-2" name="current_semester" placeholder="Semester" required>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeAddModal()">Cancel</button>
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
            </div>
        </form>
    </div>
</div>
