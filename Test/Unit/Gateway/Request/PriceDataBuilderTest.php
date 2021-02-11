<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Test\Unit\Gateway\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class PriceDataBuilderTest
 */
class PriceDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Avarda\Checkout\Gateway\Request\PriceDataBuilder
     */
    protected $priceDataBuilder;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->priceDataBuilder = $this->objectManager->getObject(
            \Avarda\Checkout\Gateway\Request\PriceDataBuilder::class
        );
    }

    public function testBuild()
    {
        $result = $this->priceDataBuilder->build([
            'amount' => 10.00,
        ]);

        $this->assertEquals([
            'Price' => 10.00,
        ], $result);
    }

    public function testBuildFormatting()
    {
        $result = $this->priceDataBuilder->build([
            'amount' => '10',
        ]);

        $this->assertEquals([
            'Price' => 10.00,
        ], $result);
    }

    public function testBuildEmpty()
    {
        $result = $this->priceDataBuilder->build([
            'amount' => 0,
        ]);

        $this->assertEquals([
            'Price' => 0.00,
        ], $result);
    }
}
