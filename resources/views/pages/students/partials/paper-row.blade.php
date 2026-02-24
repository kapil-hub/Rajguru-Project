<div class="flex items-center gap-3 bg-white rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <input type="hidden" name="papers[{{ $index }}][is_backlog]" value="{{ $isBacklog ? 1 : 0 }}">

    <!-- Paper Dropdown -->
    <div class="flex-1">
        <select name="papers[{{ $index }}][paper_id]"
                class="paper-select border rounded-lg p-2 w-full">
            <option value="">Select Paper</option>
            @foreach($allPapers as $p)
                <option value="{{ $p->id }}"
                        data-semester="{{ $p->semester }}"
                        @selected(optional($paper)->paper_master_id == $p->id)>
                    {{ $p->name }} ({{ $p->code }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- Semester display (readonly, backlog only) --}}
    @if($isBacklog)
        <div class="w-28">
            <input type="text"
                   name="papers[{{ $index }}][semester]"
                   class="semester-box border rounded-lg p-2 w-full bg-gray-100"
                   readonly
                   value="{{ $paper->semester ?? '' }}">
        </div>
    @endif

    <button type="button"
            onclick="removePaper(this)"
            class="text-red-600 text-xl hover:scale-110">
        🗑️
    </button>
</div>
