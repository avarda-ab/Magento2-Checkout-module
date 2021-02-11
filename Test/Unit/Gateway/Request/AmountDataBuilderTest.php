<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Test\Unit\Gateway\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class AmountDataBuilderTest
 */
class AmountDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Avarda\Checkout\Gateway\Request\AmountDataBuilder
     */
    protected $amountDataBuilder;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->amountDataBuilder = $this->objectManager->getObject(
            \Avarda\Checkout\Gateway\Request\AmountDataBuilder::class
        );
    }

    public function testBuild()
    {
        $result = $this->amountDataBuilder->build([
            'amount' => 10.00,
        ]);

        $this->assertEquals([
            'Amount' => 10.00,
        ], $result);
    }

    public function testBuildFormatting()
    {
        $result = $this->amountDataBuilder->build([
            'amount' => '10',
        ]);

        $this->assertEquals([
            'Amount' => 10.00,
        ], $result);
    }

    public function testBuildEmpty()
    {
        $result = $this->amountDataBuilder->build([
            'amount' => 0,
        ]);

        $this->assertEquals([
            'Amount' => 0.00,
        ], $result);
    }
}
