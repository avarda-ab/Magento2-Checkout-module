<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Api;

/**
 * Interface for managing Avarda payment information
 * @api
 */
interface PaymentManagementInterface
{
    /**
     * Get purchase ID for Avarda payment
     *
     * @param int $cartId
     * @throws \Magento\Framework\Exception\PaymentException
     * @return \Avarda\Checkout\Api\Data\PaymentDetailsInterface
     */
    public function getPurchaseId($cartId);

    /**
     * Freeze the cart before redirected to payment. Return 200 status code if
     * everything is OK.
     *
     * @param int $cartId
     * @throws \Magento\Framework\Exception\PaymentException
     * @return void
     */
    public function freezeCart($cartId);

    /**
     * Get quote items additional information not provided by Magento Webapi
     *
     * @param string $cartId
     * @throws \Magento\Framework\Exception\PaymentException
     * @return \Avarda\Checkout\Api\Data\ItemDetailsListInterface
     */
    public function getItemDetailsList($cartId);
}
