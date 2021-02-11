<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Request\Item;

use Avarda\Checkout\Gateway\Helper\ItemSubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class AmountDataBuilder
 */
class AmountDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * The amount to add to the payment
     */
    const AMOUNT = 'Amount';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::AMOUNT => $this->formatPrice(
                ItemSubjectReader::readAmount($buildSubject)
            ),
        ];
    }
}
