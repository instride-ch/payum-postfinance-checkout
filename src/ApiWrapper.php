<?php

declare(strict_types=1);

namespace Wvision\Payum\PostFinanceCheckout;

use PostFinanceCheckout\Sdk\Service\TransactionPaymentPageService;
use PostFinanceCheckout\Sdk\Service\TransactionService;

class ApiWrapper
{
    public function __construct(
        private TransactionService $api,
        private TransactionPaymentPageService $paymentPageApi,
        private string $spaceId
    ) {
    }

    public function getApi(): TransactionService
    {
        return $this->api;
    }

    public function getPaymentPageApi(): TransactionPaymentPageService
    {
        return $this->paymentPageApi;
    }

    public function getSpaceId(): string
    {
        return $this->spaceId;
    }
}
