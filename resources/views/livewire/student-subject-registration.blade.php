<div class="min-h-screen bg-gray-100 py-10 px-4">

    <div class="max-w-7xl mx-auto grid lg:grid-cols-3 gap-6">

        <!-- LEFT -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-xl overflow-hidden">

            <!-- HEADER -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">

                <h1 class="text-3xl font-bold">
                    Subject Registration
                </h1>

                <p class="mt-2 text-indigo-100">
                    Semester {{ $nextSemester }} Registration
                </p>
            </div>

            <div class="p-6">

                <!-- SUCCESS -->
                @if(session()->has('success'))

                    <div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded-2xl mb-6">
                        {{ session('success') }}
                    </div>

                @endif

                <!-- INFO -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-5 mb-8">

                    <div class="grid md:grid-cols-3 gap-4 text-sm">

                        <div>
                            <span class="font-bold text-gray-700">
                                Department :
                            </span>

                            {{ $academic->department_id }}
                        </div>

                        <div>
                            <span class="font-bold text-gray-700">
                                Course :
                            </span>

                            {{ $academic->course_id }}
                        </div>

                        <div>
                            <span class="font-bold text-gray-700">
                                Semester :
                            </span>

                            {{ $nextSemester }}
                        </div>

                    </div>
                </div>

                <!-- DSC -->
                @if(isset($corePapers) && !empty($corePapers))

                    <div class="bg-green-50 border border-green-200 rounded-3xl p-6 mb-8">

                        <div class="flex items-center justify-between mb-5">

                            <h2 class="text-2xl font-bold text-green-700">
                                DSC Subjects
                            </h2>

                            <span class="bg-green-100 text-green-700 px-4 py-1 rounded-full text-sm">
                                Auto Selected
                            </span>
                        </div>

                        <div class="overflow-x-auto">

                            <table class="w-full">

                                <thead>

                                    <tr class="text-left text-gray-600 border-b">

                                        <th class="pb-3">Paper</th>
                                        <th class="pb-3">Code</th>
                                        <th class="pb-3">Status</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach($corePapers as $paper)

                                        <tr class="border-b last:border-0">

                                            <td class="py-4 font-semibold text-gray-800">
                                                {{ $paper->name }}
                                            </td>

                                            <td class="py-4 text-gray-500">
                                                {{ $paper->code }}
                                            </td>

                                            <td class="py-4">
                                                <span
                                                    class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                                    Compulsory
                                                </span>
                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>

                @endif

                <!-- DSE Papers TYPES -->
                <div class="space-y-6">

                    @foreach($dsePapers as $paper)



                        <div class="bg-gray-50 border rounded-3xl p-6">

                            <label class="block mb-3">

                                <div class="flex items-center justify-between mb-2">

                                    <span class="text-xl font-bold text-indigo-700">
                                        DSE Subject
                                    </span>

                                    <span class="text-sm text-gray-500">
                                        Select One
                                    </span>
                                </div>

                                <select wire:model="selectedPapers.DSE"
                                    class="w-full rounded-2xl border-gray-300 focus:ring-2 focus:ring-indigo-500 shadow-sm">

                                    <option value="">
                                        Select DSE Subject
                                    </option>


                                    <option value="{{ $paper->id }}">

                                        {{ $paper->name }}
                                        ({{ $paper->code }})

                                    </option>


                                </select>
                            </label>

                            @error('selectedPapers.' . 'DSE')

                                <div class="text-red-500 text-sm mt-2">
                                    {{ $message }}
                                </div>

                            @enderror
                        </div>



                    @endforeach

                    <!-- GE -->
                    <div class="bg-orange-50 border border-orange-200 rounded-3xl p-6">

                        <label class="block mb-3">

                            <div class="flex items-center justify-between mb-2">

                                <span class="text-xl font-bold text-orange-700">
                                    GE Subject
                                </span>

                                <span class="text-sm text-orange-500">
                                    Other Department
                                </span>
                            </div>

                            <select wire:model="selectedPapers.GE"
                                class="w-full rounded-2xl border-orange-300 focus:ring-2 focus:ring-orange-500 shadow-sm">

                                <option value="">
                                    Select GE Subject
                                </option>

                                @foreach($gePapers as $paper)

                                    <option value="{{ $paper->id }}">

                                        {{ $paper->name }}
                                        -
                                        {{ $paper->code }}

                                    </option>

                                @endforeach

                            </select>
                        </label>

                        @error('selectedPapers.GE')

                            <div class="text-red-500 text-sm mt-2">
                                {{ $message }}
                            </div>

                        @enderror
                    </div>
                    <!-- SEC -->
                    <div class="bg-orange-50 border border-orange-200 rounded-3xl p-6">

                        <label class="block mb-3">

                            <div class="flex items-center justify-between mb-2">

                                <span class="text-xl font-bold text-orange-700">
                                    SEC Subject
                                </span>

                                <span class="text-sm text-orange-500">
                                    Other Department
                                </span>
                            </div>

                            <select wire:model="selectedPapers.SEC"
                                class="w-full rounded-2xl border-orange-300 focus:ring-2 focus:ring-orange-500 shadow-sm">

                                <option value="">
                                    Select SEC Subject
                                </option>

                                @foreach($secPapers as $secPapers)

                                    <option value="{{ $paper->id }}">

                                        {{ $paper->name }}
                                        -
                                        {{ $paper->code }}

                                    </option>

                                @endforeach

                            </select>
                        </label>

                        @error('selectedPapers.SEC')

                            <div class="text-red-500 text-sm mt-2">
                                {{ $message }}
                            </div>

                        @enderror
                    </div>

                    <!-- AEC -->
                    <div class="bg-orange-50 border border-orange-200 rounded-3xl p-6">

                        <label class="block mb-3">

                            <div class="flex items-center justify-between mb-2">

                                <span class="text-xl font-bold text-orange-700">
                                    AEC Subject
                                </span>

                                <span class="text-sm text-orange-500">
                                    Other Department
                                </span>
                            </div>

                            <select wire:model="selectedPapers.AEC"
                                class="w-full rounded-2xl border-orange-300 focus:ring-2 focus:ring-orange-500 shadow-sm">

                                <option value="">
                                    Select GE Subject
                                </option>

                                @foreach($aecPapers as $paper)

                                    <option value="{{ $paper->id }}">

                                        {{ $paper->name }}
                                        -
                                        {{ $paper->code }}

                                    </option>

                                @endforeach

                            </select>
                        </label>

                        @error('selectedPapers.AEC')

                            <div class="text-red-500 text-sm mt-2">
                                {{ $message }}
                            </div>

                        @enderror
                    </div>

                </div>

                <!-- SUBMIT -->
                <div class="mt-10">

                    <button wire:click="save" wire:loading.attr="disabled"
                        class="w-full bg-blue-600 text-white font-bold py-4 rounded-2xl shadow-lg hover:opacity-90 transition">

                        <span wire:loading.remove>
                            Submit Registration
                        </span>

                        <span wire:loading>
                            Saving...
                        </span>

                    </button>
                </div>
            </div>
        </div>

        <!-- RIGHT -->
        <div class="bg-white rounded-3xl shadow-xl p-6 h-fit sticky top-5">

            <h2 class="text-2xl font-bold text-gray-800 mb-5">
                My Registrations
            </h2>

            <div class="space-y-4 max-h-[700px] overflow-y-auto">

                @forelse($registered as $item)

                    <div class="border rounded-2xl p-4">

                        <div class="flex items-center justify-between mb-2">

                            <h3 class="font-bold text-gray-800">
                                {{ $item->paper->name ?? '-' }}
                            </h3>

                            @if($item->is_approved)

                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Approved
                                </span>

                            @else

                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Pending
                                </span>

                            @endif
                        </div>

                        <div class="text-sm text-gray-500">

                            Semester :
                            {{ $item->semester }}

                        </div>

                    </div>

                @empty

                    <div class="bg-gray-100 rounded-2xl p-6 text-center text-gray-500">
                        No registrations found.
                    </div>

                @endforelse

            </div>
        </div>
    </div>
</div>