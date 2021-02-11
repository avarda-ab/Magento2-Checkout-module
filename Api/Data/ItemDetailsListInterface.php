<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Api\Data;

/**
 * Interface ItemDetailsListInterface
 * @api
 */
interface ItemDetailsListInterface
{
    /**
     * Constants defined for keys of array, makes typos less likely
     */
    const ITEMS = 'items';

    /**
     * Get quote items
     *
     * @return \Avarda\Checkout\Api\Data\ItemDetailsInterface[]
     */
    public function getItems();

    /**
     * Set quote items
     *
     * @param \Avarda\Checkout\Api\Data\ItemDetailsInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * Set quote items
     *
     * @param \Avarda\Checkout\Api\Data\ItemDetailsInterface $item
     * @return $this
     */
    public function addItem(ItemDetailsInterface $item);
}
