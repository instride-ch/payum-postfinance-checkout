<?php

declare(strict_types=1);

namespace Instride\Payum\PostFinanceCheckout\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryAwareInterface;
use Payum\Core\Security\GenericTokenFactoryAwareTrait;
use Payum\Core\Security\TokenInterface;
use PostFinanceCheckout\Sdk\ApiException;
use PostFinanceCheckout\Sdk\Http\ConnectionException;
use PostFinanceCheckout\Sdk\Model\LineItemCreate;
use PostFinanceCheckout\Sdk\Model\LineItemType;
use PostFinanceCheckout\Sdk\Model\TransactionCreate;
use PostFinanceCheckout\Sdk\VersioningException;
use Instride\Payum\PostFinanceCheckout\ApiWrapper;
use Instride\Payum\PostFinanceCheckout\Request\PrepareTransaction;

/**
 * @property ApiWrapper $api
 */
class CaptureOffSiteAction implements ActionInterface, GenericTokenFactoryAwareInterface, GatewayAwareInterface, ApiAwareInterface
{
    use ApiAwareTrait;
    use GatewayAwareTrait;
    use GenericTokenFactoryAwareTrait;

    public function __construct()
    {
        $this->apiClass = ApiWrapper::class;
    }

    /**
     * @inheritDoc
     *
     * @param Capture $request
     *
     * @throws ApiException
     * @throws ConnectionException
     * @throws VersioningException
     */
    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        // We are back from Postfinance Checkout site, so we can skip the rest.
        if (isset($model['pfc_transaction_id'])) {
            return;
        }

        $token = $request->getToken();

        if (!$token instanceof TokenInterface) {
            return;
        }

        $targetUrl = $token->getTargetUrl();

        $lineItem = new LineItemCreate();
        $lineItem->setName($model['description']);
        $lineItem->setUniqueId($model['order_id']);
        $lineItem->setQuantity(1);
        $lineItem->setAmountIncludingTax(round($model['amount'] / 100, 2));
        $lineItem->setType(LineItemType::PRODUCT);

        $transaction = new TransactionCreate();
        $transaction->setCurrency($model['currency_code']);
        $transaction->setSuccessUrl($targetUrl . '?action=success');
        $transaction->setFailedUrl($targetUrl . '?action=cancelled');
        $transaction->setAutoConfirmationEnabled(true);
        $transaction->setMetaData([
            'token' => $token->getHash(),
        ]);

        $this->gateway->execute(new PrepareTransaction($request->getFirstModel(), $transaction));

        $createdTransaction = $this->api->getApi()->create($this->api->getSpaceId(), $transaction);

        $model['pfc_transaction_id'] = $createdTransaction->getId();

        throw new HttpRedirect(
            $this->api->getPaymentPageApi()->paymentPageUrl($this->api->getSpaceId(), $createdTransaction->getId())
        );
    }

    /**
     * @inheritDoc
     */
    public function supports($request): bool
    {
        return $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }
}
