<?php

use App\Http\Controllers\Teacher\AparController;
use App\Livewire\Teacher\AparForm;
use App\Models\AparSubmission;
use App\Models\Teacher;
use App\Support\AparAcademicYear;
use App\Support\AparFormSchema;
use App\Support\AparPdfExporter;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function () {
    Schema::dropIfExists('apar_submissions');
    Schema::dropIfExists('faculty_users');
    Schema::dropIfExists('departments');

    Schema::create('departments', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->timestamps();
    });

    Schema::create('faculty_users', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('password');
        $table->string('designation')->nullable();
        $table->unsignedBigInteger('department_id')->nullable();
        $table->boolean('status')->default(1);
        $table->softDeletes();
        $table->timestamps();
    });

    $migration = require base_path('database/migrations/2026_11_14_000001_create_apar_submissions_table.php');
    $migration->up();

    $departmentId = DB::table('departments')->insertGetId([
        'name' => 'Computer Science',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->teacher = Teacher::create([
        'name' => 'APAR Faculty',
        'email' => 'apar-faculty@example.test',
        'password' => bcrypt('password'),
        'designation' => 'Assistant Professor',
        'department_id' => $departmentId,
        'status' => 1,
    ]);
});

afterEach(function () {
    Schema::dropIfExists('apar_submissions');
    Schema::dropIfExists('faculty_users');
    Schema::dropIfExists('departments');
});

test('a faculty member can persist an annual APAR draft', function () {
    $this->actingAs($this->teacher, 'teacher');

    $form = app(AparForm::class);
    $currentYear = AparAcademicYear::current();
    $form->mount($currentYear);
    $form->formData['personal']['name'] = 'APAR Faculty';
    $form->formData['academic_qualifications'][] = [
        'examination' => 'Ph.D.',
        'name' => 'Doctorate',
        'university' => 'University',
        'year' => '2024',
        'percentage' => '',
        'division' => '',
        'subjects' => 'Computer Science',
    ];

    $response = $form->saveDraft();

    $submission = AparSubmission::query()->firstOrFail();
    expect($submission->faculty_user_id)->toBe($this->teacher->id)
        ->and($submission->status)->toBe('draft')
        ->and($submission->form_data['academic_qualifications'][0]['name'])->toBe('Doctorate')
        ->and($response->headers->get('location'))->toBe(route('teacher.apar'));

    $otherForm = app(AparForm::class);
    $otherForm->mount($form->availableYears[0]);
    $otherForm->saveDraft();

    expect(AparSubmission::query()->count())->toBe(2);

    $resumeForm = app(AparForm::class);
    $resumeForm->mount($currentYear, $submission->id);
    expect(data_get($resumeForm->formData, 'academic_qualifications.0.name'))->toBe('Doctorate');
});

test('a faculty member can download their APAR PDF', function () {
    $this->actingAs($this->teacher, 'teacher');
    $submission = AparSubmission::create([
        'faculty_user_id' => $this->teacher->id,
        'academic_year' => '2026-2027',
        'status' => 'draft',
        'form_data' => AparFormSchema::emptyData(),
    ]);

    $response = app(AparController::class)->pdf($submission, app(AparPdfExporter::class));

    expect($response->headers->get('content-type'))->toBe('application/pdf');
});

test("a faculty member cannot download another faculty member's APAR PDF", function () {
    $otherFaculty = Teacher::create([
        'name' => 'Other Faculty',
        'email' => 'other-faculty@example.test',
        'password' => bcrypt('password'),
        'department_id' => $this->teacher->department_id,
        'status' => 1,
    ]);
    $submission = AparSubmission::create([
        'faculty_user_id' => $otherFaculty->id,
        'academic_year' => '2026-2027',
        'status' => 'draft',
        'form_data' => AparFormSchema::emptyData(),
    ]);

    $this->actingAs($this->teacher, 'teacher');

    expect(fn () => app(AparController::class)->pdf($submission, app(AparPdfExporter::class)))
        ->toThrow(HttpException::class);
});

test('a faculty member only sees their APARs in the filtered index', function () {
    $this->actingAs($this->teacher, 'teacher');
    $ownSubmission = AparSubmission::create([
        'faculty_user_id' => $this->teacher->id,
        'academic_year' => '2026-2027',
        'status' => 'draft',
        'form_data' => AparFormSchema::emptyData(),
    ]);
    $otherFaculty = Teacher::create([
        'name' => 'Other Faculty',
        'email' => 'other-faculty@example.test',
        'password' => bcrypt('password'),
        'department_id' => $this->teacher->department_id,
        'status' => 1,
    ]);
    AparSubmission::create([
        'faculty_user_id' => $otherFaculty->id,
        'academic_year' => '2026-2027',
        'status' => 'draft',
        'form_data' => AparFormSchema::emptyData(),
    ]);
    request()->query->set('status', 'draft');

    $view = app(AparController::class)->index(request());
    $submissions = $view->getData()['submissions'];

    expect($view->getName())->toBe('pages.teacher.apar')
        ->and($submissions->pluck('id')->all())->toBe([$ownSubmission->id]);
});
