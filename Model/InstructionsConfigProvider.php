<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class InstructionsConfigProvider implements ConfigProviderInterface
{

    protected $methodCode = \Avarda\Checkout\Model\Ui\ConfigProvider::CODE;

    /** @var array */
    protected $method = [];

    /** @var Escaper */
    protected $escaper;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface */
    protected $scopeConfig;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->escaper = $escaper;
        $this->scopeConfig = $scopeConfig;
        $this->methods = $paymentHelper->getMethodInstance($this->methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        if ($this->methods->isAvailable()) {
            $config['payment']['instructions'][$this->methodCode] = $this->getInstructions($this->methodCode);
        }
        return $config;
    }

    /**
     * Get instructions text from config
     *
     * @param string $code
     * @return string
     */
    protected function getInstructions($code)
    {
        return nl2br($this->escaper->escapeHtml($this->scopeConfig->getValue('payment/avarda_checkout/instructions', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)));
    }
}
