<?php

namespace App\Enum;

enum CandidateStatus: string
{
    case NEW = 'NEW';
    case SCREENED = 'SCREENED';
    case INTERVIEW = 'INTERVIEW';
    case HIRED = 'HIRED';
    case REJECTED = 'REJECTED';
}
