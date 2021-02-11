<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Plugin\Checkout;

use Avarda\Checkout\Gateway\Config\Config;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;

class CheckoutIndex
{
    /** @var Config */
    protected $config;

    /** @var RedirectFactory */
    protected $redirectFactory;

    public function __construct(
        RedirectFactory $redirectFactory,
        Config $config
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->config = $config;
    }

    /**
     * Checkout page
     *
     * @return ResultInterface
     */
    public function aroundExecute($subject, $proceed)
    {
        if ($this->config->isActive() &&
            $this->config->isOnepageRedirectActive()
        ) {
            return $this->redirectFactory
                ->create()->setPath('avarda/checkout');
        }

        return $proceed();
    }
}
