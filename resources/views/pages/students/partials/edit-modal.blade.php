<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50">
    <div class="bg-white w-1/2 mx-auto mt-20 p-6 rounded">
        <h3 class="text-lg font-semibold mb-4">Edit Student</h3>

        <form method="POST" id="editForm">
            @csrf
            @method('PUT')

            <input class="border p-2 w-full mb-2" name="name">
            <input class="border p-2 w-full mb-2" name="mobile">
            <input class="border p-2 w-full mb-2" name="current_semester">

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()">Cancel</button>
                <button class="bg-blue-600 text-white px-4 py-2 rounded">Update</button>
            </div>
        </form>
    </div>
</div>
