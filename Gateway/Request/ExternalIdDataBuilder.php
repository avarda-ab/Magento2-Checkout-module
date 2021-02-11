<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Request;

use Avarda\Checkout\Api\Data\PaymentDetailsInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class ExternalIdDataBuilder
 */
class ExternalIdDataBuilder implements BuilderInterface
{
    /**
     * The purchase ID (external ID) of request
     */
    const EXTERNAL_ID = 'ExternalId';

    /**
     * Helper for reading payment info instances, e.g. getting purchase ID
     * from quote payment.
     *
     * @var \Avarda\Checkout\Helper\PaymentData
     */
    protected $paymentDataHelper;

    /**
     * ExternalIdDataBuilder constructor.
     *
     * @param \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
     */
    public function __construct(
        \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
    ) {
        $this->paymentDataHelper = $paymentDataHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $payment   = $paymentDO->getPayment();

        $purchaseId = $this->paymentDataHelper->getPurchaseId($payment);
        if (!$purchaseId) {
            return [];
        }

        return [self::EXTERNAL_ID => $purchaseId];
    }
}
