<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Model\Data;

use Avarda\Checkout\Api\Data\ItemDetailsListInterface;
use Avarda\Checkout\Api\Data\ItemDetailsInterface;
use Magento\Framework\Model\AbstractExtensibleModel;

/**
 * @codeCoverageIgnoreStart
 */
class ItemDetailsList extends AbstractExtensibleModel implements
    ItemDetailsListInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItems()
    {
        if (!$this->hasData(self::ITEMS)) {
            return [];
        }

        return $this->getData(self::ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function setItems($items)
    {
        if (!is_array($items)) {
            return $this->setData(self::ITEMS, [$items]);
        }

        return $this->setData(self::ITEMS, $items);
    }

    /**
     * {@inheritdoc}
     */
    public function addItem(ItemDetailsInterface $item)
    {
        $items = $this->getItems();
        $items[] = $item;

        return $this->setData(self::ITEMS, $items);
    }
}
