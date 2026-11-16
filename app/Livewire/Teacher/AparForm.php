<?php

namespace App\Livewire\Teacher;

use App\Models\AparSubmission;
use App\Support\AparAcademicYear;
use App\Support\AparFormSchema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use RuntimeException;

class AparForm extends Component
{
    use WithFileUploads;

    public array $formData = [];

    public array $evidenceFiles = [];

    #[Locked]
    public int $step = 0;

    #[Locked]
    public array $availableYears = [];

    #[Locked]
    public string $academicYear = '';

    #[Locked]
    public string $reviewNotes = '';

    #[Locked]
    public ?int $submissionId = null;

    #[Locked]
    public string $status = 'draft';

    public function mount(?string $academicYear = null, ?int $submissionId = null): void
    {
        $this->availableYears = AparAcademicYear::options();
        $this->academicYear = $academicYear ?? AparAcademicYear::current();
        abort_unless(in_array($this->academicYear, $this->availableYears, true), 404);
        if ($submissionId) {
            $this->submissionId = $submissionId;
        }

        $this->initializeFormData();

        if ($submissionId) {
            $this->loadSubmission($submissionId);
        } else {
            $this->loadSubmission();
        }
    }

    private function initializeFormData(): void
    {
        $this->formData = AparFormSchema::emptyData();
        $teacher = auth('teacher')->user()->load('department');
        data_set($this->formData, 'personal.name', $teacher->name);
        data_set($this->formData, 'personal.department', $teacher->department?->name ?? '');
        data_set($this->formData, 'personal.designation_level', $teacher->designation ?? '');
    }

    public function nextStep(): void
    {
        if ($this->isReadOnly()) {
            return;
        }

        $this->persist('draft');
        $this->step = min($this->step + 1, count(AparFormSchema::steps()) - 1);
        session()->flash('apar_message', 'Draft saved. You can continue whenever you are ready.');
    }

    public function previousStep(): void
    {
        $this->step = max(0, $this->step - 1);
    }

    public function goToStep(int $target): void
    {
        if ($target >= 0 && $target <= $this->step) {
            $this->step = $target;
        }
    }

    public function addRow(string $key): void
    {
        $fields = AparFormSchema::tableFields($key);
        abort_if($fields === null, 404);
        abort_if($this->isReadOnly(), 403);

        $row = [];
        foreach ($fields as $field) {
            $row[$field['key']] = '';
        }

        $rows = data_get($this->formData, $key, []);
        $rows[] = $row;
        data_set($this->formData, $key, $rows);
    }

    public function removeRow(string $key, int $index): void
    {
        abort_if(AparFormSchema::tableFields($key) === null, 404);
        abort_if($this->isReadOnly(), 403);

        $rows = data_get($this->formData, $key, []);
        if (array_key_exists($index, $rows)) {
            array_splice($rows, $index, 1);
            data_set($this->formData, $key, $rows);
        }
    }

    public function saveDraft()
    {
        if ($this->isReadOnly()) {
            return redirect()->route('teacher.apar')->with(
                'error',
                'This APAR has already been published and cannot be changed.'
            );
        }

        $this->persist('draft');

        return redirect()->route('teacher.apar')->with('success', 'Your draft has been saved.');
    }

