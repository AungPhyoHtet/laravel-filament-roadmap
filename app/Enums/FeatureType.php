<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FeatureType: string implements HasColor, HasLabel
{
    case Feature = 'Feature';
    case Enhancement = 'Enhancement';
    case Bug = 'Bug';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Feature => 'primary',
            self::Enhancement => 'info',
            self::Bug => 'danger',
        };
    }
}
