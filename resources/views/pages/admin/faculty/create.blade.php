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
            <div class="flex gap-2 items-center">
                <select name="courses[0][course_id]" class="border rounded px-3 py-2" required>
                    <option value="">Select Course</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <select name="courses[0][paper_master_id]" class="border rounded px-3 py-2" required>
                    <option value="">Select Paper</option>
                    @foreach($papers as $p)
                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                    @endforeach
                </select>
                <button type="button" onclick="addCoursePaperRow()" class="px-3 py-1 bg-green-600 text-white rounded">+</button>
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
    row.classList.add('flex', 'gap-2', 'items-center');

    row.innerHTML = `
        <select name="courses[${index}][course_id]" class="border rounded px-3 py-2" required>
            <option value="">Select Course</option>
            @foreach($courses as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="courses[${index}][paper_master_id]" class="border rounded px-3 py-2" required>
            <option value="">Select Paper</option>
            @foreach($papers as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <button type="button" onclick="this.parentElement.remove()" class="px-3 py-1 bg-red-600 text-white rounded">-</button>
    `;
    container.appendChild(row);
    index++;
}
</script>
@endsection
