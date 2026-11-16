<?php

namespace App\Support;

use App\Models\AparSubmission;
use Barryvdh\DomPDF\Facade\Pdf;

class AparPdfExporter
{
    public function download(AparSubmission $apar)
    {
        $apar->loadMissing(['faculty.department', 'reviewer']);

        return Pdf::loadView('pages.admin.apar.pdf', [
            'apar' => $apar,
            'steps' => AparFormSchema::steps(),
        ])
            ->setPaper('a4', 'portrait')
            ->download('APAR-'.$apar->academic_year.'-'.$apar->faculty_user_id.'.pdf');
    }
}
