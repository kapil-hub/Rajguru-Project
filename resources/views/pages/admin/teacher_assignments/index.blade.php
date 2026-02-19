@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">

    <!-- ================= HEADER ================= -->
    <div class="flex items-center justify-between mb-8 bg-white p-6 rounded-3xl shadow-md border border-gray-100">
        <div>
            <h1 class="text-3xl font-bold text-indigo-700">
                Teacher Class Assignment
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Manage and monitor teacher assignments
            </p>
        </div>

        <button id="toggleAssignment"
            class="toggle-btn bg-indigo-600 hover:bg-indigo-700
                text-white font-semibold 
                rounded-xl px-6 py-2.5
                shadow-md transition duration-300">
            + New Assignment
        </button>
    </div>



    <!-- ================= ASSIGNMENT FORM ================= -->
<div id="assignmentCard"
     class="bg-white rounded-3xl shadow-xl border border-gray-100 p-8 mb-10"
     style="display:none;">

        <h2 class="text-xl font-semibold text-indigo-700 mb-6">
            Create New Assignment
        </h2>

        <form method="POST"
              action="{{ route('admin.teacher.assignments.store') }}"
              class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4">

                @if(auth('admin')->check())
                <select name="teacher_id" required class="modern-input">
                    <option value="">Select Teacher</option>
                    @foreach($teachers as $teacher)
                        <option value="{{ $teacher->id }}">
                            {{ $teacher->name }}
                        </option>
                    @endforeach
                </select>
                @endif

                <select name="course_id" required class="modern-input">
                    <option value="">Course</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}">
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>

                <select name="academic_session" required class="modern-input">
                    <option value="">Academic Session</option>
                    @php
                        $year = date('Y');
                        $next = $year+1;
                        $prev = $year-1;
                    @endphp
                    <option value="{{ $year.'-'.substr($next,-2) }}">
                        {{ $year.'-'.substr($next,-2) }}
                    </option>
                    <option value="{{ $prev.'-'.substr($year,-2) }}">
                        {{ $prev.'-'.substr($year,-2) }}
                    </option>
                </select>

                <select name="semester_id" required class="modern-input">
                    <option value="">Semester</option>
                    @foreach($semesters as $sem)
                        <option value="{{ $sem->id }}">{{ $sem->name }}</option>
                    @endforeach
                </select>

                <select name="section" required class="modern-input">
                    <option value="">Section</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec }}">{{ $sec }}</option>
                    @endforeach
                </select>

                <select name="paper_master_id" required
                        class="modern-input paper-select">
                    <option value="">Search Paper</option>
                    @foreach($papers as $paper)
                        <option value="{{ $paper->id }}">
                            {{ $paper->code }} - {{ $paper->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Role Buttons -->
            <div>
                <p class="text-sm font-semibold text-gray-700 mb-2">
                    Teacher Role 
                </p>

                <div class="flex flex-wrap gap-4">
                    @foreach([
                        'is_lecture' => 'Lecture',
                        'is_tute' => 'Tutorial',
                        'is_practical' => 'Practical',
                        'is_coordinator' => 'Coordinator'
                    ] as $name => $label)
                        <label
                            class="flex items-center gap-2 px-4 py-2 border rounded-xl cursor-pointer
                                   bg-gray-50 hover:bg-gray-100 transition shadow-sm">
                            <input type="checkbox"
                                   name="{{ $name }}"
                                   class="rounded text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium text-gray-700">
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class=" bg-indigo-700 to-purple-700 
                           hover:from-purple-700 hover:to-indigo-700
                           text-white font-semibold 
                           rounded-xl px-6 py-2.5
                           shadow-lg transition duration-300">
                     + Save Assignment
                </button>
            </div>
        </form>
    </div>



    <!-- ================= FILTER SECTION ================= -->
    <div class="bg-white rounded-3xl shadow-md p-6 mb-6 border border-gray-100">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">

            <input type="text" name="teacher"
                   value="{{ request('teacher') }}"
                   placeholder="Search Teacher"
                   class="modern-input">

            <select name="course" class="modern-input">
                <option value="">All Courses</option>
                @foreach($courses as $course)
                    <option value="{{ $course->id }}"
                        @selected(request('course')==$course->id)>
                        {{ $course->name }}
                    </option>
                @endforeach
            </select>

            <select name="semester" class="modern-input">
                <option value="">All Semester</option>
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}"
                        @selected(request('semester')==$sem->id)>
                        {{ $sem->name }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="modern-input">
                <option value="">All Status</option>
                <option value="1" @selected(request('status')==='1')>Active</option>
                <option value="0" @selected(request('status')==='0')>Inactive</option>
            </select>

            <button class="bg-indigo-600 hover:bg-indigo-700 text-white 
                           rounded-xl px-5 py-2 shadow-md transition">
                Apply
            </button>

            <a href="{{ route('admin.teacher.assignments') }}"
               class="bg-gray-200 hover:bg-gray-300 text-gray-700 
                      rounded-xl px-5 py-2 text-center shadow-sm transition">
                Reset
            </a>
        </form>
    </div>



    <!-- ================= TABLE ================= -->
    <div class="bg-white rounded-3xl shadow-lg overflow-hidden border border-gray-100">
        <table class="min-w-full">
            <thead class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                <tr class="text-xs uppercase tracking-wider">
                    <th class="col-span-2 px-4 py-3 text-left">Teacher</th>
                    <th class="px-4 py-3 text-left">Course</th>
                    <th class="px-4 py-3 text-left">Semester</th>
                    <th class="px-4 py-3 text-left">Section</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-center">Roles</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assignments as $a)
                <tr class="hover:bg-indigo-50 transition">
                    <td class="col-span-2  px-4 py-3 font-medium">
                        {{ $a->teacher->name }}
                    </td>
                    <td class="px-4 py-3">{{ $a->course->name }}</td>
                    <td class="px-4 py-3">{{ $a->semester->name }}</td>
                    <td class="px-4 py-3">{{ $a->section }}</td>
                    <td class="px-4 py-3">{{ $a->paperMaster->name }}</td>
                    <td class="px-4 py-3 text-center space-x-1">
                        @if($a->is_lecture)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-50 text-blue-600 border border-blue-100">Lecture</span>
                        @endif
                        @if($a->is_tute)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-green-50 text-green-600 border border-green-100">Tutorial</span>
                        @endif
                        @if($a->is_practical)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-purple-50 text-purple-600 border border-purple-100">Practical</span>
                        @endif
                        @if($a->is_coordinator)
                            <span class="px-3 py-1 text-xs font-semibold rounded-full bg-orange-50 text-orange-600 border border-orange-100">Coordinator</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($a->is_active)
                            <span class="px-3 py-1 text-xs rounded-full bg-green-50 text-green-600 border border-green-200">
                                Active
                            </span>
                        @else
                            <span class="px-3 py-1 text-xs rounded-full bg-red-50 text-red-600 border border-red-200">
                                Inactive
                            </span>
                        @endif
                    </td>

                    <td class="px-4 py-3 text-center">
                        <form method="POST"
                            action="{{ route('admin.teacher.assignments.status', $a->id) }}">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                class="px-4 py-1.5 text-xs font-semibold rounded-full transition
                                {{ $a->is_active
                                    ? 'bg-red-50 text-red-600 border border-red-200 hover:bg-red-100'
                                    : 'bg-green-50 text-green-600 border border-green-200 hover:bg-green-100' }}">
                                {{ $a->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-6 text-gray-500">
                        No assignments found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4">
            {{ $assignments->withQueryString()->links() }}
        </div>
    </div>

</div>


<style>
.modern-input{
    @apply w-full rounded-xl border-gray-300 bg-gray-50
           shadow-sm px-3 py-2 text-sm
           focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200
           transition;
}
</style>


<script>
document.addEventListener("DOMContentLoaded", function () {

    const toggleBtn = document.getElementById("toggleAssignment");
    const card = document.getElementById("assignmentCard");

    toggleBtn.addEventListener("click", function () {

        if (card.style.display === "none" || card.style.display === "") {

            // OPEN
            slideDown(card, 300);

            toggleBtn.textContent = "✖ Close Assignment";
            toggleBtn.classList.remove("bg-indigo-600", "hover:bg-indigo-700");
            toggleBtn.classList.add("bg-red-600", "hover:bg-red-700");

        } else {

            // CLOSE
            slideUp(card, 300);

            toggleBtn.textContent = "+ New Assignment";
            toggleBtn.classList.remove("bg-red-600", "hover:bg-red-700");
            toggleBtn.classList.add("bg-indigo-600", "hover:bg-indigo-700");
        }

    });

});


/* ===== Smooth Slide Down ===== */
function slideDown(element, duration) {
    element.style.display = "block";
    element.style.overflow = "hidden";
    element.style.height = "0px";

    let height = element.scrollHeight;
    let start = null;

    function animation(timestamp) {
        if (!start) start = timestamp;
        let progress = timestamp - start;
        let percent = Math.min(progress / duration, 1);

        element.style.height = percent * height + "px";

        if (percent < 1) {
            requestAnimationFrame(animation);
        } else {
            element.style.height = "";
            element.style.overflow = "";
        }
    }

    requestAnimationFrame(animation);
}


/* ===== Smooth Slide Up ===== */
function slideUp(element, duration) {
    element.style.overflow = "hidden";
    element.style.height = element.scrollHeight + "px";

    let start = null;

    function animation(timestamp) {
        if (!start) start = timestamp;
        let progress = timestamp - start;
        let percent = Math.min(progress / duration, 1);

        element.style.height = (1 - percent) * element.scrollHeight + "px";

        if (percent < 1) {
            requestAnimationFrame(animation);
        } else {
            element.style.display = "none";
            element.style.height = "";
            element.style.overflow = "";
        }
    }

    requestAnimationFrame(animation);
}
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.paper-select').forEach(el => {
        new TomSelect(el, {
            placeholder: 'Search paper...',
            allowEmptyOption: true,
            create: false,
            maxOptions: null
        });
    });
});
</script>

@endsection
