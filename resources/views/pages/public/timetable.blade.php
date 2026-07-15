@php
    $timeSlots = \App\Models\TimetableSlot::orderBy('start_time')
        ->get()
        ->map(fn($s) => $s->formatted_slot)
        ->toArray();
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
@endphp
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Public Timetable | College Portal</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite Assets (Tailwind CSS v4 & Alpine) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            background-image: radial-gradient(at 0% 0%, rgba(243, 244, 246, 0.5) 0, transparent 50%), 
                              radial-gradient(at 50% 0%, rgba(229, 231, 235, 0.3) 0, transparent 50%), 
                              radial-gradient(at 100% 0%, rgba(243, 244, 246, 0.5) 0, transparent 50%);
        }

        /* Print stylesheet optimization */
        @media print {
            body {
                background: white !important;
                color: black !important;
                font-size: 10px !important;
            }
            .no-print {
                display: none !important;
            }
            .print-full {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }
            .print-grid {
                display: table !important;
                width: 100% !important;
                border-collapse: collapse !important;
            }
            .print-row {
                display: table-row !important;
            }
            .print-cell {
                display: table-cell !important;
                border: 1px solid #cbd5e1 !important;
                padding: 6px !important;
            }
            .card-badge {
                border: 1px solid #94a3b8 !important;
                background: transparent !important;
                color: black !important;
            }
        }
    </style>
