<?php
declare(strict_types=1);

namespace App\Enums;

enum EntryStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
    case Archived  = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Published => 'Published',
            self::Archived  => 'Archived',
        };
    }

    public function canTransitionTo(self $new): bool
    {
        return match($this) {
            self::Draft     => $new === self::Published,
            self::Published => $new === self::Archived,
            self::Archived  => false,
        };
    }
}
