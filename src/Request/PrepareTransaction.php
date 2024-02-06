<?php

declare(strict_types=1);

namespace Instride\Payum\PostFinanceCheckout\Request;

use Payum\Core\Request\Generic;
use PostFinanceCheckout\Sdk\Model\TransactionCreate;

class PrepareTransaction extends Generic
{
    public function __construct(mixed $model, protected TransactionCreate $transaction)
    {
        parent::__construct($model);
    }

    public function getTransaction(): TransactionCreate
    {
        return $this->transaction;
    }
}
