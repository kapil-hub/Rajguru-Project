<div>

    {{-- Button --}}
    <button wire:click="toggle" type="button"
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl shadow-md transition">
        Manage Roles
    </button>

    {{-- MODAL --}}
    @if($open)
    @teleport('body')
        <div
            x-data
            class="fixed inset-0 z-[99999] flex items-center justify-center">

            <!-- Backdrop -->
            <div
                class="absolute inset-0 bg-black/50"
                wire:click="toggle">
            </div>

            <!-- Modal -->
            <div
                class="relative bg-white rounded-3xl shadow-2xl w-[500px] max-w-[90vw] p-6"
                wire:click.stop>

                <div class="flex justify-between items-center mb-5">
                    <h2 class="text-xl font-bold">Manage Roles</h2>

                    <button wire:click="toggle">
                        ✕
                    </button>
                </div>

                <div class="space-y-3 max-h-80 overflow-y-auto">
                    @foreach($roles as $role)
                        <label class="flex items-center gap-3">
                            <input
                                type="checkbox"
                                value="{{ $role->id }}"
                                wire:model="selectedRoles">

                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>

                <div class="flex gap-3 mt-6">
                    <button
                        wire:click="toggle"
                        class="flex-1 border rounded-lg py-2">
                        Cancel
                    </button>

                    <button
                        wire:click="save"
                        class="flex-1 bg-green-600 text-white rounded-lg py-2">
                        Save
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
    @endteleport
@endif

</div>