<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Helper;

/**
 * Class PurchaseState
 */
class PurchaseState
{
    const STATE_NEW = 'New';
    const STATE_BEING_PROCESSED = 'BeingProcessed';
    const STATE_COMPLETED = 'Completed';
    const STATE_ERROR = 'Error';
    const STATE_WAITING_FOR_SIGNICAT = 'WaitingForSignicat';
    const STATE_SESSION_TIMED_OUT = 'SessionTimedOut';
    const STATE_WAITING_FOR_CARD_PAYMENTS = 'WaitingForCardPayments';
    const STATE_WAITING_FOR_BANK_ID = 'WaitingForBankId';
    const STATE_CANCELLED = 'Cancelled';
    const STATE_WAITING_FOR_FINNISH_DIRECT_PAYMENT = 'WaitingForFinnishDirectPayment';
    const STATE_UNKNOWN = 'Unknown';

    /**
     * PurchaseState enumeration
     *
     * @var array
     */
    public static $states = [
        0 => self::STATE_NEW,
        1 => self::STATE_BEING_PROCESSED,
        2 => self::STATE_COMPLETED,
        3 => self::STATE_ERROR,
        4 => self::STATE_WAITING_FOR_SIGNICAT,
        5 => self::STATE_SESSION_TIMED_OUT,
        6 => self::STATE_WAITING_FOR_CARD_PAYMENTS,
        7 => self::STATE_WAITING_FOR_BANK_ID,
        8 => self::STATE_CANCELLED,
        9 => self::STATE_WAITING_FOR_FINNISH_DIRECT_PAYMENT,
        99 => self::STATE_UNKNOWN,
    ];

    /**
     * PurchaseState description
     *
     * @var array
     */
    public static $descriptions = [
        self::STATE_NEW => 'The partner site just negotiated a session but the Check-Out form hasn´t been shown in a browser yet.',
        self::STATE_BEING_PROCESSED => 'The customer is going through the Check-Out wizard.',
        self::STATE_COMPLETED => 'The Check-Out has been successfully completed.',
        self::STATE_ERROR => 'The Check-Out session has been canceled either by the user or due to an error.',
        self::STATE_WAITING_FOR_SIGNICAT => 'A redirect to Signicat website has been made and the customer hasn´t get redirected back to the original page yet. It purchases gets stuck it this state, a possible cause is that the page of the partner site that hosts the Check-Out form doesn´t handle redirects correctly.',
        self::STATE_SESSION_TIMED_OUT => 'The session timed out or wasn’t even created for a long time after the purchase has been initialized.',
        self::STATE_WAITING_FOR_CARD_PAYMENTS => 'A redirect to card payment provider has been made and the customer hasn´t been redirected back to the original page yet. Same as with WaitingForSignicat state, if purchases gets stuck in this state, the host page might not be able to render itself repeatedly.',
        self::STATE_WAITING_FOR_BANK_ID => 'Waiting for response from BankId application.',
        self::STATE_CANCELLED => 'When merchant calls CancelPayment method and order is canceled.',
        self::STATE_WAITING_FOR_FINNISH_DIRECT_PAYMENT => 'The customer has been redirected to a Finnish bank for a direct payment.',
        self::STATE_UNKNOWN => 'The payment is in an unknown state.',
    ];

    /**
     * Get payment state code for Magento order
     *
     * @param int $stateId
     * @return string
     */
    public function getState($stateId)
    {
        if (is_int($stateId) &&
            array_key_exists($stateId, self::$states)
        ) {
            return self::$states[$stateId];
        }

        return self::STATE_UNKNOWN;
    }

    /**
     * Check if customer is in checkout
     *
     * @param int $stateId
     * @return bool
     */
    public function isInCheckout($stateId)
    {
        return in_array(
            $this->getState($stateId),
            [self::STATE_NEW, self::STATE_BEING_PROCESSED],
            true
        );
    }

    /**
     * Check if payment is complete
     *
     * @param int $stateId
     * @return bool
     */
    public function isComplete($stateId)
    {
        return ($this->getState($stateId) == self::STATE_COMPLETED);
    }

    /**
     * Check if payment is waiting for card/bank actions
     *
     * @param int $stateId
     * @return bool
     */
    public function isWaiting($stateId)
    {
        return in_array(
            $this->getState($stateId),
            [
                self::STATE_WAITING_FOR_BANK_ID,
                self::STATE_WAITING_FOR_CARD_PAYMENTS,
                self::STATE_WAITING_FOR_FINNISH_DIRECT_PAYMENT,
                self::STATE_WAITING_FOR_SIGNICAT
            ],
            true
        );
    }

    /**
     * Check if payment is cancelled
     *
     * @param int $stateId
     * @return bool
     */
    public function isCancelled($stateId)
    {
        return in_array(
            $this->getState($stateId),
            [self::STATE_ERROR, self::STATE_CANCELLED],
            true
        );
    }

    /**
     * Check if payment is dead
     *
     * @param int $stateId
     * @return bool
     */
    public function isDead($stateId)
    {
        return (
            $this->isCancelled($stateId) ||
            $this->getState($stateId) == self::STATE_SESSION_TIMED_OUT
        );
    }
}
