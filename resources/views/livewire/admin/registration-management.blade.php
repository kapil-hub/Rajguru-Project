<div class="min-h-screen bg-gray-100 p-6">
@push('styles')

<style>

.custom-scrollbar::-webkit-scrollbar {
    width: 10px;
    height: 10px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #fef9c3;
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #eab308;
    border-radius: 999px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #ca8a04;
}

/* Firefox */ 

.custom-scrollbar {
    scrollbar-width: thin;
    scrollbar-color: #eab308 #fef9c3;
}

</style>

@endpush
    <!-- HEADER -->

    <div class="flex items-center justify-between mb-6">

        <div>

            <h1 class="text-3xl font-bold text-gray-800">
                Registration Management
            </h1>

            <p class="text-gray-500 mt-1">
                Subject Registration Approval & Analytics
            </p>

        </div>

        @if(Auth::guard('admin')->check())

            <div class="flex items-center gap-3">

                <button wire:click="migrateRegistrations" wire:loading.attr="disabled"
                    class="px-5 py-3 rounded-2xl font-semibold transition bg-rose-600 text-white shadow-lg hover:bg-rose-700 disabled:opacity-60">

                    Migrate Registrations

                </button>

                <span wire:loading wire:target="migrateRegistrations" class="text-sm font-medium text-rose-600">
                    Running migration...
                </span>

            </div>

        @endif

    </div>

    @if($showMigrationPanel && Auth::guard('admin')->check())

        <div class="bg-rose-50 border border-rose-200 rounded-3xl p-5 mb-6">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <h2 class="text-lg font-bold text-rose-900">
                        Registration Migration Status
                    </h2>

                    <p class="text-sm text-rose-700 mt-1">
                        The process archives current student papers, clears the live table, imports approved registrations, and updates student semesters in a single transaction.
                    </p>

                </div>

                @if($migrationSuccess)

                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                        Completed
                    </span>

                @elseif($migrationError)

                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                        Failed
                    </span>

                @else

                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-700">
                        In Progress
                    </span>

                @endif

            </div>

            @if($migrationError)

                <div class="mt-4 rounded-2xl bg-red-100 text-red-700 px-4 py-3 text-sm">
                    {{ $migrationError }}
                </div>

            @endif

            @if(count($migrationLogs))

                <ol class="mt-4 space-y-2">

                    @foreach($migrationLogs as $log)

                        <li class="flex gap-3 text-sm text-rose-900">

                            <span class="mt-0.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-600 text-white text-xs font-bold">
                                {{ $loop->iteration }}
                            </span>

                            <span class="leading-6">
                                {{ $log }}
                            </span>

                        </li>

                    @endforeach

                </ol>

            @endif

        </div>

    @endif

    <!-- TABS -->

    <div class="flex gap-3 mb-6">

        <button wire:click="$set('activeTab', 'students')" class="px-5 py-3 rounded-2xl font-semibold transition
            {{ $activeTab == 'students'
    ? 'bg-indigo-600 text-white shadow-lg'
    : 'bg-white text-gray-700 border' }}">

            Student Registrations

        </button>
        @if(Auth::guard('admin')->check())
            <button wire:click="$set('activeTab', 'analytics')" class="px-5 py-3 rounded-2xl font-semibold transition
                {{ $activeTab == 'analytics'
        ? 'bg-indigo-600 text-white shadow-lg'
        : 'bg-white text-gray-700 border' }}">

                Paper Analytics

            </button>
        @endif

    </div>

    <!-- STUDENT TAB -->

    @if($activeTab == 'students')

        <!-- FILTERS -->

        <div class="bg-white rounded-3xl shadow-sm p-5 mb-6">

            <div class="grid lg:grid-cols-3 gap-4">

                <!-- SEARCH -->

                <div>

                    <input type="text" wire:model.live.debounce.500ms="search"
                        placeholder="Search student name / roll no..." class="w-full rounded-2xl border-gray-300
                                    h-12 focus:ring-2 focus:ring-indigo-500">

                </div>

                <!-- SEMESTER -->

                <div>

                    <select wire:model.live="semester" class="w-full rounded-2xl border-gray-300
                                    h-12 focus:ring-2 focus:ring-indigo-500">

                        <option value="">
                            All Semester
                        </option>

                        @for($i = 1; $i <= 8; $i++)

                            <option value="{{ $i }}">
                                Semester {{ $i }}
                            </option>

                        @endfor

                    </select>

                </div>

            </div>

        </div>

        <!-- STUDENT TABLE -->

        <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-gray-50">

                        <tr class="text-left text-gray-600">

                            <th class="p-4">
                                Student
                            </th>

                            <th class="p-4">
                                Roll No
                            </th>

                            <th class="p-4">
                                Semester
                            </th>

                            <th class="p-4">
                                Total Subjects
                            </th>

                            <th class="p-4">
                                Pending
                            </th>

                            <th class="p-4">
                                Approved
                            </th>

                            <th class="p-4">
                                Rejected
                            </th>

                            <th class="p-4 text-right">
                                Action
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($registrations as $row)

                            <tr class="border-t hover:bg-gray-50 transition">

                                <td class="p-4">

                                    <div class="font-semibold text-gray-800">

                                        {{ $row->student->name ?? '-' }}

                                    </div>

                                </td>

                                <td class="p-4 text-gray-500">

                                    {{ $row->student->academic->roll_number ?? '-' }}

                                </td>

                                <td class="p-4">

                                    <span class="bg-indigo-100 text-indigo-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        Semester {{ $row->semester }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    <span class="bg-blue-100 text-blue-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->total_subjects }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    <span class="bg-yellow-100 text-yellow-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->pending_count }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    <span class="bg-green-100 text-green-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->approved_count }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    <span class="bg-red-100 text-red-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->rejected_count ?? 0 }}

                                    </span>

                                </td>

                                <td class="p-4 text-right">

                                    <button wire:click="view(
                                                                    {{ $row->student_user_id }},
                                                                    {{ $row->semester }}
                                                                )" class="bg-indigo-600 hover:bg-indigo-700
                                                                text-white px-5 py-2 rounded-2xl
                                                                font-semibold transition">

                                        View Subjects

                                    </button>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center p-10 text-gray-500">

                                    No registrations found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="p-5">

                {{ $registrations->links() }}

            </div>

        </div>

    @endif

    <!-- ANALYTICS TAB -->

    @if($activeTab == 'analytics')

        <!-- FILTERS -->

        <div class="bg-white rounded-3xl shadow-sm p-5 mb-6">

            <div class="grid lg:grid-cols-3 gap-4">

                <!-- SEMESTER -->

                <div>

                    <select wire:model.live="analyticsSemester" class="w-full rounded-2xl border-gray-300
                                    h-12 focus:ring-2 focus:ring-indigo-500">

                        <option value="">
                            All Semester
                        </option>

                        @for($i = 1; $i <= 8; $i++)

                            <option value="{{ $i }}">
                                Semester {{ $i }}
                            </option>

                        @endfor

                    </select>

                </div>

                <!-- PAPER TYPE -->

                <div>

                    <select wire:model.live="paperTypeFilter" class="w-full rounded-2xl border-gray-300
                                    h-12 focus:ring-2 focus:ring-indigo-500">

                        <option value="">
                            All Paper Types
                        </option>

                        <option value="DSC">DSC</option>
                        <option value="DSE">DSE</option>
                        <option value="GE">GE</option>
                        <option value="SEC">SEC</option>
                        <option value="VAC">VAC</option>
                        <option value="AEC">AEC</option>

                    </select>

                </div>

                <!-- PAPER -->

                <div>

                    <select wire:model.live="paperFilter" class="w-full rounded-2xl border-gray-300
                                    h-12 focus:ring-2 focus:ring-indigo-500">

                        <option value="">
                            All Papers
                        </option>

                        @foreach($papers as $paper)

                            <option value="{{ $paper->id }}">

                                {{ $paper->name }}
                                ({{ $paper->code }})

                            </option>

                        @endforeach

                    </select>

                </div>

            </div>

        </div>

        <!-- ANALYTICS TABLE -->

        <div class="bg-white rounded-3xl shadow-sm overflow-hidden">

            <div class="overflow-x-auto">

                <table class="w-full">

                    <thead class="bg-gray-50">

                        <tr class="text-left text-gray-600">

                            <th class="p-4">
                                Paper
                            </th>

                            <th class="p-4">
                                Code
                            </th>

                            <th class="p-4">
                                Type
                            </th>

                            <th class="p-4">
                                Semester
                            </th>

                            <th class="p-4">
                                Total Students
                            </th>

                            <th class="p-4">
                                Approved
                            </th>

                            <th class="p-4">
                                Pending
                            </th>

                            <th class="p-4">
                                Rejected
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($paperAnalytics as $row)

                            <tr class="border-t hover:bg-gray-50 transition">

                                <td class="p-4">

                                    <div class="font-semibold text-gray-800">

                                        {{ $row->paper->name ?? '-' }}

                                    </div>

                                </td>

                                <td class="p-4 text-gray-500">

                                    {{ $row->paper->code ?? '-' }}

                                </td>

                                <td class="p-4">

                                    <span class="bg-indigo-100 text-indigo-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->paper->paper_type ?? '-' }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    Semester
                                    {{ $row->paper->semester ?? '-' }}

                                </td>

                                <td class="p-4">

                                    <span class="bg-blue-100 text-blue-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->total_students }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    <span class="bg-green-100 text-green-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->approved_count }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    <span class="bg-yellow-100 text-yellow-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->pending_count }}

                                    </span>

                                </td>

                                <td class="p-4">

                                    <span class="bg-red-100 text-red-700
                                                                px-3 py-1 rounded-full text-xs font-semibold">

                                        {{ $row->rejected_count }}

                                    </span>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8" class="text-center p-10 text-gray-500">

                                    No analytics found.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="p-5">

                {{ $paperAnalytics->links() }}

            </div>

        </div>

    @endif

    <!-- MODAL -->

    @if($showModal)

        <div class="fixed inset-0 z-50 bg-black/50
                        flex items-center justify-center p-5">

            <div class="bg-white rounded-3xl
                shadow-2xl w-full max-w-6xl
                h-[75vh] flex flex-col overflow-hidden">

                <!-- HEADER -->

                <div class="bg-gradient-to-r from-indigo-600 to-blue-600
        p-6 text-white flex items-center justify-between
        sticky top-0 z-20">

                    <div>

                        <h2 class="text-2xl font-bold">

                            {{ $selectedStudent->student->name ?? '-' }}

                        </h2>

                        <p class="text-indigo-700 mt-1">

                            Roll No :
                            {{ $selectedStudent->student->academic->roll_number ?? '-' }}

                        </p>

                    </div>

                    <button wire:click="$set('showModal', false)" class="text-3xl text-blue-700">

                        ×

                    </button>

                </div>

                <!-- ACTIONS -->

                <div class="p-5 border-b flex gap-3">

                    <button wire:click="approveAll" class="bg-green-600 hover:bg-green-700
                                    text-white px-5 py-3 rounded-2xl
                                    font-semibold transition">

                        Approve All

                    </button>

                    <button wire:click="rejectAll" class="bg-red-600 hover:bg-red-700
                                    text-white px-5 py-3 rounded-2xl
                                    font-semibold transition">

                        Reject All

                    </button>

                </div>

                <!-- SUBJECT TABLE -->

                <div class="flex-1 overflow-y-auto overflow-x-auto p-6 custom-scrollbar">

                    <table class="w-full">

                        <thead>

                            <tr class="border-b text-left text-gray-500">

                                <th class="pb-4">
                                    Subject
                                </th>

                                <th class="pb-4">
                                    Code
                                </th>

                                <th class="pb-4">
                                    Type
                                </th>

                                <th class="pb-4">
                                    Status
                                </th>

                                <th class="pb-4 text-right">
                                    Action
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach($studentSubjects as $subject)

                                <tr class="border-b hover:bg-gray-50">

                                    <td class="py-5 font-semibold text-gray-800">

                                        {{ $subject->paper->name ?? '-' }}

                                    </td>

                                    <td class="py-5 text-gray-500">

                                        {{ $subject->paper->code ?? '-' }}

                                    </td>

                                    <td class="py-5">

                                        <span class="bg-indigo-100 text-indigo-700
                                                                    px-3 py-1 rounded-full text-xs font-semibold">

                                            {{ $subject->paper->paper_type ?? '-' }}

                                        </span>

                                    </td>

                                    <td class="py-5">

                                        @if($subject->is_approved == 1)

                                            <span class="bg-green-100 text-green-700
                                                                                    px-3 py-1 rounded-full text-xs font-semibold">

                                                Approved

                                            </span>

                                        @elseif($subject->is_approved == 2)

                                            <span class="bg-red-100 text-red-700
                                                                                    px-3 py-1 rounded-full text-xs font-semibold">

                                                Rejected

                                            </span>

                                        @else

                                            <span class="bg-yellow-100 text-yellow-700
                                                                                    px-3 py-1 rounded-full text-xs font-semibold">

                                                Pending

                                            </span>

                                        @endif

                                    </td>

                                    <td class="py-5">

                                        <div class="flex justify-end gap-3">

                                            <button wire:click="approveSubject({{ $subject->id }})" class="bg-green-100 hover:bg-green-200
                                                                        text-green-700 px-4 py-2 rounded-xl
                                                                        text-sm font-semibold transition">

                                                Approve

                                            </button>

                                            <button wire:click="rejectSubject({{ $subject->id }})" class="bg-red-100 hover:bg-red-200
                                                                        text-red-700 px-4 py-2 rounded-xl
                                                                        text-sm font-semibold transition">

                                                Reject

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    @endif

</div>