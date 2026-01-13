<?php

namespace App\Enums;

enum TaskPriority: string
{
    case HIGH = 'HIGH';
    case MEDIUM = 'MEDIUM';
    case LOW = 'LOW';

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::HIGH => 'High',
            self::MEDIUM => 'Medium',
            self::LOW => 'Low',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HIGH => 'red',
            self::MEDIUM => 'yellow',
            self::LOW => 'green',
        };
    }

    public function borderColorClass(): string
    {
        return match ($this) {
            self::HIGH => 'border-red-500',
            self::MEDIUM => 'border-yellow-500',
            self::LOW => 'border-green-500',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::HIGH => 'priority-badge-high',
            self::MEDIUM => 'priority-badge-medium',
            self::LOW => 'priority-badge-low',
        };
    }

    public static function options(): array
    {
        return array_map(
            fn (self $priority) => [
                'value' => $priority->value,
                'label' => $priority->label(),
            ],
            self::cases()
        );
    }

    public static function fromInput($value)
    {
        return self::tryFrom(strtoupper($value));
    }
}
