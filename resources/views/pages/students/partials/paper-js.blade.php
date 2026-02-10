<script>
let paperIndex = 1000;

function addPaper(isBacklog = false) {

    let container = isBacklog
        ? document.getElementById('backlog-papers')
        : document.getElementById('current-papers');

    let semesterField = isBacklog
        ? `<div class="w-32">
                <input type="number"
                       name="papers[${paperIndex}][semester]"
                       placeholder="Backlog Sem"
                       class="border rounded-lg p-2 w-full">
           </div>`
        : '';

    let html = `
        <div class="flex items-center gap-3 bg-white p-3 rounded-lg shadow-sm">
            <input type="hidden"
                   name="papers[${paperIndex}][is_backlog]"
                   value="${isBacklog ? 1 : 0}">

            <div class="flex-1">
                <select name="papers[${paperIndex}][paper_id]"
                        class="border rounded-lg p-2 w-full">
                    <option value="">Select Paper</option>
                    @foreach($allPapers as $p)
                        <option value="{{ $p->id }}">
                            {{ $p->name }} ({{ $p->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            ${semesterField}

            <button type="button"
                    onclick="this.closest('.flex').remove()"
                    class="text-red-600 text-xl">🗑️</button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', html);
    paperIndex++;
}
</script>
<script>
function initPaperSelect(container = document) {

    container.querySelectorAll('.paper-select').forEach(select => {

        if (select.tomselect) return; // prevent double init

        let ts = new TomSelect(select, {
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            },
            onChange(value) {
                let option = select.querySelector(`option[value="${value}"]`);
                let semester = option?.dataset.semester || '';

                let row = select.closest('.paper-row');
                let semBox = row.querySelector('.semester-box');

                if (semBox) {
                    semBox.value = semester;
                }

                validateDuplicatePapers();
            }
        });
    });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    initPaperSelect();
});
</script>
<script>
function validateDuplicatePapers() {

    let selected = [];
    let hasDuplicate = false;

    document.querySelectorAll('.paper-select').forEach(sel => {
        if (sel.value) {
            if (selected.includes(sel.value)) {
                hasDuplicate = true;
                sel.tomselect.control.classList.add('border-red-500');
            } else {
                selected.push(sel.value);
                sel.tomselect.control.classList.remove('border-red-500');
            }
        }
    });

    if (hasDuplicate) {
        alert('Same paper cannot be assigned twice to a student.');
    }
}

function removePaper(btn) {
    btn.closest('.paper-row').remove();
    validateDuplicatePapers();
}
</script>