<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class CustomerDataBuilder
 */
class CustomerDataBuilder implements BuilderInterface
{
    /**
     * The phone value must be less than or equal to 15 characters.
     */
    const PHONE = 'Phone';

    /**
     * The email value must be less than or equal to 60 characters.
     */
    const MAIL = 'Mail';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        if ($billingAddress === null) {
            return [];
        }

        return [
            self::PHONE => $billingAddress->getTelephone(),
            self::MAIL => $billingAddress->getEmail(),
        ];
    }
}
