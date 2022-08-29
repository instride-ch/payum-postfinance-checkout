<?php

declare(strict_types=1);

namespace Wvision\Payum\PostFinanceCheckout\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use PostFinanceCheckout\Sdk\Model\TransactionState;
use Wvision\Payum\PostFinanceCheckout\ApiWrapper;
use Wvision\Payum\PostFinanceCheckout\Request\GetHumanStatus;

class StatusAction implements ActionInterface, ApiAwareInterface
{
    use ApiAwareTrait;

    public function __construct()
    {
        $this->apiClass = ApiWrapper::class;
    }

    /**
     * @inheritDoc
     *
     * @param GetHumanStatus $request
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = new ArrayObject($request->getModel());

        if (null === $model['pfc_transaction_id']) {
            $request->markNew();

            return;
        }

        $transaction = $this->api->getApi()->read($this->api->getSpaceId(), $model['pfc_transaction_id']);
        $status = $transaction->getState();

        switch ($status) {
            case TransactionState::CONFIRMED:
                if ($request instanceof GetHumanStatus) {
                    $request->markConfirmed();
                }

                break;
            case TransactionState::COMPLETED:
            case TransactionState::FULFILL:
                $request->markCaptured();

                break;
            case TransactionState::PENDING:
                $request->markPending();

                break;
            case TransactionState::AUTHORIZED:
                $request->markAuthorized();

                break;
            case TransactionState::DECLINE:
            case TransactionState::FAILED:
                $request->markFailed();

                break;
            default:
                $request->markUnknown();

                break;
        }
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess;
    }
}
