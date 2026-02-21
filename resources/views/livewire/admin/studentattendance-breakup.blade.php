
    <div class="mt-4 border-t pt-4">
    @if($show)
        <table class="w-full text-sm">
            <thead class="text-gray-500">
            <tr>
                <th class="text-left">Paper</th>
                <th>Lecture Held</th>
                <th >Lecture Attended</th>
                <th>Lecture %</th>
                <th class="text-orange-500">Tutorial Held</th>
                <th class="text-orange-500">Tutorial Attended</th>
                <th class="text-orange-500">Tutorial %</th>
                <th class="text-yellow-500">Practical Held</th>
                <th class="text-yellow-500">Practical Attended</th>
                <th class="text-yellow-500">Practical %</th>
            </tr>
            </thead>
            <tbody>
            @foreach($papers ?? [] as $p)
                <tr class="border-t">
                    <td class="py-2 font-medium">{{ $p->paper_name }}</td>
                    <td class="text-center border-l-2 border-indigo-500 p-4">
                        {{ $p->lecture_working_days ?? 0 }}
                    </td>
                    <td class="text-center">
                        {{ $p->lecture_present_days ?? 0 }}
                    </td>
                    <td class="text-center border-orange-500 border-r-2 border-indigo-500 p-4">
                        {{ $p->lecture_working_days > 0 ? round(($p->lecture_present_days/$p->lecture_working_days)*100,2).'%' : '-' }}
                    </td>
                    <td class="text-center">
                        {{ $p->tute_working_days ?? 0 }}
                    </td>
                    <td class="text-center">
                        {{ $p->tute_present_days ?? 0 }}
                    </td>
                    <td class="text-center border-yellow-500 border-r-2 p-4">
                        {{ $p->tute_working_days > 0 ? round(($p->tute_present_days/$p->tute_working_days)*100,2).'%' : '-' }}
                    </td>
                    <td class="text-center">
                        {{ $p->practical_working_days ?? 0 }}
                    </td>
                    <td class="text-center">
                        {{ $p->practical_present_days ?? 0 }}
                    </td>
                    <td class="text-center">
                        {{ $p->practical_working_days > 0 ? round(($p->practical_present_days/$p->practical_working_days)*100,2).'%' : '-' }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    @endif
</div>
