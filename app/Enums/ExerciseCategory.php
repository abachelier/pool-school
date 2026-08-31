<?php

namespace App\Enums;

enum ExerciseCategory: string
{
    case BasicPotting = 'basic_potting';
    case PottingDownTheRail = 'potting_down_the_rail';
    case BackSpin = 'back_spin';
    case TopSpin = 'top_spin';
    case StopShot = 'stop_shot';
    case Break = 'break';
    case PatternPlay = 'pattern_play';

    public function slug(): string
    {
        return str_replace('_', '-', $this->value);
    }

    public function label(): string
    {
        return match ($this) {
            self::BasicPotting => 'Basic Potting',
            self::PottingDownTheRail => 'Potting Down the Rail',
            self::BackSpin => 'Back Spin',
            self::TopSpin => 'Top Spin',
            self::StopShot => 'Stop Shot',
            self::Break => 'Break',
            self::PatternPlay => 'Pattern Play',
        };
    }
}
