<div class="min-h-screen bg-gray-100 py-8 px-4">

    <div class="max-w-7xl mx-auto grid lg:grid-cols-3 gap-6">

        <!-- LEFT -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-xl overflow-hidden">

            <!-- HEADER -->
            <div class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-white">

                <h1 class="text-3xl font-bold">
                    Subject Registration
                </h1>

                <p class="text-indigo-100 mt-2">
                    Semester {{ $nextSemester }} Registration
                </p>

            </div>

            <div class="p-6">

                <!-- SUCCESS -->
                @if(session()->has('success'))

                    <div class="bg-green-100 border border-green-300 text-green-700 px-5 py-4 rounded-2xl mb-6">
                        {{ session('success') }}
                    </div>

                @endif

                <!-- ERROR -->
                @if(session()->has('error'))

                    <div class="bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-2xl mb-6">
                        {{ session('error') }}
                    </div>

                @endif

                <!-- INFO -->
                <div class="bg-indigo-50 border border-indigo-100 rounded-3xl p-5 mb-8">

                    <div class="grid md:grid-cols-3 gap-4 text-sm">

                        <div>
                            <div class="text-gray-500 mb-1">
                                Department
                            </div>

                            <div class="font-bold text-gray-800">
                                {{ $academic->department->name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500 mb-1">
                                Course
                            </div>

                            <div class="font-bold text-gray-800">
                                {{ $academic->course->name }}
                            </div>
                        </div>

                        <div>
                            <div class="text-gray-500 mb-1">
                                Semester
                            </div>

                            <div class="font-bold text-gray-800">
                                {{ $nextSemester }}
                            </div>
                        </div>

                    </div>

                </div>

                <!-- RULES -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-5 mb-8">

                    <h2 class="text-lg font-bold text-yellow-800 mb-4">
                        Registration Rules
                    </h2>

                    @if($nextSemester == 3)

                        <ul class="space-y-2 text-sm text-yellow-700">

                            <li>• 3 DSC subjects compulsory</li>
                            <li>• 1 VAC compulsory</li>
                            <li>• 1 SEC compulsory</li>
                            <li>• 1 AEC compulsory</li>
                            <li>• Optional DSE and/or GE</li>

                        </ul>

                    @elseif($nextSemester == 5)

                        <ul class="space-y-2 text-sm text-yellow-700">

                            <li>• 3 DSC compulsory</li>
                            <li>• 1 DSE compulsory</li>
                            <li>• 1 GE compulsory</li>
                            <li>• 1 SEC compulsory</li>

                        </ul>

                    @elseif($nextSemester == 7)

                        <ul class="space-y-2 text-sm text-yellow-700">

                            <li>• 1 DSC compulsory</li>
                            <li>• Total DSE + GE must be exactly 3</li>
                            <li>• Allowed:</li>
                            <li class="ml-4">- 3 DSE</li>
                            <li class="ml-4">- 2 DSE + 1 GE</li>
                            <li class="ml-4">- 1 DSE + 2 GE</li>

                        </ul>

                    @endif

                </div>

                <!-- DSC -->
                @if($corePapers->count())

                    <div class="bg-green-50 border border-green-200 rounded-3xl p-6 mb-6">

                        <div class="flex items-center justify-between mb-5">

                            <h2 class="text-2xl font-bold text-green-700">
                                DSC Subjects
                            </h2>

                            <span class="bg-green-100 text-green-700 px-4 py-1 rounded-full text-xs font-semibold">
                                Auto Selected
                            </span>

                        </div>

                        <div class="overflow-x-auto">

                            <table class="w-full">

                                <thead>

                                    <tr class="border-b text-left text-gray-600">

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

                <!-- SUBJECT SECTIONS -->
                <div class="space-y-6">

                    @push('styles')

                        <!-- TOM SELECT -->
                        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">

                        <style>
                            .ts-control {
                                border-radius: 18px !important;
                                min-height: 56px !important;
                                padding: 12px !important;
                                border: 2px solid #dbeafe !important;
                                box-shadow: none !important;
                            }

                            .ts-wrapper.multi .ts-control>div {
                                background: #2563eb !important;
                                color: white !important;
                                border-radius: 999px !important;
                                padding: 4px 10px !important;
                            }

                            .ts-dropdown {
                                border-radius: 18px !important;
                                overflow: hidden !important;
                                border: 1px solid #e5e7eb !important;
                            }

                            .ts-dropdown .option {
                                padding: 12px 14px !important;
                            }

                            .ts-dropdown .active {
                                background: #eff6ff !important;
                                color: #1d4ed8 !important;
                            }
                        </style>

                    @endpush


                    <div class="space-y-6">

                        <!-- DSE -->
                        @if($dsePapers->count())

                            <div class="bg-indigo-50 border border-indigo-200 rounded-3xl p-6">

                                <div class="flex items-center justify-between mb-4">

                                    <h2 class="text-xl font-bold text-indigo-700">
                                        DSE Subjects
                                    </h2>

                                    @if($nextSemester == 7)

                                        <span class="text-sm text-indigo-500">

                                            Selected :
                                            {{ count($selectedPapers['DSE'] ?? []) }}
                                            / 3

                                        </span>

                                    @endif

                                </div>

                                @if($nextSemester == 7)

                                    <!-- MULTIPLE -->

                                    <div wire:ignore>

                                        <select id="dseSelect" multiple placeholder="Select DSE Subjects..."
                                            class="paper-select w-full rounded-2xl border-blue-300 h-14 focus:ring-2 focus:ring-blue-500">

                                            @foreach($dsePapers as $paper)

                                                <option value="{{ $paper->id }}" data-eligibility="{{ $paper->eligibilty }}"
                                                    data-prerequisites="{{ $paper->prerequisites }}">
                                                    {{ $paper->name }}- ({{ $paper->code }})
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                @else

                                    <!-- SINGLE -->

                                    <div wire:ignore>

                                        <select id="dseSingleSelect" placeholder="Select DSE Subject"
                                            class="paper-select w-full rounded-2xl border-blue-300 h-14 focus:ring-2 focus:ring-blue-500"
                                            onchange="@this.set('selectedPapers.DSE', this.value)">

                                            <option value="">
                                                Select DSE Subject
                                            </option>

                                            @foreach($dsePapers as $paper)

                                                <option value="{{ $paper->id }}" data-eligibility="{{ $paper->eligibilty }}"
                                                    data-prerequisites="{{ $paper->prerequisites }}">
                                                    {{ $paper->name }}- ({{ $paper->code }})
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                @endif

                                @error('selectedPapers.DSE')

                                    <div class="text-red-500 text-sm mt-3">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                        @endif


                        <!-- GE -->
                        @if($gePapers->count())

                            <div class="bg-orange-50 border border-orange-200 rounded-3xl p-6">

                                <div class="flex items-center justify-between mb-4">

                                    <h2 class="text-xl font-bold text-orange-700">
                                        GE Subjects
                                    </h2>

                                    @if($nextSemester == 7)

                                        <span class="text-sm text-orange-500">

                                            Selected :
                                            {{ count($selectedPapers['GE'] ?? []) }}
                                            / 3

                                        </span>

                                    @endif

                                </div>

                                @if($nextSemester == 7)

                                    <!-- MULTIPLE -->

                                    <div wire:ignore>

                                        <select id="geSelect" multiple placeholder="Select GE Subjects..."
                                            class="paper-select w-full rounded-2xl border-blue-300 h-14 focus:ring-2 focus:ring-blue-500">

                                            @foreach($gePapers as $paper)

                                                <option value="{{ $paper->id }}" data-eligibility="{{ $paper->eligibilty }}"
                                                    data-prerequisites="{{ $paper->prerequisites }}">
                                                    {{ $paper->name }}- ({{ $paper->code }})
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                @else

                                    <!-- SINGLE -->

                                    <div wire:ignore>

                                        <select id="geSingleSelect" placeholder="Select GE Subject"
                                            class="paper-select w-full rounded-2xl border-blue-300 h-14 focus:ring-2 focus:ring-blue-500"
                                            onchange="@this.set('selectedPapers.GE', this.value)">

                                            <option value="">
                                                Select GE Subject
                                            </option>

                                            @foreach($gePapers as $paper)

                                                <option value="{{ $paper->id }}" data-eligibility="{{ $paper->eligibilty }}"
                                                    data-prerequisites="{{ $paper->prerequisites }}">
                                                    {{ $paper->name }}- ({{ $paper->code }})
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                @endif

                                @error('selectedPapers.GE')

                                    <div class="text-red-500 text-sm mt-3">
                                        {{ $message }}
                                    </div>

                                @enderror

                            </div>

                        @endif

                    </div>


                    @push('scripts')

                        <script>

                            document.addEventListener('livewire:init', () => {

                                let dseSelect = null;
                                let geSelect = null;

                                /*
                                |--------------------------------------------------------------------------
                                | DSE SELECT
                                |--------------------------------------------------------------------------
                                */

                                if (document.getElementById('dseSelect')) {

                                    dseSelect = new TomSelect('#dseSelect', {

                                        plugins: ['remove_button'],

                                        create: false,

                                        persist: false,

                                        maxItems: 3,

                                        onChange: function (values) {

                                            @this.set('selectedPapers.DSE', values);
                                            let lastValue = values[values.length - 1];

                                            let option = document.querySelector(
                                                `#dseSelect option[value="${lastValue}"]`
                                            );

                                            showPaperDialog(option);

                                            applyRules();

                                        }

                                    });

                                }

                                /*
                                |--------------------------------------------------------------------------
                                | GE SELECT
                                |--------------------------------------------------------------------------
                                */

                                if (document.getElementById('geSelect')) {

                                    geSelect = new TomSelect('#geSelect', {

                                        plugins: ['remove_button'],

                                        create: false,

                                        persist: false,

                                        maxItems: 2,

                                        onChange: function (values) {

                                            @this.set('selectedPapers.GE', values);

                                            let lastValue = values[values.length - 1];

                                            let option = document.querySelector(
                                                `#geSelect option[value="${lastValue}"]`
                                            );

                                            showPaperDialog(option);

                                            applyRules();

                                        }

                                    });

                                }

                                /*
                                |--------------------------------------------------------------------------
                                | RESET OPTIONS
                                |--------------------------------------------------------------------------
                                */

                                function resetOptions(selectInstance) {

                                    if (!selectInstance) return;

                                    Object.keys(selectInstance.options).forEach(value => {

                                        let option = selectInstance.getOption(value);

                                        if (option) {

                                            option.classList.remove('hidden');

                                            option.style.pointerEvents = 'auto';
                                            option.style.opacity = '1';

                                        }

                                    });

                                }

                                /*
                                |--------------------------------------------------------------------------
                                | DISABLE OPTION
                                |--------------------------------------------------------------------------
                                */

                                function disableRemaining(selectInstance) {

                                    if (!selectInstance) return;

                                    Object.keys(selectInstance.options).forEach(value => {

                                        if (!selectInstance.items.includes(value)) {

                                            let option = selectInstance.getOption(value);

                                            if (option) {

                                                option.style.pointerEvents = 'none';
                                                option.style.opacity = '0.4';

                                            }

                                        }

                                    });

                                }

                                /*
                                |--------------------------------------------------------------------------
                                | APPLY RULES
                                |--------------------------------------------------------------------------
                                */

                                function applyRules() {

                                    let dseCount = dseSelect ? dseSelect.items.length : 0;
                                    let geCount = geSelect ? geSelect.items.length : 0;

                                    let total = dseCount + geCount;

                                    /*
                                    |--------------------------------------------------------------------------
                                    | RESET FIRST
                                    |--------------------------------------------------------------------------
                                    */

                                    resetOptions(dseSelect);
                                    resetOptions(geSelect);

                                    /*
                                    |--------------------------------------------------------------------------
                                    | VALID COMBINATIONS
                                    |--------------------------------------------------------------------------
                                    |
                                    | 3 DSE
                                    | 2 DSE + 1 GE
                                    | 1 DSE + 2 GE
                                    |--------------------------------------------------------------------------
                                    */

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CASE: 3 DSE
                                    |--------------------------------------------------------------------------
                                    */

                                    if (dseCount === 3) {

                                        disableRemaining(dseSelect);
                                        disableRemaining(geSelect);

                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CASE: 2 DSE + 1 GE
                                    |--------------------------------------------------------------------------
                                    */

                                    if (dseCount === 2 && geCount === 1) {

                                        disableRemaining(dseSelect);
                                        disableRemaining(geSelect);

                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | CASE: 1 DSE + 2 GE
                                    |--------------------------------------------------------------------------
                                    */

                                    if (dseCount === 1 && geCount === 2) {

                                        disableRemaining(dseSelect);
                                        disableRemaining(geSelect);

                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | PREVENT INVALID TOTAL > 3
                                    |--------------------------------------------------------------------------
                                    */

                                    if (total >= 3) {

                                        disableRemaining(dseSelect);
                                        disableRemaining(geSelect);

                                    }

                                    /*
                                    |--------------------------------------------------------------------------
                                    | PREVENT 3 GE
                                    |--------------------------------------------------------------------------
                                    */

                                    if (geCount >= 2 && dseCount === 0) {

                                        disableRemaining(geSelect);

                                    }

                                }

                                /*
                                |--------------------------------------------------------------------------
                                | INITIAL LOAD
                                |--------------------------------------------------------------------------
                                */

                                setTimeout(() => {

                                    applyRules();

                                }, 300);

                            });
                            function showPaperDialog(option) {

                                if (!option) return;

                                let eligibility = option.dataset.eligibility;
                                let prerequisites = option.dataset.prerequisites;

                                // if both empty
                                if (!eligibility && !prerequisites) {
                                    return;
                                }

                                let html = `<div style="text-align:left">`;

                                if (eligibility) {

                                    html += `
                                                                                                                                                                                                                                <div style="margin-bottom:16px">
                                                                                                                                                                                                                                    <strong>• Eligibility</strong><br>
                                                                                                                                                                                                                                    ${eligibility}
                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                            `;
                                }

                                if (prerequisites) {

                                    html += `
                                                                                                                                                                                                                                <div>
                                                                                                                                                                                                                                    <strong>• Prerequisites</strong><br>
                                                                                                                                                                                                                                    ${prerequisites}
                                                                                                                                                                                                                                </div>
                                                                                                                                                                                                                            `;
                                }

                                html += `</div>`;

                                Swal.fire({

                                    title: '<strong>Paper Requirements</strong>',

                                    html: html,

                                    icon: 'info',

                                    confirmButtonText: 'OK',

                                    confirmButtonColor: '#2563eb',

                                    width: 600

                                });
                            }
                            document.addEventListener('change', function (e) {

                                if (e.target.classList.contains('paper-select')) {

                                    let option = e.target.selectedOptions[0];

                                    showPaperDialog(option);
                                }

                            });

                        </script>

                    @endpush
                    <!-- SEC -->
                    @if($secPapers->count())

                        <div class="bg-blue-50 border border-blue-200 rounded-3xl p-6">

                            <h2 class="text-xl font-bold text-blue-700 mb-4">
                                SEC Subject
                            </h2>

                            <select wire:model="selectedPapers.SEC"
                                class="paper-select w-full rounded-2xl border-blue-300 h-14 focus:ring-2 focus:ring-blue-500">

                                <option value="">
                                    Select SEC Subject
                                </option>

                                @foreach($secPapers as $paper)

                                    <option value="{{ $paper->id }}" data-eligibility="{{ $paper->eligibilty }}"
                                        data-prerequisites="{{ $paper->prerequisites }}">
                                        {{ $paper->name }}- ({{ $paper->code }})
                                    </option>

                                @endforeach

                            </select>

                            @error('selectedPapers.SEC')

                                <div class="text-red-500 text-sm mt-3">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    @endif

                    <!-- VAC -->
                    @if($vacPapers->count())

                        <div class="bg-pink-50 border border-pink-200 rounded-3xl p-6">

                            <h2 class="text-xl font-bold text-pink-700 mb-4">
                                VAC Subject
                            </h2>

                            <select wire:model="selectedPapers.VAC"
                                class="paper-select w-full rounded-2xl border-blue-300 h-14 focus:ring-2 focus:ring-blue-500">

                                <option value="">
                                    Select VAC Subject
                                </option>

                                @foreach($vacPapers as $paper)

                                    <option value="{{ $paper->id }}" data-eligibility="{{ $paper->eligibilty }}"
                                        data-prerequisites="{{ $paper->prerequisites }}">
                                        {{ $paper->name }}- ({{ $paper->code }})
                                    </option>

                                @endforeach

                            </select>

                            @error('selectedPapers.VAC')

                                <div class="text-red-500 text-sm mt-3">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    @endif

                    <!-- AEC -->
                    @if($aecPapers->count())

                        <div class="bg-purple-50 border border-purple-200 rounded-3xl p-6">

                            <h2 class="text-xl font-bold text-purple-700 mb-4">
                                AEC Subject
                            </h2>

                            <select wire:model="selectedPapers.AEC"
                                class="paper-select w-full rounded-2xl border-blue-300 h-14 focus:ring-2 focus:ring-blue-500">

                                <option value="">
                                    Select AEC Subject
                                </option>

                                @foreach($aecPapers as $paper)

                                    <option value=" {{ $paper->id }}" data-eligibility="{{ $paper->eligibilty }}"
                                        data-prerequisites="{{ $paper->prerequisites }}">
                                        {{ $paper->name }}- ({{ $paper->code }})
                                    </option>

                                @endforeach

                            </select>

                            @error('selectedPapers.AEC')

                                <div class="text-red-500 text-sm mt-3">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    @endif

                </div>

                <!-- SUBMIT -->
                <div class="mt-10">

                    <button wire:click="save" wire:loading.attr="disabled"
                        class="w-full bg-blue-600  text-white font-bold py-4 rounded-2xl shadow-lg hover:opacity-90 transition">

                        <span wire:loading.remove>
                            {{ $isEdit ? 'Update Registration' : 'Submit Registration' }}
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

            <h2 class="text-2xl font-bold text-gray-800 mb-6">
                My Registrations
            </h2>

            <div class="space-y-4 max-h-[700px] overflow-y-auto">

                @forelse($registered as $item)

                    <div class="border rounded-2xl p-4">

                        <div class="flex items-center justify-between mb-2">

                            <h3 class="font-bold text-gray-800">

                                {{ $item->paper->name ?? '-' }}

                            </h3>

                            @if($item->is_approved == 1)

                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Approved
                                </span>
                            @elseif($item->is_approved == 2)

                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Rejected
                                </span>
                            @else

                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    Pending
                                </span>

                            @endif

                        </div>

                        <div class="text-sm text-gray-500">

                            Semester :
                            {{ $item->semester }} - {{ $item->paper->paper_type ?? '-' }}

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