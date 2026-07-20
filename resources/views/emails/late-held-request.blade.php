@php
    $slot = $lateHeldRequest->timetable;
    $statusText = match($mailType) {
        'approved' => 'approved',
        'rejected' => 'rejected',
        default => 'submitted for approval',
    };
    $accent = match($mailType) {
        'approved' => '#16a34a',
        'rejected' => '#dc2626',
        default => '#2563eb',
    };
    $requestLabel = $lateHeldRequest->request_type === 'substitute_held'
        ? 'Substitute Lecture Request'
        : 'Late Held Class Request';
@endphp
<!doctype html>
<html>
<body style="margin:0;background:#f3f4f6;font-family:Arial,sans-serif;color:#111827;">
    <div style="max-width:680px;margin:0 auto;padding:24px;">
        <div style="background:#ffffff;border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;">
            <div style="padding:20px 24px;border-top:5px solid {{ $accent }};">
                <h1 style="margin:0;font-size:20px;color:#111827;">{{ $requestLabel }}</h1>
                <p style="margin:8px 0 0;color:#4b5563;font-size:14px;">
                    Request #{{ $lateHeldRequest->id }} has been {{ $statusText }}.
                </p>
            </div>

            <div style="padding:0 24px 24px;">
                <p style="font-size:15px;line-height:1.6;">Dear {{ $receiverName }},</p>

                @if($mailType === 'pending_approval')
                    <p style="font-size:15px;line-height:1.6;">
                        {{ $senderName }} has submitted a {{ strtolower($requestLabel) }} for your review.
                    </p>
                @elseif($mailType === 'approved')
                    <p style="font-size:15px;line-height:1.6;">
                        Your {{ strtolower($requestLabel) }} has been approved. The associated held count has been added to the pool.
                    </p>
                @else
                    <p style="font-size:15px;line-height:1.6;">
                        Your {{ strtolower($requestLabel) }} has been rejected.
                    </p>
                @endif

                @if($isLocalMail && $originalEmail)
                    <p style="padding:10px 12px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;color:#9a3412;font-size:13px;">
                        Local testing copy. Intended recipient: {{ $originalEmail }}
                    </p>
                @endif

                <table style="width:100%;border-collapse:collapse;margin:18px 0;font-size:14px;">
                    <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Request Type</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $requestLabel }}</td></tr>
                    <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Sender</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $senderName }}</td></tr>
                    @if($lateHeldRequest->substituteTeacher)
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Original Teacher</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $lateHeldRequest->teacher?->name }}</td></tr>
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Taken By</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $lateHeldRequest->substituteTeacher?->name }}</td></tr>
                    @endif
                    <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Held Date</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $lateHeldRequest->held_date?->format('d M Y') }}</td></tr>
                    <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Class</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $slot?->course?->name }} Sem {{ $slot?->semester }} | {{ $slot?->paper?->name }}</td></tr>
                    <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Slot</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $slot?->day_name }} {{ substr((string) $slot?->start_time, 0, 5) }}-{{ substr((string) $slot?->end_time, 0, 5) }}</td></tr>
                    <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">Reason</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $lateHeldRequest->reason ?: '-' }}</td></tr>
                    @if($lateHeldRequest->tic_remark)
                        <tr><td style="padding:8px;border:1px solid #e5e7eb;background:#f9fafb;font-weight:bold;">TIC Remark</td><td style="padding:8px;border:1px solid #e5e7eb;">{{ $lateHeldRequest->tic_remark }}</td></tr>
                    @endif
                </table>

                <p style="margin:22px 0;">
                    <a href="{{ $requestUrl }}" style="display:inline-block;background:{{ $accent }};color:#ffffff;text-decoration:none;padding:11px 18px;border-radius:8px;font-weight:bold;font-size:14px;">
                        View Request
                    </a>
                </p>

                <p style="font-size:14px;line-height:1.6;color:#4b5563;">
                    Regards,<br>
                    Rajguru College Attendance System
                </p>
            </div>

            <div style="padding:14px 24px;background:#f9fafb;border-top:1px solid #e5e7eb;color:#6b7280;font-size:12px;line-height:1.5;">
                This is an automated email from the Rajguru College attendance portal. Please do not reply to this message.
            </div>
        </div>
    </div>
</body>
</html>
