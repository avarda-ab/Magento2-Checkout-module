<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Request;

use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

/**
 * Class PriceDataBuilder
 */
class PriceDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * The price to add to the payment
     */
    const PRICE = 'Price';

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::PRICE => $this->formatPrice(
                SubjectReader::readAmount($buildSubject)
            ),
        ];
    }
}
