<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Api\Data;

/**
 * PaymentQueue interface.
 * @api
 */
interface PaymentQueueInterface
{
    /**#@+
     * Constants defined for keys of  data array
     */
    const QUEUE_ID = 'queue_id';
    const PURCHASE_ID = 'purchase_id';
    const QUOTE_ID = 'quote_id';
    const UPDATED_AT = 'updated_at';
    /**#@-*/

    /**
     * Queue id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set queue id
     *
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * Purchase id
     *
     * @return string|null
     */
    public function getPurchaseId();

    /**
     * Set purchase id
     *
     * @param string $purchaseId
     * @return $this
     */
    public function setPurchaseId($purchaseId);

    /**
     * Quote id
     *
     * @return int|null
     */
    public function getQuoteId();

    /**
     * Set quote id
     *
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId);

    /**
     * Queue updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set queue updated date
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}
