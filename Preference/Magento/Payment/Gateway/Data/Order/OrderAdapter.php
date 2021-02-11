<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Preference\Magento\Payment\Gateway\Data\Order;

use \Magento\Payment\Gateway\Data\Order\AddressAdapterFactory;
use \Magento\Sales\Model\Order;

/**
 * Class OrderAdapter
 */
class OrderAdapter extends \Magento\Payment\Gateway\Data\Order\OrderAdapter
{
    /**
     * @var Order
     */
    private $order;

    /**
     * @var AddressAdapter
     */
    private $addressAdapterFactory;

    /**
     * @param Order $order
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        Order $order,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        parent::__construct($order, $addressAdapterFactory);
    }

    /**
     * Get customer email from Order
     */
    public function getCustomerEmail()
    {
        return $this->order->getCustomerEmail();
    }

    /**
     * Get customer email from Order
     */
    public function getIsVirtual()
    {
        return $this->order->getIsVirtual();
    }
}
