<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Data;

/**
 * Interface ItemDataObjectInterface
 *
 * @api
 * @since 0.2.0
 */
interface ItemDataObjectInterface
{
    /**
     * Returns order item
     *
     * @return ItemAdapterInterface
     */
    public function getItem();

    /**
     * Returns subject data for builders
     *
     * @return array
     */
    public function getSubject();
}
