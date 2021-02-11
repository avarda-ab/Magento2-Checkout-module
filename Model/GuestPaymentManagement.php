<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model;

use Avarda\Checkout\Api\Data\PaymentDetailsInterface;
use Avarda\Checkout\Api\Data\PaymentDetailsInterfaceFactory;
use Avarda\Checkout\Api\GuestPaymentManagementInterface;
use Avarda\Checkout\Api\QuotePaymentManagementInterface;

/**
 * GuestPaymentManagement
 * @see \Avarda\Checkout\Api\GuestPaymentManagementInterface
 */
class GuestPaymentManagement implements GuestPaymentManagementInterface
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
     * @var \Avarda\Checkout\Api\QuotePaymentManagementInterface
     */
    protected $quotePaymentManagement;

    /**
     * Required to get the real quote ID from masked quote ID.
     *
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * GuestPaymentManagement constructor.
     *
     * @param PaymentDetailsInterfaceFactory $paymentDetailsFactory
     * @param QuotePaymentManagementInterface $quotePaymentManagement
     * @param \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
     */
    public function __construct(
        PaymentDetailsInterfaceFactory $paymentDetailsFactory,
        QuotePaymentManagementInterface $quotePaymentManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->paymentDetailsFactory = $paymentDetailsFactory;
        $this->quotePaymentManagement = $quotePaymentManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurchaseId($cartId)
    {
        $purchaseId = $this->quotePaymentManagement->getPurchaseId(
            $this->getQuoteId($cartId)
        );

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
        $this->quotePaymentManagement
            ->setQuoteIsActive($this->getQuoteId($cartId), false);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemDetailsList($cartId)
    {
        return $this->quotePaymentManagement
            ->getItemDetailsList($this->getQuoteId($cartId));
    }

    /**
     * Get the quote ID from masked cart ID.
     *
     * Note: getQuoteId() == $cartId == quote::entity_id
     *
     * @param string $cartId
     * @return int
     */
    protected function getQuoteId($cartId)
    {
        /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()
            ->load($cartId, 'masked_id');

        $quoteId = $quoteIdMask->getData('quote_id');
        if ($quoteId === null) {
            throw new \Magento\Framework\Exception\PaymentException(
                __('Could not find quote with given ID.')
            );
        }

        return $quoteId;
    }
}
