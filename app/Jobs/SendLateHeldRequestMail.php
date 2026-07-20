<?php

namespace App\Jobs;

use App\Models\LateHeldRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendLateHeldRequestMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $lateHeldRequestId,
        public string $mailType,
        public string $recipientEmail,
        public string $receiverName,
        public string $subject,
        public string $senderName,
        public bool $isLocalMail = false,
        public ?string $originalEmail = null,
    ) {}

    public function handle(): void
    {
        $lateHeldRequest = LateHeldRequest::with([
            'teacher',
            'substituteTeacher',
            'timetable.paper',
            'timetable.course',
            'timetable.room',
            'timetable.teacher',
            'actionBy',
        ])->find($this->lateHeldRequestId);

        if (!$lateHeldRequest) {
            return;
        }

        Mail::send('emails.late-held-request', [
            'lateHeldRequest' => $lateHeldRequest,
            'mailType' => $this->mailType,
            'receiverName' => $this->receiverName,
            'senderName' => $this->senderName,
            'requestUrl' => route('outstanding-actions.index') . '#request-' . $lateHeldRequest->id,
            'isLocalMail' => $this->isLocalMail,
            'originalEmail' => $this->originalEmail,
        ], fn ($message) => $message->to($this->recipientEmail, $this->receiverName)->subject($this->subject));
    }
}
