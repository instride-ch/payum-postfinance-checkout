<?php

declare(strict_types=1);

namespace Wvision\Payum\PostFinanceCheckout;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use PostFinanceCheckout\Sdk\ApiClient;
use PostFinanceCheckout\Sdk\Service\TransactionPaymentPageService;
use PostFinanceCheckout\Sdk\Service\TransactionService;
use Wvision\Payum\PostFinanceCheckout\Action\CaptureOffSiteAction;
use Wvision\Payum\PostFinanceCheckout\Action\ConvertPaymentAction;
use Wvision\Payum\PostFinanceCheckout\Action\NotifyAction;
use Wvision\Payum\PostFinanceCheckout\Action\NotifyNullAction;
use Wvision\Payum\PostFinanceCheckout\Action\StatusAction;

class PostFinanceCheckoutGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'postfinance_checkout',
            'payum.factory_title' => 'Postfinance Checkout',
            'payum.action.capture' => new CaptureOffSiteAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.notify_null' => new NotifyNullAction(),
        ]);

        if (!$config['payum.api']) {
            $config['payum.default_options'] = [
                'space_id' => '',
                'user_id' => '',
                'secret' => '',
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['space_id', 'user_id', 'secret'];

            $config['payum.api'] = static function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                $client = new ApiClient($config['user_id'], $config['secret']);

                return new ApiWrapper(
                    new TransactionService($client),
                    new TransactionPaymentPageService($client),
                    $config['space_id']
                );
            };
        }
    }
}
