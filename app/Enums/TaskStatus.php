<?php

namespace App\Enums;

enum TaskStatus: string
{
    case TODO = 'TODO';
    case IN_PROGRESS = 'IN_PROGRESS';
    case CANCELLED = 'CANCELLED';
    case DONE = 'DONE';

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
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::CANCELLED => 'Cancelled',
            self::DONE => 'Done',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::TODO => 'clock',
            self::IN_PROGRESS => 'bolt',
            self::CANCELLED => 'x-circle',
            self::DONE => 'check-circle',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TODO => 'blue',
            self::IN_PROGRESS => 'orange',
            self::CANCELLED => 'red',
            self::DONE => 'green',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::TODO => 'bg-blue-500',
            self::IN_PROGRESS => 'bg-orange-500',
            self::CANCELLED => 'bg-red-500',
            self::DONE => 'bg-green-500',
        };
    }

    public function boardId(): string
    {
        return match ($this) {
            self::TODO => 'todo',
            self::IN_PROGRESS => 'in-progress',
            self::CANCELLED => 'cancelled',
            self::DONE => 'done',
        };
    }

    public function toBoardArray(): array
    {
        return [
            'id' => $this->boardId(),
            'title' => $this->label(),
            'status' => $this->value,
            'color' => $this->color(),
            'colorClass' => $this->colorClass(),
        ];
    }

    public static function allBoards(): array
    {
        return array_map(
            fn (self $status) => $status->toBoardArray(),
            self::cases()
        );
    }

    public static function fromInput($value)
    {
        return self::tryFrom(strtoupper($value));
    }
}
