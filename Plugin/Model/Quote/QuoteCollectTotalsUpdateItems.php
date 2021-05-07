<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Plugin\Model\Quote;

use Avarda\Checkout\Api\QuotePaymentManagementInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Quote\Api\Data\CartInterface;

class QuoteCollectTotalsUpdateItems
{
    /**
     * Required for triggering update items request.
     *
     * @var QuotePaymentManagementInterface
     */
    protected $quotePaymentManagement;

    /**
     * Helper for reading payment info instances, e.g. getting purchase ID
     * from quote payment.
     *
     * @var \Avarda\Checkout\Helper\PaymentData
     */
    protected $paymentDataHelper;

    /**
     * Helper to determine Avarda's purchase state.
     *
     * @var \Avarda\Checkout\Helper\PurchaseState
     */
    protected $purchaseStateHelper;

    /**
     * Variable to ensure this plugin's logic is applied only once.
     *
     * @var bool
     */
    protected $collectTotalsFlag = false;

    /**
     * Http request
     *
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * QuoteCollectTotals constructor.
     *
     * @param QuotePaymentManagementInterface $quotePaymentManagement
     * @param \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
     * @param \Avarda\Checkout\Helper\PurchaseState $purchaseStateHelper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        QuotePaymentManagementInterface $quotePaymentManagement,
        \Avarda\Checkout\Helper\PaymentData $paymentDataHelper,
        \Avarda\Checkout\Helper\PurchaseState $purchaseStateHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->quotePaymentManagement = $quotePaymentManagement;
        $this->paymentDataHelper = $paymentDataHelper;
        $this->purchaseStateHelper = $purchaseStateHelper;
        $this->request = $request;
    }

    /**
     * Collect totals is triggered when quote is updated in any way, making it a
     * safe function to utilize and guarantee item updates to Avarda.
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     * @param CartInterface|\Magento\Quote\Model\Quote $result
     *
     * @return CartInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCollectTotals(CartInterface $subject, CartInterface $result)
    {
        $payment = $subject->getPayment();
        if (!$this->collectTotalsFlag &&
            $subject->getItemsCount() > 0 &&
            $this->paymentDataHelper->isAvardaPayment($payment)
        ) {
            // avoid infinite loops, because the calls here might call also collectTotals
            $this->collectTotalsFlag = true;
            try {
                // Update payment status to determine if session is outdated and needs to be initialized
                $this->quotePaymentManagement->updatePaymentStatus($subject->getId());

                $stateId = $this->getStateId($subject);
                if ($this->purchaseStateHelper->isComplete($stateId)) {
                    return $result;
                }
                if (($renew = $this->purchaseStateHelper->isDead($stateId)) === false) {
                    try {
                        $this->quotePaymentManagement->updateItems($subject);
                    } catch (WebapiException $e) {
                        $renew = true;
                    }
                }
            } catch (\Exception $e) {
                $renew = true;
            }
            if ($renew) {
                $this->quotePaymentManagement->initializePurchase($subject);
            }
            $this->collectTotalsFlag = false;
        }

        $this->collectTotalsFlag = false;
        return $result;
    }

    /**
     * Get state ID based on payment object
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     *
     * @throws PaymentException
     *
     * @return int
     */
    protected function getStateId(CartInterface $subject)
    {
        $payment = $subject->getPayment();
        $stateId = $this->paymentDataHelper->getStateId($payment);
        if (!$this->purchaseStateHelper->isInCheckout($stateId)) {
            $this->quotePaymentManagement
                ->updatePaymentStatus($subject->getId());

            $stateId = $this->paymentDataHelper->getStateId($payment);
            if ($this->purchaseStateHelper->isWaiting($stateId)) {
                throw new PaymentException(
                    __('Avarda is processing the purchase, unable to update items.')
                );
            }
        }

        return $stateId;
    }
}
