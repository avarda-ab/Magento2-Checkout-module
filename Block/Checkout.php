<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Block;

use Avarda\Checkout\Gateway\Config\Config;
use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\Session;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class Checkout extends Template
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $directoryHelper;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * Request instance
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Repository
     */
    protected $assetRepo;

    /**
     * @var CartInterface
     */
    protected $quote;

    /**
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @var array|LayoutProcessorInterface[]
     */
    protected $layoutProcessors;
    /**
     * @var SerializerInterface|mixed
     */
    private $serializer;

    /**
     * Checkout constructor.
     *
     * @param Context $context
     * @param Config $config
     * @param CompositeConfigProvider $configProvider
     * @param Session $checkoutSession
     * @param Data $directoryHelper
     * @param ProductMetadataInterface $productMetadata
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param RequestInterface $request
     * @param LocaleResolver $localeResolver
     * @param array $layoutProcessors
     * @param array $data = []
     * @param SerializerInterface|null $serializerInterface
     */
    public function __construct(
        Context $context,
        Config $config,
        CompositeConfigProvider $configProvider,
        Session $checkoutSession,
        Data $directoryHelper,
        ProductMetadataInterface $productMetadata,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        RequestInterface $request,
        LocaleResolver $localeResolver,
        array $layoutProcessors = [],
        array $data = [],
        SerializerInterface $serializerInterface = null
    ) {
        parent::__construct($context, $data);

        $this->config = $config;
        $this->configProvider = $configProvider;
        $this->checkoutSession = $checkoutSession;
        $this->directoryHelper = $directoryHelper;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->assetRepo = $context->getAssetRepository();
        $this->request = $request;
        $this->localeResolver = $localeResolver;
        $this->layoutProcessors = $layoutProcessors;
        $this->serializer = $serializerInterface ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\JsonHexTag::class);

        if ($productMetadata->getEdition() === 'Enterprise') {
            $this->jsLayout = array_merge_recursive([
                'components' => [
                    'gift-card' => [
                        'component' => 'Magento_GiftCardAccount/js/view/payment/gift-card-account',
                        'children' => [
                            'errors' => [
                                'sortOrder' => 0,
                                'component' => 'Magento_GiftCardAccount/js/view/payment/gift-card-messages',
                                'displayArea' => 'messages'
                            ]
                        ]
                    ]
                ]
            ], $this->jsLayout);
        }
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * @return int|null
     */
    public function getMaskedQuoteId()
    {
        return $this->quoteIdMaskFactory->create()->load(
            $this->getQuoteId(),
            'quote_id'
        )->getMaskedId();
    }

    /**
     * @return int|null
     */
    public function getCustomerId()
    {
        return (int) $this->getQuote()->getCustomerId();
    }

    /**
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getQuote()->getId();
    }

    /**
     * @return bool
     */
    public function hasItems()
    {
        return $this->getQuote()->hasItems();
    }

    /**
     * @return CartInterface
     */
    protected function getQuote()
    {
        if (!isset($this->quote)) {
            $this->quote = $this->checkoutSession->getQuote();
        }

        return $this->quote;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->localeResolver->getLocale();
    }

    /**
     * @return string
     */
    public function getCountryId()
    {
        return $this->directoryHelper->getDefaultCountry();
    }

    /**
     * Get AvardaCheckOutClient script path for Require.js.
     *
     * @return string
     */
    public function getCheckOutClientScriptPath()
    {
        return $this->config->getApplicationUrl() . '/Scripts/CheckOutClient';
    }

    /**
     * @return array
     */
    public function getCheckoutConfig()
    {
        return $this->configProvider->getConfig();
    }

    /**
     * @return string
     */
    public function getPurchaseId()
    {
        return $this->_request->getParam('purchase');
    }

    /**
     * @return string|null
     */
    public function getCustomCssUrl()
    {
        $url = $this->config->getCustomCssUrl();
        if ($url) {
            if (0 === strpos($url, 'http')) {
                return $url;
            }

            return $this->assetRepo->getUrl($url);
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getTheme()
    {
        return $this->config->getTheme();
    }

    /**
     * @return bool
     */
    public function isReplaceDefaultCss()
    {
        return $this->config->isReplaceDefaultCss();
    }

    /**
     * @return string
     */
    public function getSaveOrderUrl()
    {
        return $this->getUrl('avarda/checkout/saveOrder', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getCallbackUrl()
    {
        return $this->getUrl('avarda/checkout/process', ['_secure' => true]);
    }

    /**
     * @return string
     */
    public function getCompleteCallbackUrl()
    {
        return $this->getBaseUrl() . 'rest/V1/avarda/orderComplete';
    }

    /**
     * @return string
     */
    public function getProductPlaceholderUrl()
    {
        return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/thumbnail.jpg');
    }

    /**
     * @return boolean
     */
    public function getArrivedFromCheckout()
    {
        return !$this->config->isOnepageRedirectActive() && $this->request->getParam('fromCheckout');
    }

    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }

        return $this->serializer->serialize($this->jsLayout);
    }

    /**
     * Retrieve form key
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Get base url for block.
     *
     * @return string
     * @codeCoverageIgnore
     * @throws NoSuchEntityException
     */
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Retrieve serialized checkout config.
     *
     * @return bool|string
     * @since 100.2.0
     */
    public function getSerializedCheckoutConfig()
    {
        return  $this->serializer->serialize($this->getCheckoutConfig());
    }
}
