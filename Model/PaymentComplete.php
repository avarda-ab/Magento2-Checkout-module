<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model;

use Avarda\Checkout\Api\PaymentCompleteInterface;
use Avarda\Checkout\Api\QuotePaymentManagementInterface;
use Avarda\Checkout\Controller\Checkout\SaveOrder;
use \Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class PaymentComplete implements PaymentCompleteInterface
{
    /** @var QuotePaymentManagementInterface */
    protected $quotePaymentManagement;

    /** @var SaveOrder */
    protected $saveOrderController;

    /** @var RequestInterface */
    protected $request;

    public function __construct(
        QuotePaymentManagementInterface $quotePaymentManagement,
        SaveOrder $saveOrderController,
        RequestInterface $request
    ) {
        $this->quotePaymentManagement = $quotePaymentManagement;
        $this->saveOrderController = $saveOrderController;
        $this->request = $request;
    }

    /**
     * Handle serverside order complete handling
     */
    public function execute($purchaseId)
    {
        $this->request->setParams(['purchase' => $purchaseId]);
        try {
            $this->quotePaymentManagement->getQuoteIdByPurchaseId($purchaseId);
            $this->saveOrderController->execute();
        } catch (NoSuchEntityException $noSuchEntityException) {
            // Order is already saved
        }
    }
}
