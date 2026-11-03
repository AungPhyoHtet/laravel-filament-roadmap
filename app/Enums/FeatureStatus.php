<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum FeatureStatus: string implements HasColor, HasIcon, HasLabel
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

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Proposed => Heroicon::OutlinedLightBulb,
            self::Planned => Heroicon::OutlinedCalendarDays,
            self::InProgress => Heroicon::OutlinedArrowPath,
            self::Delivered => Heroicon::OutlinedCheckCircle,
            self::Cancelled => Heroicon::OutlinedXCircle,
        };
    }
}
