<table class="w-full border rounded">
<thead class="bg-gray-100">
<tr>
    <th class="p-2">Roll No</th>
    <th>Name</th>
    <th class="text-center">Attendance</th>
</tr>
</thead>

<tbody>
@foreach($students as $row)
<tr class="border-t">
    <td class="p-2">{{ $row->student->roll_no }}</td>
    <td>{{ $row->student->name }}</td>
    <td class="text-center">
        <label>
            <input type="radio"
                   name="attendance[{{ $row->student_user_id }}]"
                   value="P"
                   {{ ($attendance[$row->student_user_id]->status ?? 'P') == 'P' ? 'checked' : '' }}>
            Present
        </label>

        <label class="ml-4">
            <input type="radio"
                   name="attendance[{{ $row->student_user_id }}]"
                   value="A"
                   {{ ($attendance[$row->student_user_id]->status ?? '') == 'A' ? 'checked' : '' }}>
            Absent
        </label>
    </td>
</tr>
@endforeach
</tbody>
</table>
