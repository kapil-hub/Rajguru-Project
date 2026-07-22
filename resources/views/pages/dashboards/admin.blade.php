@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-2">

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Admin Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Quick overview of students, teachers, and subjects.</p>
    </div>

    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/[0.045]">
            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M16 11C17.6569 11 19 9.65685 19 8C19 6.34315 17.6569 5 16 5M8 11C6.34315 11 5 9.65685 5 8C5 6.34315 6.34315 5 8 5C9.65685 5 11 6.34315 11 8C11 9.65685 9.65685 11 8 11ZM8 14C5.23858 14 3 16.2386 3 19V20H13V19C13 16.2386 10.7614 14 8 14ZM16 14C18.2091 14 20 15.7909 20 18V20H16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Students</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($students ?? 0) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/[0.045]">
            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 14C15.866 14 19 12.2091 19 10V6L12 3L5 6V10C5 12.2091 8.13401 14 12 14ZM12 14V21M8 18H16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Total Teachers</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($teachers ?? 0) }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md dark:border-white/10 dark:bg-white/[0.045]">
            <div class="mb-5 flex h-11 w-11 items-center justify-center rounded-lg bg-amber-50 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M5 4.5H17C18.1046 4.5 19 5.39543 19 6.5V19.5H7C5.89543 19.5 5 18.6046 5 17.5V4.5ZM5 4.5V16.5C5 15.3954 5.89543 14.5 7 14.5H19" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Subjects</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($papers ?? 0) }}</p>
        </div>
    </div>
</div>
@endsection
