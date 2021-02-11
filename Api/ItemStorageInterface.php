<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Api;

use Avarda\Checkout\Gateway\Data\ItemDataObjectInterface;

/**
 * Interface for storing Avarda item information
 * @api
 */
interface ItemStorageInterface
{
    /**
     * @param ItemDataObjectInterface[] $items
     * @return $this
     */
    public function setItems($items);

    /**
     * @param ItemDataObjectInterface $item
     * @return $this
     */
    public function addItem(ItemDataObjectInterface $item);

    /**
     * @return ItemDataObjectInterface[]
     */
    public function getItems();

    /**
     * @return $this
     */
    public function reset();
}
