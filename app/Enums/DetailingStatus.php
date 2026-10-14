<?php

namespace App\Enums;

enum DetailingStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Inactive = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Aguardando análise',
            self::Approved => 'Aprovada',
            self::Rejected => 'Recusada',
            self::Inactive => 'Inativa',
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
