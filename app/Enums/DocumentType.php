<?php

namespace App\Enums;

enum DocumentType: string
{
    case ParticipantsExcel = 'participants_excel';
    case AssignmentLetter = 'assignment_letter';
    case PaymentProof = 'payment_proof';

    public function label(): string
    {
        return match ($this) {
            self::ParticipantsExcel => __('Participant Data (Excel)'),
            self::AssignmentLetter => __('Assignment Letter'),
            self::PaymentProof => __('Proof of Payment'),
        };
    }
}
