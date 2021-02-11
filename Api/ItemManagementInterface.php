<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Api;

/**
 * Interface for managing Avarda item information
 * @api
 */
interface ItemManagementInterface
{
    /**
     * Get quote items additional information not provided by Magento Webapi
     *
     * @return \Avarda\Checkout\Api\Data\ItemDetailsListInterface
     */
    public function getItemDetailsList();
}
