<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Api\Data;

/**
 * Interface PaymentDetailsInterface
 * @api
 */
interface PaymentDetailsInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const PURCHASE_ID = 'purchase_id';

    /**
     * Return the generated purchase ID
     *
     * @return string
     */
    public function getPurchaseId();

    /**
     * Return the generated purchase ID
     *
     * @param string $purchaseId
     * @return $this
     */
    public function setPurchaseId($purchaseId);
}
