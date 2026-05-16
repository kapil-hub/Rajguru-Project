<div>

    {{-- Button --}}
    <button wire:click="toggle" type="button"
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md transition">
        Manage Roles
    </button>

    {{-- MODAL --}}
    @if($open)

        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/40 z-40" wire:click="toggle"></div>

        {{-- Modal Box --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div class="w-full max-w-md bg-white rounded-3xl shadow-2xl overflow-hidden" wire:click.stop>

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b">

                    <div>
                        <h2 class="text-xl font-bold text-gray-800">
                            Manage Roles
                        </h2>

                        <p class="text-sm text-gray-500 mt-1">
                            Select roles for this user
                        </p>
                    </div>

                    <button wire:click="toggle"
                        class="w-9 h-9 rounded-full bg-gray-100 hover:bg-red-100 text-gray-600 hover:text-red-600 transition">
                        ✕
                    </button>

                </div>

                {{-- Body --}}
                <div class="p-6">

                    <div class="space-y-3 max-h-80 overflow-y-auto pr-1">

                        @forelse($roles as $role)

                            <label
                                class="flex items-center justify-between border rounded-2xl px-4 py-3 cursor-pointer hover:bg-indigo-50 transition">

                                <div class="flex items-center gap-3">

                                    <input type="checkbox" value="{{ $role->id }}" wire:model="selectedRoles"
                                        class="w-5 h-5 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">

                                    <span class="font-medium text-gray-700">
                                        {{ $role->name }}
                                    </span>

                                </div>

                            </label>

                        @empty

                            <div class="text-center text-gray-500 py-6">
                                No roles found
                            </div>

                        @endforelse

                    </div>

                    {{-- Footer --}}
                    <div class="mt-6 flex gap-3">

                        <button wire:click="toggle"
                            class="w-1/2 py-3 rounded-2xl border border-gray-300 hover:bg-gray-100 font-medium">
                            Cancel
                        </button>

                        <button wire:click="save"
                            class="w-1/2 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white font-semibold shadow">
                            Save Roles
                        </button>

                    </div>

                    {{-- Success --}}
                    @if(session()->has('message'))

                        <div class="mt-4 p-3 rounded-xl bg-green-100 text-green-700 text-sm">

                            {{ session('message') }}

                        </div>

                    @endif

                </div>

            </div>

        </div>

    @endif

</div>