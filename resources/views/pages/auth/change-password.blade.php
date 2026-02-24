@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white p-6 rounded-2xl shadow-md p-6 mb-6 border-l-8 border-indigo-600">

    <h2 class="text-xl font-semibold text-center mb-4">
        Change Password
    </h2>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-2 rounded mb-3 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <div class="mb-3">
            <label class="text-sm">Current Password</label>
            <input type="password" name="current_password"
                class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-3">
            <label class="text-sm">New Password</label>
            <input type="password" name="new_password"
                class="w-full border rounded px-3 py-2" required>
        </div>

        <div class="mb-4">
            <label class="text-sm">Confirm New Password</label>
            <input type="password" name="new_password_confirmation"
                class="w-full border rounded px-3 py-2" required>
        </div>

        <button class="w-full bg-indigo-600 text-white py-2 rounded hover:bg-indigo-700">
            Update Password
        </button>
    </form>

</div>
@endsection
