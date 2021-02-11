<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class OrderReferenceDataBuilder
 */
class OrderReferenceDataBuilder implements BuilderInterface
{
    /**
     * A unique identifier for Avarda to find the order
     */
    const ORDER_REFERENCE = 'OrderReference';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order     = $paymentDO->getOrder();

        return [self::ORDER_REFERENCE => $order->getOrderIncrementId()];
    }
}
