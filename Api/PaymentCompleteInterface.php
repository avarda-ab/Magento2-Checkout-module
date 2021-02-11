<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */

namespace Avarda\Checkout\Api;

/**
 * Interface for managing Avarda order complete callback
 * @api
 */
interface PaymentCompleteInterface
{
    /**
     * @throws \Magento\Framework\Exception\PaymentException
     * @param string $purchaseId the external purchaseId
     * @return \Avarda\Checkout\Api\Data\ItemDetailsListInterface
     */
    public function execute($purchaseId);
}
