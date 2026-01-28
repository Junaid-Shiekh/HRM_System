<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case APPLIED = 'applied';
    case SCREENING = 'screening';
    case INTERVIEW = 'interview';
    case OFFER = 'offer';
    case HIRED = 'hired';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::APPLIED => 'Applied',
            self::SCREENING => 'Screening',
            self::INTERVIEW => 'Interview',
            self::OFFER => 'Offer',
            self::HIRED => 'Hired',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::APPLIED => 'info',
            self::SCREENING => 'warning',
            self::INTERVIEW => 'primary',
            self::OFFER => 'success',
            self::HIRED => 'success',
            self::REJECTED => 'danger',
        };
    }
}
