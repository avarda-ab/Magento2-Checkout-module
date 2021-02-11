<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model\Ui\PartPayment;

use Avarda\Checkout\Model\Ui\ConfigProvider as BaseConfigProvider;

/**
 * Class ConfigProvider
 */
class ConfigProvider extends BaseConfigProvider
{
    const CODE = 'avarda_partpayment';

    /**
     * Disable the module in frontend, the payment method should only be selectable through Avarda Checkout.
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                self::CODE => [
                    'isActive' => false,
                ]
            ]
        ];

        return $config;
    }
}