    public function uploadEvidence(): void
    {
        if ($this->isReadOnly()) {
            abort(403);
        }

        $this->validate([
            'evidenceFiles' => ['required', 'array', 'max:15'],
            'evidenceFiles.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ]);
        if (count(data_get($this->formData, 'enclosures', [])) + count($this->evidenceFiles) > 15) {
            throw ValidationException::withMessages([
                'evidenceFiles' => 'You can attach up to 15 supporting files to an APAR.',
            ]);
        }

        $submission = $this->persist('draft');
        $attachments = data_get($submission->form_data, 'enclosures', []);

        foreach ($this->evidenceFiles as $file) {
            $path = $file->store('apar/'.$submission->id, 'local');
            if ($path === false) {
                throw new RuntimeException('An evidence file could not be saved. Please try again.');
            }

            $attachments[] = [
                'path' => $path,
                'name' => basename(str_replace('\\', '/', $file->getClientOriginalName())),
                'size' => $file->getSize(),
            ];
        }

        $this->formData['enclosures'] = $attachments;
        $submission->form_data = $this->formData;
        $submission->save();
        $this->evidenceFiles = [];

        session()->flash('apar_message', 'Evidence files uploaded and attached to your draft.');
    }

    public function removeEvidence(int $index): void
    {
        if ($this->isReadOnly()) {
            abort(403);
        }

        $submission = $this->ownSubmission();
        abort_if(! $submission, 404);
        $attachments = data_get($this->formData, 'enclosures', []);
        if (! array_key_exists($index, $attachments)) {
            return;
        }

        $path = $attachments[$index]['path'] ?? '';
        abort_unless(str_starts_with($path, 'apar/'.$submission->id.'/'), 404);
        if (Storage::disk('local')->exists($path) && ! Storage::disk('local')->delete($path)) {
            throw new RuntimeException('The evidence file could not be removed. Please try again.');
        }

        array_splice($attachments, $index, 1);
        $this->formData['enclosures'] = $attachments;
        $submission->form_data = $this->formData;
        $submission->save();
    }

    public function publish()
    {
        if ($this->isReadOnly()) {
            abort(403);
        }

        $this->validate([
            'formData.personal.name' => ['required', 'string', 'max:255'],
            'formData.personal.department' => ['required', 'string', 'max:255'],
            'formData.personal.designation_level' => ['required', 'string', 'max:255'],
            'formData.declaration.confirmed' => ['accepted'],
            'formData.declaration.date' => ['required', 'date'],
            'formData.declaration.place' => ['required', 'string', 'max:255'],
        ], [
            'formData.personal.name.required' => 'Enter your name before publishing.',
            'formData.personal.department.required' => 'Enter your department before publishing.',
            'formData.personal.designation_level.required' => 'Enter your current designation and academic level.',
            'formData.declaration.confirmed.accepted' => 'Please confirm the declaration before publishing.',
        ]);

        $futurePlans = trim((string) data_get($this->formData, 'other.future_plans', ''));
        $wordCount = count(preg_split('/\s+/u', $futurePlans, -1, PREG_SPLIT_NO_EMPTY) ?: []);
        if ($wordCount > 150) {
            throw ValidationException::withMessages([
                'formData.other.future_plans' => 'Future plans must be approximately 150 words or fewer.',
            ]);
        }

        $this->persist('submitted');

        return redirect()->route('teacher.apar')->with(
            'success',
            'Your APAR has been published and sent to the administration for review.'
        );
    }

    public function isReadOnly(): bool
    {
        return in_array($this->status, ['submitted', 'reviewed'], true);
    }

    public function render()
    {
        return view('livewire.teacher.apar-form', [
            'steps' => AparFormSchema::steps(),
        ]);
    }

    private function loadSubmission(?int $submissionId = null): void
    {
        $submission = $submissionId
            ? AparSubmission::query()
                ->where('faculty_user_id', auth('teacher')->id())
                ->where('academic_year', $this->academicYear)
                ->findOrFail($submissionId)
            : $this->ownSubmission();

        if (! $submission) {
            return;
        }

        $this->submissionId = $submission->id;
        $this->status = $submission->status;
        $this->reviewNotes = $submission->review_notes ?? '';
        $this->formData = array_replace_recursive(
            AparFormSchema::emptyData(),
            $submission->form_data ?? []
        );
    }

    private function ownSubmission(): ?AparSubmission
    {
        return AparSubmission::query()
            ->where('faculty_user_id', auth('teacher')->id())
            ->where('academic_year', $this->academicYear)
            ->first();
    }

    private function persist(string $status): AparSubmission
    {
        if (! in_array($this->academicYear, $this->availableYears, true)) {
            throw ValidationException::withMessages([
                'academicYear' => 'Select a valid academic year.',
            ]);
        }

        $submission = $this->ownSubmission() ?? new AparSubmission([
            'faculty_user_id' => auth('teacher')->id(),
            'academic_year' => $this->academicYear,
        ]);

        abort_if(in_array($submission->status, ['submitted', 'reviewed'], true), 403);

        if ($submission->exists && $submission->status === 'changes_requested' && $status === 'draft') {
            $status = 'changes_requested';
        }

        $submission->status = $status;
        $submission->form_data = $this->formData;
        if ($status === 'submitted') {
            $submission->submitted_at = now();
            $submission->reviewed_at = null;
            $submission->reviewed_by = null;
        }
        $submission->save();

        $this->submissionId = $submission->id;
        $this->status = $submission->status;
        $this->reviewNotes = $submission->review_notes ?? '';

        return $submission;
    }
}
