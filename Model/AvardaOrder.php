<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model;

use Avarda\Checkout\Api\Data\AvardaOrderInterface;
use Magento\Framework\Model\AbstractModel;

class AvardaOrder extends AbstractModel implements AvardaOrderInterface
{
    protected function _construct()
    {
        $this->_init(\Avarda\Checkout\Model\ResourceModel\AvardaOrder::class);
    }

    /**
     * Purchase id
     *
     * @return string
     */
    public function getPurchaseId()
    {
        return $this->getData(self::PURCHASE_ID);
    }

    /**
     * Set purchase id
     *
     * @param string $purchaseId
     * @return $this
     */
    public function setPurchaseId(string $purchaseId)
    {
        $this->setData(self::PURCHASE_ID, $purchaseId);

        return $this;
    }
}
