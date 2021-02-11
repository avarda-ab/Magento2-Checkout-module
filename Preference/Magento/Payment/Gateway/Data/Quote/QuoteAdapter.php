<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Preference\Magento\Payment\Gateway\Data\Quote;

use \Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use \Magento\Quote\Api\Data\CartInterface;;

/**
 * Class QuoteAdapter
 */
class QuoteAdapter extends \Magento\Payment\Gateway\Data\Quote\QuoteAdapter
{
    /**
     * @var CartInterface
     */
    private $quote;

    /**
     * @var AddressAdapter
     */
    private $addressAdapterFactory;

    /**
     * @param CartInterface $quote
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        CartInterface $quote,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->quote = $quote;
        $this->addressAdapterFactory = $addressAdapterFactory;
    }

    /**
     * Determines whether the cart is a virtual cart.
     *
     * A virtual cart contains virtual items.
     *
     * @return bool|null Virtual flag value. Otherwise, null.
     */
    public function getIsVirtual()
    {
        return $this->quote->getIsVirtual();
    }
}