</head>
<body class="h-full text-slate-800 antialiased" x-data="publicTimetable()" x-cloak>

    <!-- Header Section (no-print) -->
    <header class="no-print relative overflow-hidden bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900  px-6 text-white shadow-lg">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-indigo-500/20 via-transparent to-transparent"></div>
        <div class="relative max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-white/10 backdrop-blur-md rounded-2xl border border-white/20 shadow-inner">
                    <svg class="h-8 w-8 text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <img src="/images/logo/logo.png" width="150" height="70">
                    <p class="text-indigo-300 text-sm font-medium mt-0.5 tracking-wide">Timetable & Schedule</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Print Button -->
                <button @click="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-white/10 hover:bg-white/20 border border-white/10 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 shadow-sm active:translate-y-0">
                    <svg class="h-4.5 w-4.5 text-indigo-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    <span>Print / PDF</span>
                </button>
                <!-- Share Button -->
                <button @click="copyShareLink()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-semibold text-sm transition-all duration-200 hover:-translate-y-0.5 shadow-md hover:shadow-indigo-500/20 active:translate-y-0">
                    <span x-text="shareText">Share URL</span>
                    <svg class="h-4.5 w-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.684 10.742l4.744-2.372m0 0a3 3 0 102.242-5.83 3 3 0 00-2.242 5.83M8.684 13.258l4.744 2.372m0 0a3 3 0 102.242 5.83 3 3 0 00-2.242-5.83" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="max-w-7xl mx-auto px-4 py-6 md:px-6 md:py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

            <!-- Filters Panel (no-print) -->
            <section class="no-print lg:col-span-1 space-y-6">
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                        <h2 class="text-md font-bold text-slate-900 tracking-wide uppercase">Filters</h2>
                        <button @click="resetFilters()" class="text-xs font-semibold text-rose-600 hover:text-rose-700 transition duration-150">
                            Reset All
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Department -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Department</label>
                            <div class="relative">
                                <select x-model="activeFilters.department" @change="onDepartmentChange()" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:bg-white transition duration-200 appearance-none">
                                    <option value="">Select Department</option>
                                    <template x-for="dept in filterOptions.departments" :key="dept.id">
                                        <option :value="dept.id" x-text="dept.name"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Course -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Course</label>
                            <div class="relative">
                                <select x-model="activeFilters.course" @change="onFilterChange()" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:bg-white transition duration-200 appearance-none">
                                    <option value="">Select Course</option>
                                    <template x-for="c in filterOptions.courses" :key="c.id">
                                        <option :value="c.id" x-text="c.name"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Semester -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Semester</label>
                            <div class="relative">
                                <select x-model="activeFilters.semester" @change="onFilterChange()" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:bg-white transition duration-200 appearance-none">
                                    <option value="">Select Semester</option>
                                    <template x-for="sem in filterOptions.semesters" :key="sem">
                                        <option :value="sem" x-text="'Semester ' + sem"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Teacher -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Teacher</label>
                            <div class="relative">
                                <select x-model="activeFilters.teacher" @change="onFilterChange()" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:bg-white transition duration-200 appearance-none">
                                    <option value="">Select Teacher</option>
                                    <template x-for="t in filterOptions.teachers" :key="t.id">
                                        <option :value="t.id" x-text="t.name"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Room -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Room</label>
                            <div class="relative">
                                <select x-model="activeFilters.room" @change="onFilterChange()" class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 px-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:bg-white transition duration-200 appearance-none">
                                    <option value="">Select Room</option>
                                    <template x-for="r in filterOptions.rooms" :key="r.id">
                                        <option :value="r.id" x-text="r.room_number"></option>
                                    </template>
                                </select>
                                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                </div>
                            </div>
                        </div>

                        <!-- Text Search -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Search Keywords</label>
                            <div class="relative">
                                <input type="text" x-model="activeFilters.search" @input.debounce.300ms="onFilterChange()" placeholder="Teacher, Paper, Code..." class="w-full rounded-xl border border-slate-200 bg-slate-50/50 hover:bg-slate-50 pl-9 pr-3 py-2.5 text-sm outline-none focus:border-indigo-500 focus:bg-white transition duration-200">
                                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-slate-400">
                                    <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            <!-- Timetable Schedule Panel -->
            <section class="lg:col-span-3 space-y-6 print-full">

                <!-- Actions Header (no-print) -->
                <div class="no-print bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 bg-indigo-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-semibold text-slate-600" x-text="loading ? 'Refreshing schedule...' : (timetableData.length + ' slots found')"></span>
                    </div>

                    <!-- View Switcher -->
                    <div class="flex bg-slate-100 p-1 rounded-xl">
                        <button @click="viewMode = 'grid'" :class="viewMode === 'grid' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 rounded-lg font-semibold text-xs tracking-wide transition duration-150 flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                            <span>Grid View</span>
                        </button>
                        <button @click="viewMode = 'list'" :class="viewMode === 'list' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'" class="px-4 py-2 rounded-lg font-semibold text-xs tracking-wide transition duration-150 flex items-center gap-1.5">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                            <span>List View</span>
                        </button>
                    </div>
                </div>

                <!-- Loading State Skeletons -->
                <div x-show="loading" class="space-y-4">
                    <template x-for="i in 3" :key="i">
                        <div class="h-32 bg-slate-200/50 rounded-2xl animate-pulse"></div>
                    </template>
                </div>

                <!-- Welcome/Empty State when no search criteria matches or filters are blank -->
                <div x-show="!loading && timetableData.length === 0" class="bg-white border border-slate-200/80 rounded-2xl p-12 text-center shadow-sm">
                    <div class="mx-auto w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center border border-slate-100 mb-4">
                        <svg class="h-8 w-8 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">No Timetable Records Displayed</h3>
                    <p class="text-sm text-slate-500 mt-2 max-w-md mx-auto">Please adjust or expand your filter criteria on the left. You can search by Department, Course, Semester, or enter keyword searches directly.</p>
                </div>

                <!-- GRID TIMETABLE VIEW (Desktop & Print) -->
                <div x-show="!loading && timetableData.length > 0 && viewMode === 'grid'" class="bg-white border border-slate-200/80 rounded-2xl overflow-hidden shadow-sm print-full print-grid">
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-left text-sm print-full">
                            <thead>
                                <tr class="bg-slate-50/70 border-b border-slate-200 print-row">
                                    <th class="sticky left-0 bg-slate-50 border-r border-slate-200 px-6 py-4 font-bold text-slate-600 uppercase text-xs tracking-wider z-10 print-cell">
                                        Day
                                    </th>
                                    <template x-for="slot in timeSlots" :key="slot">
                                        <th class="px-5 py-4 border-r border-slate-200/80 text-center font-bold text-slate-600 uppercase text-xs tracking-wider print-cell min-w-[200px]" x-text="slot"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="day in days" :key="day">
                                    <tr class="print-row hover:bg-slate-50/40 transition duration-150">
                                        <td class="sticky left-0 bg-white border-r border-slate-200 px-6 py-8 font-extrabold text-slate-800 align-middle text-md z-10 shadow-[2px_0_5px_rgba(0,0,0,0.02)] print-cell" x-text="day"></td>
                                        
                                        <template x-for="slot in timeSlots" :key="slot">
                                            <td class="p-2 border-r border-slate-100 align-top h-36 min-w-[200px] print-cell">
                                                <div class="flex flex-col gap-2 h-full justify-start overflow-y-auto max-h-32 custom-scrollbar">
                                                    
                                                    <template x-for="item in getGridSlots(day, slot)" :key="item.id">
                                                        <div @click="openModal(item)" 
                                                             :style="{ borderLeftColor: item.color || '#6366f1' }"
                                                             class="group cursor-pointer flex flex-col justify-between rounded-xl border-l-4 border bg-slate-50/50 hover:bg-slate-50 border-slate-200/60 p-2.5 transition duration-150 hover:-translate-y-0.5 hover:shadow-sm">
                                                            
                                                            <div>
                                                                <!-- Paper name & code -->
                                                                <div class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider line-clamp-1" x-text="item.paper || 'N/A'"></div>
                                                                <div class="text-xs font-semibold text-slate-800 leading-snug line-clamp-1 mt-0.5" x-text="item.course || ''"></div>
                                                                <div class="text-[11px] text-slate-500 mt-1 line-clamp-1">
                                                                    Sem <span x-text="item.semester"></span>
                                                                    <template x-if="item.batches && item.batches.length > 0">
                                                                        <span class="ml-1 px-1 py-0.5 text-[9px] bg-slate-100 rounded text-slate-600 font-bold" x-text="'B: ' + item.batches.join(', ')"></span>
                                                                    </template>
                                                                </div>
                                                            </div>
                                                            
                                                            <!-- Footer / Tags -->
                                                            <div class="mt-2.5 pt-1.5 border-t border-slate-100 flex items-center justify-between gap-1 text-[10px] text-slate-500">
                                                                <span class="font-medium line-clamp-1" x-text="'📍 ' + (item.room || 'Room N/A')"></span>
                                                                
                                                                <!-- Type badges -->
                                                                <div class="flex gap-0.5">
                                                                    <template x-if="item.lecture"><span class="px-1 py-0.2 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded font-extrabold text-[9px] card-badge">L</span></template>
                                                                    <template x-if="item.tutorial"><span class="px-1 py-0.2 bg-amber-50 border border-amber-100 text-amber-700 rounded font-extrabold text-[9px] card-badge">T</span></template>
                                                                    <template x-if="item.practical"><span class="px-1 py-0.2 bg-emerald-50 border border-emerald-100 text-emerald-700 rounded font-extrabold text-[9px] card-badge">P</span></template>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </template>

                                                </div>
                                            </td>
                                        </template>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- LIST / TIMELINE VIEW (Mobile Default) -->
                <div x-show="!loading && timetableData.length > 0 && viewMode === 'list'" class="space-y-4 print-full">
                    <template x-for="day in days" :key="day">
                        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden" x-data="{ expanded: true }">
                            
                            <!-- Header click toggle -->
                            <button @click="expanded = !expanded" class="w-full px-5 py-4 flex items-center justify-between bg-slate-50/40 hover:bg-slate-50 border-b border-slate-100 transition duration-150">
                                <div class="flex items-center gap-3">
                                    <h3 class="font-bold text-slate-800 text-sm tracking-wide uppercase" x-text="day"></h3>
                                    <span class="px-2 py-0.5 text-xs bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-full font-bold" x-text="getGridSlots(day, '').length + ' classes'"></span>
                                </div>
                                <svg class="h-5 w-5 text-slate-400 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            
                            <!-- Day timeline -->
                            <div x-show="expanded" x-collapse class="p-4 space-y-3">
                                
                                <!-- If no classes on this day -->
                                <template x-if="getGridSlots(day, '').length === 0">
                                    <p class="text-xs text-slate-400 italic text-center py-2">No schedules assigned for this day.</p>
                                </template>
                                
                                <!-- Card iteration -->
                                <template x-for="item in getGridSlots(day, '')" :key="item.id">
                                    <div @click="openModal(item)" 
                                         :style="{ borderLeftColor: item.color || '#6366f1' }"
                                         class="cursor-pointer border-l-4 border border-slate-200/60 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-3 bg-slate-50/50 hover:bg-slate-50 transition duration-150">
                                        
                                        <div class="flex items-start gap-4">
                                            <!-- Time badge -->
                                            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-3 text-center min-w-[90px]">
                                                <div class="text-xs font-extrabold text-indigo-700" x-text="item.start"></div>
                                                <div class="text-[10px] text-slate-400 mt-0.5" x-text="'to ' + item.end"></div>
                                            </div>
                                            
                                            <!-- Course, Paper details -->
                                            <div>
                                                <div class="text-xs font-bold text-slate-400 uppercase tracking-wider" x-text="item.paper || 'N/A'"></div>
                                                <h4 class="text-sm font-bold text-slate-800 mt-0.5" x-text="item.course || ''"></h4>
                                                <div class="flex flex-wrap items-center gap-2 mt-1.5 text-xs text-slate-500">
                                                    <span>Sem <span x-text="item.semester"></span></span>
                                                    <span>•</span>
                                                    <span class="font-medium" x-text="'👤 ' + (item.teacher || 'Unknown Faculty')"></span>
                                                    <span>•</span>
                                                    <span class="font-medium" x-text="'📍 ' + (item.room || 'Room N/A')"></span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Batches and Type Badges -->
                                        <div class="flex items-center gap-2 self-start md:self-center">
                                            <template x-if="item.batches && item.batches.length > 0">
                                                <span class="px-2 py-0.5 bg-slate-100 rounded text-slate-600 font-bold text-xs" x-text="'Batch: ' + item.batches.join(', ')"></span>
                                            </template>
                                            <template x-if="item.lecture"><span class="px-2.5 py-0.5 bg-indigo-50 text-indigo-700 border border-indigo-100 rounded-full font-bold text-xs">Lecture</span></template>
                                            <template x-if="item.tutorial"><span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 border border-amber-100 rounded-full font-bold text-xs">Tutorial</span></template>
                                            <template x-if="item.practical"><span class="px-2.5 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-100 rounded-full font-bold text-xs">Practical</span></template>
                                        </div>

                                    </div>
                                </template>
                                
                            </div>
                        </div>
                    </template>
                </div>

            </section>
        </div>
    </main>

    <!-- Details Modal Popup (no-print) -->
    <div x-show="modalOpen" 
         class="no-print fixed inset-0 z-99999 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @keydown.escape.window="modalOpen = false">
        
        <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200/80 overflow-hidden transform"
             @click.away="modalOpen = false"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Modal Header (Type Colored) -->
            <div class="p-6 text-white" :style="{ backgroundColor: activeSlot.color || '#6366f1' }">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider font-extrabold bg-white/20 px-2.5 py-0.5 rounded-full" x-text="getSlotTypeLabel(activeSlot)"></span>
                    <button @click="modalOpen = false" class="text-white hover:text-slate-100">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
                <h3 class="text-xl font-bold mt-4 leading-tight" x-text="activeSlot.course || ''"></h3>
                <p class="text-white/80 text-sm mt-1" x-text="activeSlot.paper || ''"></p>
            </div>

            <!-- Modal Content Body -->
            <div class="p-6 space-y-4">
                
                <!-- Day / Time -->
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 p-2 bg-slate-50 rounded-xl text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase">Schedule Slot</div>
                        <div class="text-sm font-bold text-slate-800 mt-0.5" x-text="activeSlot.day + ', ' + activeSlot.start + ' - ' + activeSlot.end"></div>
                    </div>
                </div>

                <!-- Teacher -->
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 p-2 bg-slate-50 rounded-xl text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase">Faculty / Teacher</div>
                        <div class="text-sm font-bold text-slate-800 mt-0.5" x-text="activeSlot.teacher || 'No Faculty Assigned'"></div>
                    </div>
                </div>

                <!-- Room -->
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 p-2 bg-slate-50 rounded-xl text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase">Room & Location</div>
                        <div class="text-sm font-bold text-slate-800 mt-0.5" x-text="activeSlot.room || 'No Room Allocated'"></div>
                    </div>
                </div>

                <!-- Department -->
                <div class="flex items-start gap-3">
                    <div class="mt-0.5 p-2 bg-slate-50 rounded-xl text-slate-400">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                    </div>
                    <div>
                        <div class="text-xs font-semibold text-slate-400 uppercase">Department</div>
                        <div class="text-sm font-bold text-slate-800 mt-0.5" x-text="activeSlot.department || 'N/A'"></div>
                    </div>
                </div>

                <!-- Batches -->
                <template x-if="activeSlot.batches && activeSlot.batches.length > 0">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 p-2 bg-slate-50 rounded-xl text-slate-400">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        </div>
                        <div>
                            <div class="text-xs font-semibold text-slate-400 uppercase">Registered Batches</div>
                            <div class="text-sm font-bold text-slate-800 mt-0.5" x-text="activeSlot.batches.join(', ')"></div>
                        </div>
                    </div>
                </template>

            </div>

            <!-- Modal Footer -->
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button @click="modalOpen = false" class="px-5 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-sm font-bold transition duration-150">
                    Close Details
                </button>
            </div>

        </div>
    </div>

    <!-- Alpine Timetable App Handler -->
    <script>
        function publicTimetable() {
            return {
                config: {},
                filterOptions: {
                    departments: [],
                    courses: [],
                    papers: [],
                    teachers: [],
                    rooms: [],
                    semesters: []
                },
                activeFilters: {
                    department: '',
                    course: '',
                    semester: '',
                    teacher: '',
                    room: '',
                    search: ''
                },
                timetableData: [],
                timeSlots: @json($timeSlots),
                days: @json($days),
                loading: false,
                viewMode: 'grid',
                modalOpen: false,
                activeSlot: {},
                shareText: 'Share URL',

                async init() {
                    // Detect screen size on load for appropriate viewMode
                    if (window.innerWidth < 1024) {
                        this.viewMode = 'list';
                    }
                    
                    this.loading = true;
                    try {
                        // Parallel load of config & filter lists
                        const [configRes, filtersRes] = await Promise.all([
                            fetch('/api/public/config').then(r => r.json()),
                            fetch('/api/public/timetable/filters').then(r => r.json())
                        ]);

                        if (configRes.success) this.config = configRes.data;
                        if (filtersRes.success) this.filterOptions = filtersRes.data;
                        
                        // Parse search URL params (deep-linking)
                        const params = new URLSearchParams(window.location.search);
                        let hasQueryParams = false;
                        for (const key in this.activeFilters) {
                            if (params.has(key)) {
                                this.activeFilters[key] = params.get(key);
                                hasQueryParams = true;
                            }
                        }

                        // If deep-linked parameters are found, fetch the matching rows automatically
                        if (hasQueryParams) {
                            await this.fetchTimetable();
                        }
                    } catch (e) {
                        console.error("Failed to load initial data", e);
                    } finally {
                        this.loading = false;
                    }
                },

                async fetchTimetable() {
                    this.loading = true;
                    try {
                        const params = new URLSearchParams();
                        for (const key in this.activeFilters) {
                            if (this.activeFilters[key]) {
                                params.append(key, this.activeFilters[key]);
                            }
                        }

                        // Fetch the filtered records
                        const res = await fetch(`/api/public/timetable?${params.toString()}`);
                        const result = await res.json();
                        
                        if (result.success) {
                            this.timetableData = result.data;
                        }
                    } catch (e) {
                        console.error("Failed to fetch timetable", e);
                    } finally {
                        this.loading = false;
                    }
                },

                // When filters change
                async onFilterChange() {
                    this.syncUrlParams();
                    await this.fetchTimetable();
                },

                // Reset filters to defaults
                async resetFilters() {
                    for (const key in this.activeFilters) {
                        this.activeFilters[key] = '';
                    }
                    this.syncUrlParams();
                    this.timetableData = [];
                },

                // Handle department change specifically
                async onDepartmentChange() {
                    await this.onFilterChange();
                },

                // Sync UI filters to browser URL params
                syncUrlParams() {
                    const params = new URLSearchParams();
                    for (const key in this.activeFilters) {
                        if (this.activeFilters[key]) {
                            params.append(key, this.activeFilters[key]);
                        }
                    }
                    const newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
                    window.history.replaceState({}, '', newUrl);
                },

                // Filter slot data for a specific day and/or slot cell
                getGridSlots(day, slot) {
                    return this.timetableData.filter(item => {
                        const dayMatch = item.day.toLowerCase() === day.toLowerCase();
                        if (!dayMatch) return false;
                        
                        if (slot) {
                            // Match against slot range formatted string
                            const itemSlot = `${item.start}-${item.end}`;
                            return itemSlot === slot;
                        }
                        
                        return true;
                    });
                },

                // Show details popup
                openModal(item) {
                    this.activeSlot = item;
                    this.modalOpen = true;
                },

                getSlotTypeLabel(item) {
                    if (item.lecture) return 'Lecture';
                    if (item.tutorial) return 'Tutorial';
                    if (item.practical) return 'Practical';
                    if (item.coordinator) return 'Coordinator';
                    return 'Class Slot';
                },

                // Copy shareable link to clipboard
                copyShareLink() {
                    const url = window.location.href;
                    navigator.clipboard.writeText(url).then(() => {
                        this.shareText = 'Copied!';
                        setTimeout(() => this.shareText = 'Share URL', 2000);
                    }).catch(err => {
                        console.error('Could not copy text: ', err);
                    });
                }
            };
        }
    </script>
</body>
</html>
