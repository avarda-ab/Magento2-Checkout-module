<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Helper;

/**
 * Class PaymentMethod
 */
class PaymentMethod
{
    const METHOD_INVOICE = 'Invoice';
    const METHOD_LOAN = 'Loan';
    const METHOD_CARD = 'Card';
    const METHOD_DIRECT_PAYMENT = 'DirectPayment';
    const METHOD_PART_PAYMENT = 'PartPayment';
    const METHOD_UNKNOWN = 'Unknown';

    /**
     * PaymentMethod enumeration
     *
     * @var array
     */
    public static $methods = [
        0 => self::METHOD_INVOICE,
        1 => self::METHOD_LOAN,
        2 => self::METHOD_CARD,
        3 => self::METHOD_DIRECT_PAYMENT,
        4 => self::METHOD_PART_PAYMENT,
        99 => self::METHOD_UNKNOWN,
    ];

    /**
     * PaymentMethod payment codes
     *
     * @var array
     */
    public static $codes = [
        self::METHOD_INVOICE => 'avarda_invoice',
        self::METHOD_LOAN => 'avarda_loan',
        self::METHOD_CARD => 'avarda_card',
        self::METHOD_DIRECT_PAYMENT => 'avarda_directpayment',
        self::METHOD_PART_PAYMENT => 'avarda_partpayment',
        self::METHOD_UNKNOWN => 'avarda_checkout',
    ];

    /**
     * Get payment method code for Magento order
     *
     * @param int $paymentMethod
     * @return string
     */
    public function getPaymentMethod($paymentMethod)
    {
        if (is_int($paymentMethod) &&
            array_key_exists($paymentMethod, self::$methods)
        ) {
            $method = self::$methods[$paymentMethod];
            if (array_key_exists($method, self::$codes)) {
                return self::$codes[$method];
            }
        }

        return self::$codes[self::METHOD_UNKNOWN];
    }
}
