@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto p-6 bg-white shadow rounded-lg">

    <h1 class="text-2xl font-bold mb-6">Edit Faculty: {{ $faculty->name }}</h1>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-200 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.faculty.update', $faculty->id) }}">
        @csrf
        @method('PUT')

        {{-- Faculty Basic Info --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <input type="text" name="title" placeholder="Title" value="{{ $faculty->title }}" class="border rounded px-3 py-2" required>
            <input type="text" name="name" placeholder="Name" value="{{ $faculty->name }}" class="border rounded px-3 py-2" required>
            <input type="text" name="designation" placeholder="Designation" value="{{ $faculty->designation }}" class="border rounded px-3 py-2" required>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <select name="department_id" class="border rounded px-3 py-2" required>
                <option value="">Select Department</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" @if($faculty->department_id==$d->id) selected @endif>{{ $d->name }}</option>
                @endforeach
            </select>
            <input type="email" name="email" placeholder="Email" value="{{ $faculty->email }}" class="border rounded px-3 py-2" required>
            <input type="text" name="mobile" placeholder="Mobile" value="{{ $faculty->mobile }}" class="border rounded px-3 py-2" required>
        </div>

        {{-- Status --}}
        <div class="mb-6">
            <label class="inline-flex items-center">
                <input type="checkbox" name="status" value="1" class="form-checkbox" @if($faculty->status) checked @endif>
                <span class="ml-2">Active</span>
            </label>
        </div>

        {{-- Faculty Details (Courses & Papers) --}}
        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-3">Assigned Courses & Papers</h2>

            <div id="facultyDetailsWrapper" class="space-y-2">

            @if($faculty->details->count() == 0)

                <select name="details[0][course_id]" class="border rounded px-3 py-2" required>
                    <option value="">Select Course</option>
                    @foreach($courses as $c)
                        <option value="{{ $c->id }}" @if($d->course_id==$c->id) selected @endif>{{ $c->name }}</option>
                    @endforeach
                </select>

                {{-- Paper --}}
                <select name="details[0][paper_master_id]" class="border rounded px-3 py-2" required>
                    <option value="">Select Paper</option>
                    @foreach($papers as $p)
                        <option value="{{ $p->id }}" @if($d->paper_master_id==$p->id) selected @endif>{{ $p->name }}</option>
                    @endforeach
                </select>
            @endif
            @foreach($faculty->details as $index => $d)
                <div class="flex gap-2 items-center">
                    <select name="details[{{ $index }}][course_id]" class="border rounded px-3 py-2" required>
                        <option value="">Select Course</option>
                        @foreach($courses as $c)
                            <option value="{{ $c->id }}" @if($d->course_id==$c->id) selected @endif>{{ $c->name }}</option>
                        @endforeach
                    </select>

                    {{-- Paper --}}
                    <select name="details[{{ $index }}][paper_master_id]" class="border rounded px-3 py-2" required>
                        <option value="">Select Paper</option>
                        @foreach($papers as $p)
                            <option value="{{ $p->id }}" @if($d->paper_master_id==$p->id) selected @endif>{{ $p->name }}</option>
                        @endforeach
                    </select>

                    <button type="button" onclick="this.parentElement.remove()" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Remove</button>
                </div>
            @endforeach
        </div>

            <button type="button" onclick="addFacultyDetail()" class="mt-3 px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                + Add Course & Paper
            </button>
        </div>

        {{-- Submit Button --}}
        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                Update Faculty
            </button>
        </div>

    </form>
</div>

<script>
let detailIndex = {{ $faculty->details->count() }};

function addFacultyDetail() {
    const wrapper = document.getElementById('facultyDetailsWrapper');

    const html = `
    <div class="flex gap-2 items-center mt-2">
        <select name="details[${detailIndex}][course_id]" class="border rounded px-3 py-2" required>
            <option value="">Select Course</option>
            @foreach($courses as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </select>

        <select name="details[${detailIndex}][paper_master_id]" class="border rounded px-3 py-2" required>
            <option value="">Select Paper</option>
            @foreach($papers as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <button type="button" onclick="this.parentElement.remove()" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Remove</button>
    </div>
    `;

    wrapper.insertAdjacentHTML('beforeend', html);
    detailIndex++;
}
</script>
@endsection
