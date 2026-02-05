@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto p-6 bg-white rounded shadow">

    <h2 class="text-2xl font-semibold mb-4">Add Faculty</h2>

    @if ($errors->any())
        <div class="mb-4 p-2 bg-red-100 text-red-700 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.faculty.store') }}" method="POST">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <input type="text" name="title" placeholder="Title" class="border rounded px-3 py-2">
            <input type="text" name="name" placeholder="Name" class="border rounded px-3 py-2" required>
            <input type="text" name="designation" placeholder="Designation" class="border rounded px-3 py-2" required>
            <select name="department_id" class="border rounded px-3 py-2" required>
                <option value="">Select Department</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                @endforeach
            </select>
            <input type="text" name="mobile" placeholder="Mobile" class="border rounded px-3 py-2">
            <input type="email" name="email" placeholder="Email" class="border rounded px-3 py-2" required>
            <input type="password" name="password" placeholder="Password" class="border rounded px-3 py-2" required>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" class="border rounded px-3 py-2" required>
        </div>

        <h3 class="text-lg font-semibold mb-2">Assign Courses & Papers</h3>
        <div id="coursePaperContainer" class="space-y-2 mb-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-3">

                <select name="courses[0][course_id]"
                    class="border rounded px-3 py-2 w-full"
                    required>
                    <option value="">Select Course</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>

                <select name="courses[0][paper_master_id]"
                    class="paper-select border rounded px-3 py-2 w-full"
                    required>
                    <option value="">Search Paper</option>
                    @foreach($papers as $p)
                        <option value="{{ $p->id }}">{{ trim($p->name) }}</option>
                    @endforeach
                </select>

                <!-- + button aligned -->
                <button type="button"
                    onclick="addCoursePaperRow()"
                    class="w-10 h-10 bg-green-600 text-white rounded flex items-center justify-center">
                    +
                </button>

            </div>

        </div>

        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Faculty</button>
    </form>

</div>

<script>
let index = 1;

function addCoursePaperRow() {
    const container = document.getElementById('coursePaperContainer');
    const row = document.createElement('div');

    row.className = 'grid grid-cols-1 md:grid-cols-3 gap-4 items-end mb-3';

    row.innerHTML = `
        <select name="courses[${index}][course_id]"
            class="border rounded px-3 py-2 w-full"
            required>
            <option value="">Select Course</option>
            @foreach($courses as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>

        <select name="courses[${index}][paper_master_id]"
            class="paper-select border rounded px-3 py-2 w-full"
            required>
            <option value="">Search Paper</option>
            @foreach($papers as $p)
                <option value="{{ $p->id }}">{{ trim($p->name) }}</option>
            @endforeach
        </select>

        <!-- - button aligned -->
        <button type="button"
            onclick="this.closest('.grid').remove()"
            class="w-10 h-10 bg-red-600 text-white rounded flex items-center justify-center">
            −
        </button>
    `;

    container.appendChild(row);

    // re-init Tom Select
    initPaperSelect(row.querySelector('.paper-select'));

    index++;
}
function initPaperSelect(element) {
    new TomSelect(element, {
        placeholder: 'Search paper...',
        allowEmptyOption: true,
        create: false,
        maxOptions: null
    });
}
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.paper-select').forEach(el => {
        initPaperSelect(el);
    });
});
</script>

@endsection
