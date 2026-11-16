<?php

use App\Models\AparSubmission;
use App\Support\AparFormSchema;
use Barryvdh\DomPDF\Facade\Pdf;

test('an APAR with empty optional sections can be exported as a PDF', function () {
    $apar = new AparSubmission([
        'academic_year' => '2026-2027',
        'status' => 'submitted',
        'form_data' => AparFormSchema::emptyData(),
    ]);

    $output = Pdf::loadView('pages.admin.apar.pdf', [
        'apar' => $apar,
        'steps' => AparFormSchema::steps(),
    ])->output();

    expect($output)->toStartWith('%PDF');
});
