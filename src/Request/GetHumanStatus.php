<?php

declare(strict_types=1);

namespace Instride\Payum\PostFinanceCheckout\Request;

use Payum\Core\Request\GetHumanStatus as BaseGetHumanStatus;

class GetHumanStatus extends BaseGetHumanStatus
{
    public const STATUS_CONFIRMED = 'confirmed';

    public function markConfirmed(): void
    {
        $this->status = static::STATUS_CONFIRMED;
    }

    public function isConfirmed(): bool
    {
        return $this->isCurrentStatusEqualTo(static::STATUS_CONFIRMED);
    }
}
