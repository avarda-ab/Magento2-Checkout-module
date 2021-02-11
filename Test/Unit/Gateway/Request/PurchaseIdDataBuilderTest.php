<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Test\Unit\Gateway\Request;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class PurchaseIdDataBuilderTest
 */
class PurchaseIdDataBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Payment\Model\InfoInterface
     */
    protected $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Payment\Gateway\Data\PaymentDataObjectInterface
     */
    protected $paymentDataMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Avarda\Checkout\Helper\PaymentData
     */
    protected $paymentDataHelperMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Avarda\Checkout\Gateway\Request\PurchaseIdDataBuilder
     */
    protected $purchaseIdDataBuilder;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->paymentMock = $this->createMock(\Magento\Payment\Model\InfoInterface::class);

        $this->paymentDataMock = $this->createMock(\Magento\Payment\Gateway\Data\PaymentDataObjectInterface::class);
        $this->paymentDataMock->method('getPayment')->willReturn($this->paymentMock);

        $this->paymentDataHelperMock = $this->createMock(\Avarda\Checkout\Helper\PaymentData::class);

        $this->purchaseIdDataBuilder = $this->objectManager->getObject(
            \Avarda\Checkout\Gateway\Request\PurchaseIdDataBuilder::class,
            [
                'paymentDataHelper' => $this->paymentDataHelperMock,
            ]
        );
    }

    public function testBuild()
    {
        $this->paymentDataHelperMock
            ->expects($this->once())
            ->method('getPurchaseId')
            ->with($this->identicalTo($this->paymentMock))
            ->will($this->returnValue('1234'));

        $result = $this->purchaseIdDataBuilder->build([
            'payment' => $this->paymentDataMock
        ]);

        $this->assertEquals([
            'PurchaseId' => '1234',
        ], $result);
    }

    public function testBuildEmpty()
    {
        $this->paymentDataHelperMock
            ->expects($this->once())
            ->method('getPurchaseId')
            ->with($this->identicalTo($this->paymentMock))
            ->will($this->returnValue(false));

        $result = $this->purchaseIdDataBuilder->build([
            'payment' => $this->paymentDataMock
        ]);

        $this->assertEquals([], $result);
    }
}
