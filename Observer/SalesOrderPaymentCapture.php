<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use Avarda\Checkout\Api\ItemStorageInterface;
use Avarda\Checkout\Gateway\Data\ItemDataObjectFactory;
use Avarda\Checkout\Gateway\Data\ItemAdapter\ArrayDataItemFactory;
use Avarda\Checkout\Gateway\Data\ItemAdapter\OrderItemFactory;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class SalesOrderPaymentCapture
 */
class SalesOrderPaymentCapture implements ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var ItemStorageInterface
     */
    protected $itemStorage;

    /**
     * @var ItemDataObjectFactory
     */
    protected $itemDataObjectFactory;

    /**
     * @var OrderItemFactory
     */
    protected $orderItemAdapterFactory;

    /**
     * @var ArrayDataItemFactory
     */
    protected $arrayDataItemAdapterFactory;

    /**
     * @var \Avarda\Checkout\Helper\PaymentData
     */
    protected $paymentDataHelper;

    /**
     * CreditmemoCollectTotalsPrepareItems constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param ItemStorageInterface $itemStorage
     * @param ItemDataObjectFactory $itemDataObjectFactory
     * @param OrderItemFactory $orderItemAdapterFactory
     * @param ArrayDataItemFactory $arrayDataItemAdapterFactory
     * @param \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ItemStorageInterface $itemStorage,
        ItemDataObjectFactory $itemDataObjectFactory,
        OrderItemFactory $orderItemAdapterFactory,
        ArrayDataItemFactory $arrayDataItemAdapterFactory,
        \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
    ) {
        $this->logger = $logger;
        $this->itemStorage = $itemStorage;
        $this->itemDataObjectFactory = $itemDataObjectFactory;
        $this->orderItemAdapterFactory = $orderItemAdapterFactory;
        $this->arrayDataItemAdapterFactory = $arrayDataItemAdapterFactory;
        $this->paymentDataHelper = $paymentDataHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            $payment = $observer->getEvent()->getData('payment');
            if ($this->paymentDataHelper->isAvardaPayment($payment)) {
                $this->prepareItemStorage($observer->getEvent()->getData('invoice'));
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }

    /**
     * Populate the item storage with Avarda items needed for request building
     *
     * @param InvoiceInterface $subject
     */
    public function prepareItemStorage(InvoiceInterface $subject)
    {
        $this->itemStorage->reset();
        $this->prepareItems($subject);
        $this->prepareShipment($subject);
        $this->prepareGiftCards($subject);
    }

    /**
     * Create item data objects from invoice items
     *
     * @param InvoiceInterface|\Magento\Sales\Model\Order\Invoice $subject
     */
    protected function prepareItems(InvoiceInterface $subject)
    {
        /** @var \Magento\Sales\Model\Order\Invoice\Item $item */
        foreach ($subject->getItems() as $item) {
            $orderItem = $item->getOrderItem();
            if (!$orderItem->getProductId() ||
                $orderItem->getData('parent_item_id') !== null ||
                $item->isDeleted()
            ) {
                continue;
            }

            $itemAdapter = $this->orderItemAdapterFactory->create([
                'orderItem' => $orderItem
            ]);
            $itemDataObject = $this->itemDataObjectFactory->create(
                $itemAdapter,
                $item->getQty(),
                $item->getRowTotalInclTax() -
                    $item->getDiscountAmount(),
                $item->getTaxAmount() +
                    $item->getDiscountTaxCompensationAmount() +
                    $item->getWeeeTaxAppliedAmount()
            );

            $this->itemStorage->addItem($itemDataObject);
        }
    }

    /**
     * Create item data object from shipment information
     *
     * @param InvoiceInterface|\Magento\Sales\Model\Order\Invoice $subject
     */
    protected function prepareShipment(InvoiceInterface $subject)
    {
        $shippingAmount = $subject->getShippingInclTax();
        if ($shippingAmount > 0) {
            $order = $subject->getOrder();
            $itemAdapter = $this->arrayDataItemAdapterFactory->create([
                'data' => [
                    'name' => $order->getShippingDescription(),
                    'sku' => $order->getShippingMethod(),
                ],
            ]);
            $itemDataObject = $this->itemDataObjectFactory->create(
                $itemAdapter,
                1,
                $shippingAmount,
                $subject->getShippingTaxAmount()
            );

            $this->itemStorage->addItem($itemDataObject);
        }
    }

    /**
     * Create item data object from gift card information
     *
     * @param InvoiceInterface|\Magento\Sales\Model\Order\Invoice $subject
     */
    protected function prepareGiftCards(InvoiceInterface $subject)
    {
        $giftCardsAmount = $subject->getData('gift_cards_amount');
        if ($giftCardsAmount !== null && $giftCardsAmount > 0) {
            $itemAdapter = $this->arrayDataItemAdapterFactory->create([
                'data' => [
                    'name' => __('Gift Card'),
                    'sku' => __('giftcard'),
                ],
            ]);
            $itemDataObject = $this->itemDataObjectFactory->create(
                $itemAdapter,
                1,
                $giftCardsAmount * -1,
                0
            );

            $this->itemStorage->addItem($itemDataObject);
        }
    }
}
