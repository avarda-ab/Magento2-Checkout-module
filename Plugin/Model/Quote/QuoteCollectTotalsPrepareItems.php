<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Plugin\Model\Quote;

use Avarda\Checkout\Api\ItemStorageInterface;
use Avarda\Checkout\Gateway\Data\ItemAdapter\ArrayDataItemFactory;
use Avarda\Checkout\Gateway\Data\ItemAdapter\QuoteItemFactory;
use Avarda\Checkout\Gateway\Data\ItemDataObjectFactory;
use Magento\Quote\Api\Data\CartInterface;

class QuoteCollectTotalsPrepareItems
{
    /**
     * @var \Psr\Log\LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var ItemStorageInterface $itemStorage
     */
    protected $itemStorage;

    /**
     * @var ItemDataObjectFactory $itemDataObjectFactory
     */
    protected $itemDataObjectFactory;

    /**
     * @var QuoteItemFactory $quoteItemAdapterFactory
     */
    protected $quoteItemAdapterFactory;

    /**
     * @var ArrayDataItemFactory $arrayDataItemAdapterFactory
     */
    protected $arrayDataItemAdapterFactory;

    /**
     * @var \Avarda\Checkout\Helper\PaymentData
     */
    protected $paymentDataHelper;

    /**
     * @var bool
     */
    protected $collectTotalsFlag = false;

    /**
     * QuoteCollectTotals constructor.
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param ItemStorageInterface $itemStorage
     * @param ItemDataObjectFactory $itemDataObjectFactory,
     * @param QuoteItemFactory $quoteItemAdapterFactory
     * @param ArrayDataItemFactory $arrayDataItemAdapterFactory
     * @param \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        ItemStorageInterface $itemStorage,
        ItemDataObjectFactory $itemDataObjectFactory,
        QuoteItemFactory $quoteItemAdapterFactory,
        ArrayDataItemFactory $arrayDataItemAdapterFactory,
        \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
    ) {
        $this->logger = $logger;
        $this->itemStorage = $itemStorage;
        $this->itemDataObjectFactory = $itemDataObjectFactory;
        $this->quoteItemAdapterFactory = $quoteItemAdapterFactory;
        $this->arrayDataItemAdapterFactory = $arrayDataItemAdapterFactory;
        $this->paymentDataHelper = $paymentDataHelper;
    }

    /**
     * @param CartInterface $subject
     * @param CartInterface $result
     * @return CartInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCollectTotals(CartInterface $subject, CartInterface $result)
    {
        try {
            if (!$this->collectTotalsFlag &&
                $subject->getItemsCount() > 0 &&
                $subject->getItems() !== null
            ) {
                $this->prepareItemStorage($subject);
                $this->collectTotalsFlag = true;
            }
        } catch (\Exception $e) {
            $this->logger->error($e);
        }

        return $result;
    }

    /**
     * Populate the item storage with Avarda items needed for request building
     *
     * @param CartInterface $subject
     *
     * @return void
     */
    protected function prepareItemStorage(CartInterface $subject)
    {
        $this->itemStorage->reset();
        $this->prepareItems($subject);
        $this->prepareShipment($subject);
        $this->prepareGiftCards($subject);
    }

    /**
     * Create item data objects from quote items
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     *
     * @return void
     */
    protected function prepareItems(CartInterface $subject)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        foreach ($subject->getItemsCollection() as $item) {
            if ($item->getData('product_id') === null ||
                $item->getData('parent_item_id') !== null ||
                $item->isDeleted()
            ) {
                continue;
            }

            $itemAdapter = $this->quoteItemAdapterFactory->create([
                'quoteItem' => $item
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
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     *
     * @return void
     */
    protected function prepareShipment(CartInterface $subject)
    {
        if ($subject->isVirtual()) {
            return;
        }

        $shippingAddress = $subject->getShippingAddress();
        if ($shippingAddress && $shippingAddress->getShippingTaxAmount() > 0) {
            $itemAdapter = $this->arrayDataItemAdapterFactory->create([
                'data' => [
                    'name' => $shippingAddress->getShippingDescription(),
                    'sku' => $shippingAddress->getShippingMethod(),
                ],
            ]);
            $itemDataObject = $this->itemDataObjectFactory->create(
                $itemAdapter,
                1,
                $shippingAddress->getShippingInclTax(),
                $shippingAddress->getShippingTaxAmount()
            );

            $this->itemStorage->addItem($itemDataObject);
        }
    }

    /**
     * Create item data object from gift card information
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $subject
     *
     * @return void
     */
    protected function prepareGiftCards(CartInterface $subject)
    {
        $giftCardsAmountUsed = $subject->getData('gift_cards_amount_used');
        if ($giftCardsAmountUsed !== null && $giftCardsAmountUsed > 0) {
            $itemAdapter = $this->arrayDataItemAdapterFactory->create([
                'data' => [
                    'name' => __('Gift Card'),
                    'sku' => __('giftcard'),
                ],
            ]);
            $itemDataObject = $this->itemDataObjectFactory->create(
                $itemAdapter,
                1,
                $giftCardsAmountUsed * -1,
                0
            );

            $this->itemStorage->addItem($itemDataObject);
        }
    }
}
