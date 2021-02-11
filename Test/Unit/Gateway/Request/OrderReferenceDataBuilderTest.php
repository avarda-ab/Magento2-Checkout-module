<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Test\Unit\Gateway\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class OrderReferenceDataBuilderTest
 */
class OrderReferenceDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Payment\Gateway\Data\OrderAdapterInterface
     */
    protected $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Payment\Gateway\Data\PaymentDataObjectInterface
     */
    protected $paymentDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Avarda\Checkout\Gateway\Request\OrderReferenceDataBuilder
     */
    protected $orderReferenceDataBuilder;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->orderMock = $this->createMock(\Magento\Payment\Gateway\Data\OrderAdapterInterface::class);

        $this->paymentDataMock = $this->createMock(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $this->paymentDataMock->method('getOrder')->willReturn($this->orderMock);

        $this->orderReferenceDataBuilder = $this->objectManager->getObject(
            \Avarda\Checkout\Gateway\Request\OrderReferenceDataBuilder::class
        );
    }

    public function testBuild()
    {
        $this->orderMock->method('getOrderIncrementId')->will($this->returnValue('1234'));

        $result = $this->orderReferenceDataBuilder->build([
            'payment' => $this->paymentDataMock
        ]);

        $this->assertEquals([
            'OrderReference' => '1234',
        ], $result);
    }

    public function testBuildEmpty()
    {
        $this->orderMock->method('getOrderIncrementId')->willReturn(null);

        $result = $this->orderReferenceDataBuilder->build([
            'payment' => $this->paymentDataMock
        ]);

        $this->assertEquals([
            'OrderReference' => '',
        ], $result);
    }
}
