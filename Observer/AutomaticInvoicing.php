<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Observer;

use Avarda\Checkout\Gateway\Config\Config;
use Avarda\Checkout\Helper\PaymentData;
use Exception;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\InvoiceManagementInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Psr\Log\LoggerInterface;

/**
 * Class AutomaticInvoicing
 */
class AutomaticInvoicing implements ObserverInterface
{
    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var PaymentData
     */
    protected $paymentDataHelper;

    /**
     * @var InvoiceManagementInterface|InvoiceService
     */
    protected $invoiceService;

    /**
     * @var TransactionFactory
     */
    protected $transactionFactory;

    /**
     * AutomaticInvoicing constructor.
     *
     * @param LoggerInterface $logger
     * @param Config $config
     * @param PaymentData $paymentDataHelper
     * @param InvoiceManagementInterface $invoiceService
     * @param TransactionFactory $transactionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        Config $config,
        PaymentData $paymentDataHelper,
        InvoiceManagementInterface $invoiceService,
        TransactionFactory $transactionFactory
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->paymentDataHelper = $paymentDataHelper;
        $this->invoiceService = $invoiceService;
        $this->transactionFactory = $transactionFactory;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var Order $order */
            $order = $observer->getEvent()->getData('order');
            $payment = $order->getPayment();
            if ($this->paymentDataHelper->isAvardaPayment($payment) &&
                $this->config->isAutomaticInvoicingActive()
            ) {
                $invoice = $this->invoiceService->prepareInvoice($order);
                $invoice->setData('requested_capture_case', null);
                $invoice->setState(Invoice::STATE_OPEN);
                $invoice->register();

                $this->transactionFactory->create()
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder())
                    ->save();
            }
        } catch (Exception $e) {
            $this->logger->error($e);
        }
    }
}
