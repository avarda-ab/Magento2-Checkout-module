<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Request;

use Avarda\Checkout\Gateway\Config\Config;
use Avarda\Checkout\Preference\Magento\Payment\Gateway\Data\Order\OrderAdapterFactory;
use Avarda\Checkout\Preference\Magento\Payment\Gateway\Data\Quote\QuoteAdapterFactory;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class AddressDataBuilder
 */
class AddressDataBuilder implements BuilderInterface
{
    /**
     * Whether the customer can edit the attributes related to invoicing address
     * manually.
     */
    const IS_INVOICING_EDITABLE = 'IsInvoicingEditable';

    /**
     * Enabling/disabling checkbox visibility for delivery address.
     */
    const IS_DELIVERY_EDITABLE = 'IsDeliveryEditable';

    /**
     * Delivery address fields prefix
     */
    const DELIVERY_PREFIX = 'Delivery';

    /**
     * Invoicing address fields prefix
     */
    const INVOICING_PREFIX = 'Invoicing';

    /**
     * The first name value must be less than or equal to 40 characters.
     */
    const FIRST_NAME = 'FirstName';

    /**
     * The last name value must be less than or equal to 40 characters.
     */
    const LAST_NAME = 'LastName';

    /**
     * The street address line 1. Maximum 40 characters.
     */
    const STREET_1 = 'AddressLine1';

    /**
     * The street address line 2. Maximum 40 characters.
     */
    const STREET_2 = 'AddressLine2';

    /**
     * The Zip/Postal code. Maximum 6 characters.
     */
    const ZIP = 'Zip';

    /**
     * The locality/city. 30 character maximum.
     */
    const CITY = 'City';

    /**
     * If delivery adress is diffrent the payment
    */
    const USE_DIFFERENT_DELIVERY_ADDRESS = 'UseDifferentDeliveryAddress';

    /**
     * Customers email address
     */
    const EMAIL = "Mail";
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var OrderAdapterFactory $orderAdapterFactory
     */
    protected $orderAdapterFactory;

    /**
     * @var QuoteAdapterFactory $quoteAdapterFactory
     */
    protected $quoteAdapterFactory;

    public function __construct(
        Config $config,
        OrderAdapterFactory $orderAdapterFactory,
        QuoteAdapterFactory $quoteAdapterFactory
    ) {
        $this->config = $config;
        $this->orderAdapterFactory = $orderAdapterFactory;
        $this->quoteAdapterFactory = $quoteAdapterFactory;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $payment = $paymentDO->getPayment();
        /*if ($paymentDO->getPayment() instanceof \Magento\Sales\Model\Order\Payment) {
            $order = $this->orderAdapterFactory->create(
                ['order' => $paymentDO->getPayment()->getOrder()]
            );
        } elseif ($paymentDO->getPayment() instanceof \Magento\Quote\Model\Quote\Payment) {
            $order = $this->quoteAdapterFactory->create(
                ['quote' => $paymentDO->getPayment()->getQuote()]
            );
        }*/

        return array_merge(
            $this->setBillingAddress($order),
            $this->setShippingAddress($order),
            $this->setAdditionalData($order)
        );
    }

    /**
     * @param OrderAdapterInterface $order
     * @return array
     */
    protected function setBillingAddress(OrderAdapterInterface $order)
    {
        $address = $order->getBillingAddress();
        if ($address !== null && $address->getPostcode() !== null) {
            return [
                self::INVOICING_PREFIX . self::FIRST_NAME => $address->getFirstname(),
                self::INVOICING_PREFIX . self::LAST_NAME  => $address->getLastname(),
                self::INVOICING_PREFIX . self::STREET_1   => $address->getStreetLine1(),
                self::INVOICING_PREFIX . self::STREET_2   => $address->getStreetLine2(),
                self::INVOICING_PREFIX . self::ZIP        => $address->getPostcode(),
                self::INVOICING_PREFIX . self::CITY       => $address->getCity(),
            ];
        }

        /**
         * Add postcode to invoicing zip if billing address is not set yet. This
         * way it can be pre-filled in Avarda iframe.
         */
        $address = $order->getShippingAddress();
        if ($address !== null) {
            return [
                self::INVOICING_PREFIX . self::FIRST_NAME => $address->getFirstname(),
                self::INVOICING_PREFIX . self::LAST_NAME  => $address->getLastname(),
                self::INVOICING_PREFIX . self::STREET_1   => $address->getStreetLine1(),
                self::INVOICING_PREFIX . self::STREET_2   => $address->getStreetLine2(),
                self::INVOICING_PREFIX . self::ZIP        => $address->getPostcode(),
                self::INVOICING_PREFIX . self::CITY       => $address->getCity(),
            ];
        }

        return [];
    }

    /**
     * @param OrderAdapterInterface $order
     * @return array
     */
    protected function setShippingAddress(OrderAdapterInterface $order)
    {
        $address = $order->getShippingAddress();
        if ($address === null) {
            return [];
        }

        return [
            self::DELIVERY_PREFIX . self::FIRST_NAME => $address->getFirstname(),
            self::DELIVERY_PREFIX . self::LAST_NAME  => $address->getLastname(),
            self::DELIVERY_PREFIX . self::STREET_1   => $address->getStreetLine1(),
            self::DELIVERY_PREFIX . self::STREET_2   => $address->getStreetLine2(),
            self::DELIVERY_PREFIX . self::ZIP        => $address->getPostcode(),
            self::DELIVERY_PREFIX . self::CITY       => $address->getCity(),
        ];
    }

    /**
     * Enable editing inside the Avarda iframe
     *
     * @param OrderAdapterInterface $order
     * @return array
     */
    protected function setAdditionalData(OrderAdapterInterface $order)
    {
        // @todo Acctualy do some type of check to determine if we already have the adresses and if they are the same.
        $isVirtual = true;
        $email = null;

        $countItems = 0;
        foreach ($order->getItems() as $_item) {
            /* @var $_item \Magento\Quote\Model\Quote\Item */
            if ($_item->isDeleted() || $_item->getParentItemId()) {
                continue;
            }
            $countItems++;
            if (!$_item->getProduct()->getIsVirtual()) {
                $isVirtual = false;
                break;
            }
        }
        $isVirtual = $countItems == 0 ? false : $isVirtual;

        $useDifferentDeliveryAddress = $this->config->isOnepageRedirectActive() ? 'false' : 'true';
        $isDeliveryEditable = $this->config->isOnepageRedirectActive() ? 'true' : 'false';

        if ($isVirtual) {
            $useDifferentDeliveryAddress = 'false';
            $isDeliveryEditable = 'false';
        }

        $address = $order->getBillingAddress();
        if ($address !== null && $address->getEmail()) {
            $email= $address->getEmail();
        } elseif ($address = $order->getShippingAddress() && $address->getEmail()) {
            $email= $address->getEmail();
        }

        return [
            self::EMAIL => $email,
            self::IS_INVOICING_EDITABLE => 'true',
            self::IS_DELIVERY_EDITABLE  => $isDeliveryEditable,
            self::USE_DIFFERENT_DELIVERY_ADDRESS => $useDifferentDeliveryAddress
        ];
    }
}
