<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model\Data;

use Avarda\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @codeCoverageIgnoreStart
 */
class PaymentDetails extends AbstractExtensibleModel implements
    PaymentDetailsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getPurchaseId()
    {
        return $this->getData(self::PURCHASE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setPurchaseId($purchaseId)
    {
        return $this->setData(self::PURCHASE_ID, $purchaseId);
    }
}
