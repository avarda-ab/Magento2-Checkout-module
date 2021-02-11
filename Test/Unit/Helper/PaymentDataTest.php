<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Test\Unit\Helper;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Class PaymentDataTest
 */
class PaymentDataTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Payment\Model\Info
     */
    protected $paymentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Avarda\Checkout\Helper\PaymentData
     */
    protected $paymentDataHelper;

    public function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->paymentMock = $this->createMock(\Magento\Payment\Model\Info::class);

        $this->paymentDataHelper = $this->objectManager->getObject(
            \Avarda\Checkout\Helper\PaymentData::class
        );
    }

    /**
     * @dataProvider purchaseIdDataProvider
     *
     * @param $additionalInformation
     * @param $expectedResult
     */
    public function testGetPurchaseId($additionalInformation, $expectedResult)
    {
        $this->paymentMock
            ->expects($this->once())
            ->method('getAdditionalInformation')
            ->will($this->returnValue($additionalInformation));

        $result = $this->paymentDataHelper->getPurchaseId($this->paymentMock);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider purchaseIdDataProvider
     *
     * @param $additionalInformation
     * @param $expectedResult
     */
    public function testIsAvardaPayment($additionalInformation, $expectedResult)
    {
        $this->paymentMock
            ->expects($this->once())
            ->method('getAdditionalInformation')
            ->will($this->returnValue($additionalInformation));

        $result = $this->paymentDataHelper->isAvardaPayment($this->paymentMock);

        $this->assertEquals((bool) $expectedResult, $result);
    }

    public function purchaseIdDataProvider()
    {
        return [
            'Existing Purchase ID' => [
                'additionalInformation' => ['purchase_id' => '1a2b3c4d'],
                'expectedResult'        => '1a2b3c4d',
            ],
            'Missing Field Purchase ID' => [
                'additionalInformation' => ['some_value' => '1a2b3c4d'],
                'expectedResult'        => false,
            ],
            'Missing Data Purchase ID' => [
                'additionalInformation' => [],
                'expectedResult'        => false,
            ],
        ];
    }

    /**
     * @dataProvider stateIdDataProvider
     *
     * @param $additionalInformation
     * @param $expectedResult
     */
    public function testGetStateId($additionalInformation, $expectedResult)
    {
        $this->paymentMock
            ->expects($this->once())
            ->method('getAdditionalInformation')
            ->will($this->returnValue($additionalInformation));

        $result = $this->paymentDataHelper->getStateId($this->paymentMock);

        $this->assertEquals($expectedResult, $result);
    }

    public function stateIdDataProvider()
    {
        return [
            'Existing State ID' => [
                'additionalInformation' => ['state_id' => '2'],
                'expectedResult'        => '2', // STATE_COMPLETED
            ],
            'Existing Invalid State ID' => [
                'additionalInformation' => ['state_id' => '12345'],
                'expectedResult'        => '99', // STATE_UNKNOWN
            ],
            'Missing Field State ID' => [
                'additionalInformation' => ['some_value' => '2'],
                'expectedResult'        => '0', // STATE_NEW
            ],
            'Missing Data State ID' => [
                'additionalInformation' => [],
                'expectedResult'        => '0', // STATE_NEW
            ],
        ];
    }

    public function testTransactionId()
    {
        $this->assertRegExp(
            '/^[0-9A-F]{8}-[0-9A-F]{4}-4[0-9A-F]{3}-[89AB][0-9A-F]{3}-[0-9A-F]{12}$/i',
            $this->paymentDataHelper->getTransactionId()
        );
    }
}
