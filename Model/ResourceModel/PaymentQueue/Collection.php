<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model\ResourceModel\PaymentQueue;

/**
 * Payment queue collection.
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Initializes collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addFilterToMap('queue_id', 'main_table.queue_id');
        $this->addFilterToMap('purchase_id', 'main_table.purchase_id');
        $this->_init(
            \Avarda\Checkout\Model\PaymentQueue::class,
            \Avarda\Checkout\Model\ResourceModel\PaymentQueue::class
        );
    }
}
