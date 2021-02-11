<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Observer;

use Avarda\Checkout\Api\QuotePaymentManagementInterface;
use Avarda\Checkout\Helper\PaymentData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order;

class OrderCancelObserver implements ObserverInterface
{
    /** @var PaymentData */
    protected $paymentDataHelper;

    /** @var QuotePaymentManagementInterface */
    protected $quotePaymentManagement;

    /** @var CommandPoolInterface */
    protected $commandPool;

    /** @var PaymentDataObjectFactoryInterface */
    protected $paymentDataObjectFactory;

    public function __construct(
        PaymentData $paymentDataHelper,
        QuotePaymentManagementInterface $quotePaymentManagement,
        CommandPoolInterface $commandPool,
        PaymentDataObjectFactoryInterface $paymentDataObjectFactory
    ) {
        $this->paymentDataHelper = $paymentDataHelper;
        $this->quotePaymentManagement = $quotePaymentManagement;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->commandPool = $commandPool;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getData('order');
        $payment = $order->getPayment();
        if ($this->paymentDataHelper->isAvardaPayment($payment)) {
            $arguments['amount'] = $payment->getAmountOrdered();

            /** @var InfoInterface|null $payment */
            if ($payment !== null && $payment instanceof InfoInterface) {
                $arguments['payment'] = $this->paymentDataObjectFactory
                    ->create($payment);
            }

            $this->commandPool->get('avarda_cancel')->execute($arguments);
        }
    }
}
