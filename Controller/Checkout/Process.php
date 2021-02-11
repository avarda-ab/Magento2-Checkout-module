<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Controller\Checkout;

use Avarda\Checkout\Api\QuotePaymentManagementInterface;
use Avarda\Checkout\Controller\AbstractCheckout;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\PaymentException;

class Process extends AbstractCheckout
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var QuotePaymentManagementInterface
     */
    protected $quotePaymentManagement;

    /**
     * Process constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Avarda\Checkout\Gateway\Config\Config $config
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param QuotePaymentManagementInterface $quotePaymentManagement
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Avarda\Checkout\Gateway\Config\Config $config,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        QuotePaymentManagementInterface $quotePaymentManagement
    ) {
        parent::__construct($context, $logger, $config);
        $this->resultPageFactory = $resultPageFactory;
        $this->quotePaymentManagement = $quotePaymentManagement;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     */
    public function execute()
    {
        // Show no route if Avarda is inactive and notify webmaster in logs.
        if (!$this->isCallback() && !$this->config->isActive()) {
            return $this->noroute('/checkout/avarda/process');
        }

        try {
            if (($purchaseId = $this->getPurchaseId()) === null) {
                throw new \Exception(
                    __('Failed to save order with purchase ID "%purchase_id"', [
                        'purchase_id' => $purchaseId
                    ])
                );
            }

            $quoteId = $this->quotePaymentManagement
                ->getQuoteIdByPurchaseId($purchaseId);

            $this->quotePaymentManagement->updatePaymentStatus($quoteId);
            $this->quotePaymentManagement->setQuoteIsActive($quoteId, true);
            return $this->resultPageFactory->create();

        } catch (PaymentException $e) {
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->error($e);
            $message = __('Failed to save Avarda order. Please try again later.');
        }

        $this->messageManager->addErrorMessage($message);
        return $this->resultRedirectFactory
            ->create()->setPath('avarda/checkout');
    }
}
