<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum FeatureStatus: string implements HasColor, HasLabel
{
    case Proposed = 'Proposed';
    case Planned = 'Planned';
    case InProgress = 'In Progress';
    case Delivered = 'Delivered';
    case Cancelled = 'Cancelled';

    public function getLabel(): string
    {
        return $this->value;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Proposed => 'gray',
            self::Planned => 'info',
            self::InProgress => 'warning',
            self::Delivered => 'success',
            self::Cancelled => 'danger',
        };
    }
}
