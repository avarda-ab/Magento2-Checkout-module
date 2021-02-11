<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model;

use Avarda\Checkout\Api\Data\PaymentDetailsInterface;
use Avarda\Checkout\Api\Data\PaymentDetailsInterfaceFactory;
use Avarda\Checkout\Api\PaymentManagementInterface;
use Avarda\Checkout\Api\QuotePaymentManagementInterface;

/**
 * PaymentManagement
 * @see \Avarda\Checkout\Api\PaymentManagementInterface
 */
class PaymentManagement implements PaymentManagementInterface
{
    /**
     * Required to create purchase ID response.
     *
     * @var PaymentDetailsInterfaceFactory
     */
    protected $paymentDetailsFactory;

    /**
     * A common interface to execute Webapi actions.
     *
     * @var QuotePaymentManagementInterface
     */
    protected $quotePaymentManagement;

    /**
     * GuestPaymentManagement constructor.
     *
     * @param PaymentDetailsInterfaceFactory $paymentDetailsFactory
     * @param QuotePaymentManagementInterface $quotePaymentManagement
     */
    public function __construct(
        PaymentDetailsInterfaceFactory $paymentDetailsFactory,
        QuotePaymentManagementInterface $quotePaymentManagement
    ) {
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->quotePaymentManagement = $quotePaymentManagement;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurchaseId($cartId)
    {
        $purchaseId = $this->quotePaymentManagement->getPurchaseId($cartId);

        /** @var PaymentDetailsInterface $paymentDetails */
        $paymentDetails = $this->paymentDetailsFactory->create();
        $paymentDetails->setPurchaseId($purchaseId);
        return $paymentDetails;
    }

    /**
     * {@inheritdoc}
     */
    public function freezeCart($cartId)
    {
        $this->quotePaymentManagement->setQuoteIsActive($cartId, false);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemDetailsList($cartId)
    {
        return $this->quotePaymentManagement->getItemDetailsList($cartId);
    }
}
