<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model\ResourceModel;

/**
 * Payment queue resource model
 */
class PaymentQueue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('avarda_payment_queue', 'queue_id');
    }

    /**
     * Get payment queue identifier by purchase ID
     *
     * @param string $purchaseId
     * @return int|false
     */
    public function getIdByPurchaseId($purchaseId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from('avarda_payment_queue', 'queue_id')->where('purchase_id = :purchase_id');

        $bind = [':purchase_id' => (string)$purchaseId];

        return $connection->fetchOne($select, $bind);
    }
}
