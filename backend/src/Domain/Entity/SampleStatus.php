<?php

namespace App\Domain\Entity;

enum SampleStatus: string
{
    case RECEBIDA = 'recebida';
    case EM_ANALISE = 'em_analise';
    case CONCLUIDA = 'concluida';
    case REJEITADA = 'rejeitada';

    public function label(): string
    {
        return match ($this) {
            self::RECEBIDA => 'Recebida',
            self::EM_ANALISE => 'Em Análise',
            self::CONCLUIDA => 'Concluída',
            self::REJEITADA => 'Rejeitada',
        };
    }
}
