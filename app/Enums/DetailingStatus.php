<?php

namespace App\Enums;

enum DetailingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Inactive = 'inactive';

    public function canTransitionTo(self $status): bool
    {
        return in_array($status, match ($this) {
            self::Pending => [self::Approved, self::Rejected],
            self::Approved => [self::Inactive],
            self::Inactive => [self::Approved],
            self::Rejected => [],
        }, true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Aguardando análise',
            self::Approved => 'Aprovada',
            self::Rejected => 'Recusada',
            self::Inactive => 'Inativa',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-amber-100 text-amber-900',
            self::Approved => 'bg-emerald-100 text-emerald-900',
            self::Rejected => 'bg-red-100 text-red-900',
            self::Inactive => 'bg-zinc-200 text-zinc-800',
        };
    }

    public function ownerMessage(): string
    {
        return match ($this) {
            self::Pending => 'Recebemos sua solicitação. A estética passa a aparecer no catálogo depois que o cadastro for analisado e aprovado.',
            self::Approved => 'Sua estética está publicada e aparece no catálogo público.',
            self::Rejected => 'O cadastro da estética não foi aprovado.',
            self::Inactive => 'Sua estética está inativa e não aparece no catálogo. Os dados não podem ser alterados enquanto ela estiver nessa situação.',
        };
    }
}
