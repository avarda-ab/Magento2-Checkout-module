<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'avarda_checkout';

    /**
     * @var \Avarda\Checkout\Gateway\Config\Config
     */
    protected $config;

    public function __construct(
        \Avarda\Checkout\Gateway\Config\Config $config
    ) {
        $this->config = $config;
    }

    /**
     * Disable the module in frontend if needed, then the payment method should only be selectable through Avarda Checkout.
     *
     * @return array
     */
    public function getConfig()
    {
        $active = $this->config->isActive() && !$this->config->isOnepageRedirectActive();

        $config = [
            'payment' => [
                self::CODE => [
                    'isActive' => $active,
                ]
            ]
        ];

        return $config;
    }
}
