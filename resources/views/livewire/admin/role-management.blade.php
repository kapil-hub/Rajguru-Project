<div class="max-w-7xl mx-auto">

    {{-- HEADER --}}
    <div class="bg-white rounded-3xl shadow-xl p-6 border-l-8 border-indigo-600">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

            <div>

                <h2 class="text-3xl font-bold text-gray-800">
                    Role Management
                </h2>

                <p class="text-gray-500 mt-1">
                    Manage system roles and permissions
                </p>

            </div>

            <button wire:click="openModal"
                class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl shadow-lg">
                + Add Role
            </button>

        </div>

        {{-- SEARCH --}}
        <div class="mb-5">

            <input type="text" wire:model.live="search" placeholder="Search role..."
                class="w-full md:w-96 border border-gray-300 rounded-2xl px-4 py-3 focus:ring-2 focus:ring-indigo-500">

        </div>

        {{-- SUCCESS --}}
        @if(session()->has('success'))

            <div class="mb-5 p-4 bg-green-100 text-green-700 rounded-2xl">

                {{ session('success') }}

            </div>

        @endif

        {{-- TABLE --}}
        <div class="overflow-x-auto">

            <table class="min-w-full">

                <thead>

                    <tr class="border-b bg-gray-50">

                        <th class="text-left px-5 py-4">
                            #
                        </th>

                        <th class="text-left px-5 py-4">
                            Role Name
                        </th>

                        <th class="text-left px-5 py-4">
                            Slug
                        </th>

                        <th class="text-left px-5 py-4">
                            Description
                        </th>

                        <th class="text-center px-5 py-4">
                            Actions
                        </th>

                    </tr>

                </thead>

                <tbody>

                    @forelse($roles as $index => $role)

                        <tr class="border-b hover:bg-gray-50">

                            <td class="px-5 py-4">
                                {{ $index + 1 }}
                            </td>

                            <td class="px-5 py-4 font-semibold">
                                {{ $role->name }}
                            </td>

                            <td class="px-5 py-4">
                                {{ $role->slug }}
                            </td>

                            <td class="px-5 py-4 text-gray-600">
                                {{ $role->description }}
                            </td>

                            <td class="px-5 py-4 text-center">

                                <div class="flex justify-center gap-2">

                                    <button wire:click="edit({{ $role->id }})"
                                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl">
                                        Edit
                                    </button>

                                    <button wire:click="delete({{ $role->id }})" wire:confirm="Are you sure?"
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl">
                                        Delete
                                    </button>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="text-center py-10 text-gray-500">

                                No roles found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- MODAL --}}
    @if($showModal)

        <div class="fixed inset-0 bg-black/40 z-40" wire:click="closeModal"></div>

        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">

            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg" wire:click.stop>

                {{-- HEADER --}}
                <div class="flex items-center justify-between p-6 border-b">

                    <div>

                        <h2 class="text-2xl font-bold">

                            {{ $editId ? 'Edit Role' : 'Add Role' }}

                        </h2>

                        <p class="text-gray-500 text-sm mt-1">

                            Fill role information

                        </p>

                    </div>

                    <button wire:click="closeModal" class="w-10 h-10 rounded-full hover:bg-red-100">
                        ✕
                    </button>

                </div>

                {{-- BODY --}}
                <div class="p-6 space-y-5">

                    {{-- NAME --}}
                    <div>

                        <label class="font-medium">
                            Role Name
                        </label>

                        <input type="text" wire:model="name" class="w-full mt-2 border rounded-2xl px-4 py-3">

                        @error('name')

                            <div class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                    {{-- SLUG --}}
                    <div>

                        <label class="font-medium">
                            Slug
                        </label>

                        <select wire:model="slug" class="w-full mt-2 border rounded-2xl px-4 py-3">
                            <option value="">Select Slug</option>
                            @foreach ($routes[0]['items'] as $r)
                                @if(isset($r["description"]))
                                    <option value="{{ $r["path"] }}">{{ $r["description"] }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- DESCRIPTION --}}
                    <div>

                        <label class="font-medium">
                            Description
                        </label>

                        <textarea wire:model="description" rows="4"
                            class="w-full mt-2 border rounded-2xl px-4 py-3"></textarea>

                    </div>

                    {{-- FOOTER --}}
                    <div class="flex gap-3 pt-2">

                        <button wire:click="closeModal" class="w-1/2 py-3 border rounded-2xl hover:bg-gray-100">
                            Cancel
                        </button>

                        <button wire:click="save"
                            class="w-1/2 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl shadow-lg">
                            Save Role
                        </button>

                    </div>

                </div>

            </div>

        </div>

    @endif

</div>