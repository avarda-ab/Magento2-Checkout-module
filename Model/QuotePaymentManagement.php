<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model;

use Avarda\Checkout\Api\QuotePaymentManagementInterface;
use Magento\Framework\Exception\PaymentException;
use Magento\Payment\Model\InfoInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\CartInterface;

/**
 * QuotePaymentManagement
 * @see \Avarda\Checkout\Api\QuotePaymentManagementInterface
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class QuotePaymentManagement implements QuotePaymentManagementInterface
{
    const ERROR_QUOTE_MISSING_PURCHASE = 'Cart ID %cart_id does not have an active Avarda payment.';

    /**
     * Required for GET /avarda-items.
     *
     * @var \Avarda\Checkout\Api\ItemManagementInterface $itemManagement
     */
    protected $itemManagement;

    /**
     * Required for populating requests with item data.
     *
     * @var \Avarda\Checkout\Api\ItemStorageInterface $itemStorage
     */
    protected $itemStorage;

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
     * Command pool for API requests to Avarda.
     *
     * @var \Magento\Payment\Gateway\Command\CommandPoolInterface
     */
    protected $commandPool;

    /**
     * Required for executing API requests from command pool.
     *
     * @var \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface
     */
    protected $paymentDataObjectFactory;

    /**
     * Repository to load quote from database.
     *
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * Repository for Avarda's payment queue which links Avarda's purchase ID to
     * Magento's quote ID.
     *
     * @var \Avarda\Checkout\Api\PaymentQueueRepositoryInterface
     */
    protected $paymentQueueRepository;

    /**
     * Required to operate with payment queue repository.
     *
     * @var \Avarda\Checkout\Api\Data\PaymentQueueInterfaceFactory
     */
    protected $paymentQueueFactory;

    /**
     * Required for placing order in Magento.
     *
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * Temporary quote object to limit calls to repository.
     *
     * @var CartInterface
     */
    protected $quote;

    /** @var \Magento\Sales\Model\Order\Email\Sender\OrderSender */
    protected $orderSender;

    /** @var \Magento\Sales\Api\OrderRepositoryInterface */
    protected $orderRepository;

    /**
     * QuotePaymentManagement constructor.
     *
     * @param \Avarda\Checkout\Api\ItemManagementInterface $itemManagement
     * @param \Avarda\Checkout\Api\ItemStorageInterface $itemStorage
     * @param \Avarda\Checkout\Helper\PaymentData $paymentDataHelper
     * @param \Avarda\Checkout\Helper\PurchaseState $purchaseStateHelper
     * @param \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool
     * @param \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface $paymentDataObjectFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Avarda\Checkout\Api\PaymentQueueRepositoryInterface $paymentQueueRepository
     * @param \Avarda\Checkout\Api\Data\PaymentQueueInterfaceFactory $paymentQueueFactory
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     */
    public function __construct(
        \Avarda\Checkout\Api\ItemManagementInterface $itemManagement,
        \Avarda\Checkout\Api\ItemStorageInterface $itemStorage,
        \Avarda\Checkout\Helper\PaymentData $paymentDataHelper,
        \Avarda\Checkout\Helper\PurchaseState $purchaseStateHelper,
        \Magento\Payment\Gateway\Command\CommandPoolInterface $commandPool,
        \Magento\Payment\Gateway\Data\PaymentDataObjectFactoryInterface $paymentDataObjectFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Avarda\Checkout\Api\PaymentQueueRepositoryInterface $paymentQueueRepository,
        \Avarda\Checkout\Api\Data\PaymentQueueInterfaceFactory $paymentQueueFactory,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->itemManagement = $itemManagement;
        $this->itemStorage = $itemStorage;
        $this->paymentDataHelper = $paymentDataHelper;
        $this->purchaseStateHelper = $purchaseStateHelper;
        $this->commandPool = $commandPool;
        $this->paymentDataObjectFactory = $paymentDataObjectFactory;
        $this->quoteRepository = $quoteRepository;
        $this->paymentQueueRepository = $paymentQueueRepository;
        $this->paymentQueueFactory = $paymentQueueFactory;
        $this->cartManagement = $cartManagement;
        $this->orderSender = $orderSender;
        $this->orderRepository = $orderRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getPurchaseId($cartId)
    {
        $quote = $this->getQuote($cartId);
        $purchaseId = $this->paymentDataHelper->getPurchaseId(
            $quote->getPayment()
        );

        if (!$purchaseId) {
            /** We have to manually collect totals to populate the item storage */
            $quote->collectTotals();
            $purchaseId = $this->initializePurchase($quote);
        }

        return $purchaseId;
    }

    /**
     * {@inheritdoc}
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $quote
     */
    public function initializePurchase(CartInterface $quote)
    {
        $quote->reserveOrderId();

        $this->executeCommand('avarda_initialize_payment', $quote);

        /**
         * Save the additional data to quote payment and retrieve purchase ID
         * @see \Avarda\Checkout\Gateway\Response\InitializePaymentHandler
         */
        $quote->save();
        $purchaseId = $this->paymentDataHelper
            ->getPurchaseId($quote->getPayment());

        /** Save purchase ID link to quote ID in payment queue */
        $paymentQueue = $this->paymentQueueFactory->create();
        $paymentQueue->setPurchaseId($purchaseId);
        $paymentQueue->setQuoteId($quote->getId());
        try {
            $this->paymentQueueRepository->save($paymentQueue);
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            // Simple fix to not fail on already exists error
        }

        return $purchaseId;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemDetailsList($cartId)
    {
        $quote = $this->getQuote($cartId);
        $this->itemStorage->setItems($quote->getItems());
        return $this->itemManagement->getItemDetailsList();
    }

    /**
     * {@inheritdoc}
     */
    public function updateItems(CartInterface $quote)
    {
        $this->executeCommand('avarda_update_items', $quote);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuoteIsActive($cartId, $isActive)
    {
        $quote = $this->getQuote($cartId);
        try {
            $this->isAvardaPayment($quote);
        } catch (PaymentException $e) {
            // isAvardaPayment checko fails if payment method is something else than avarda
            $quote->getPayment()->setMethod('')->save();
            $this->isAvardaPayment($quote);
        }
        if ($quote->getIsActive() !== $isActive) {
            $quote->setIsActive($isActive);
            $quote->save();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updatePaymentStatus($cartId)
    {
        $this->isAvardaPayment($quote = $this->getQuote($cartId));
        $this->executeCommand('avarda_get_payment_status', $quote);
    }

    /**
     * {@inheritdoc}
     */
    public function placeOrder($cartId)
    {
        $this->isAvardaPayment($quote = $this->getQuote($cartId));

        /** Unfreeze cart before placing the order */
        $this->setQuoteIsActive($cartId, true);

        /** Must set checkout method for guests */
        if (!$quote->getCustomerId()) {
            $quote->setCheckoutMethod(CartManagementInterface::METHOD_GUEST);
        }

        $orderId = $this->cartManagement->placeOrder($cartId);

        // Clean payment queue
        $purchaseId = $this->paymentDataHelper->getPurchaseId(
            $quote->getPayment()
        );
        $paymentQueue = $this->paymentQueueRepository->get($purchaseId);
        $this->paymentQueueRepository->delete($paymentQueue);

        $order = $this->orderRepository->get($orderId);
        $this->orderSender->send($order);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuoteIdByPurchaseId($purchaseId)
    {
        $paymentQueue = $this->paymentQueueRepository->get($purchaseId);
        if ($paymentQueue->getQuoteId() === null) {
            throw new PaymentException(__('No cart linked with purchase ID "%purchase_id"', [
                'purchase_id' => $purchaseId
            ]));
        }

        $payment = $this->getQuote($paymentQueue->getQuoteId())->getPayment();
        if ($this->paymentDataHelper->getPurchaseId($payment) !== $purchaseId) {
            throw new PaymentException(__('Purchase ID "%purchase_id" is outdated', [
                'purchase_id' => $purchaseId
            ]));
        }

        return $paymentQueue->getQuoteId();
    }

    /**
     * Execute command for request to Avarda API based on quote.
     *
     * @param string $commandCode
     * @param CartInterface|\Magento\Quote\Model\Quote $quote
     * @return void
     */
    protected function executeCommand($commandCode, CartInterface $quote)
    {
        $arguments['amount'] = $quote->getGrandTotal();

        /** @var InfoInterface|null $payment */
        $payment = $quote->getPayment();
        if ($payment !== null && $payment instanceof InfoInterface) {
            $arguments['payment'] = $this->paymentDataObjectFactory
                ->create($payment);
        }

        $this->commandPool->get($commandCode)
            ->execute($arguments);
    }

    /**
     * Get quote by cart/quote ID
     *
     * @param int $cartId
     * @return CartInterface|\Magento\Quote\Model\Quote
     */
    protected function getQuote($cartId)
    {
        if (!isset($this->quote) || $this->quote->getId() !== $cartId) {
            /** @var CartInterface|\Magento\Quote\Model\Quote $quote */
            $this->quote = $this->quoteRepository->get($cartId);
        }

        return $this->quote;
    }

    /**
     * Check if quote has a valid Avarda payment.
     *
     * @param CartInterface|\Magento\Quote\Model\Quote $quote
     * @throws PaymentException
     * @return void
     */
    protected function isAvardaPayment(CartInterface $quote)
    {
        if (!$this->paymentDataHelper->isAvardaPayment($quote->getPayment())) {
            throw new PaymentException(__(self::ERROR_QUOTE_MISSING_PURCHASE, [
                'cart_id' => $quote->getId()
            ]));
        }
    }
}
