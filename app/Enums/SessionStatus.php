<?php

namespace App\Enums;

enum SessionStatus: string
{
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
