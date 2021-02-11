<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Config;

/**
 * Class Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ACTIVE = 'active';
    const KEY_TEST_MODE = 'test_mode';
    const KEY_SITE_CODE = 'site_code';
    const KEY_SITE_PASSWORD = 'site_password';
    const KEY_ONEPAGE_REDIRECT_ACTIVE = 'onepage_redirect_active';
    const KEY_AUTOMATIC_INVOICING_ACTIVE = 'automatic_invoicing_active';
    const KEY_CUSTOM_CSS_URL  = 'custom_css_url';
    const KEY_REPLACE_DEFAULT_CSS = 'replace_default_css';
    const KEY_DEFAULT_THEME = 'avarda';
    const KEY_ORDER_STATUS = 'order_status';

    const APPLICATION_URL_STAGE = 'https://stage.avarda.org/CheckOut2';
    const APPLICATION_URL_ONLINE = 'https://online.avarda.org/CheckOut2';

    /**
     * Get Payment configuration status
     *
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * @return bool
     */
    public function getTestMode()
    {
        return (bool) $this->getValue(self::KEY_TEST_MODE);
    }

    /**
     * @return string
     */
    public function getSiteCode()
    {
        return $this->getValue(self::KEY_SITE_CODE);
    }

    /**
     * @return string
     */
    public function getSitePassword()
    {
        return $this->getValue(self::KEY_SITE_PASSWORD);
    }

    /**
     * @return string
     */
    public function getApplicationUrl()
    {
        if ($this->getTestMode()) {
            return self::APPLICATION_URL_STAGE;
        }

        return self::APPLICATION_URL_ONLINE;
    }

    /**
     * @return bool
     */
    public function isOnepageRedirectActive()
    {
        return (bool) $this->getValue(self::KEY_ONEPAGE_REDIRECT_ACTIVE);
    }

    /**
     * @return int
     */
    public function getOrderStatus()
    {
        return $this->getValue(self::KEY_ORDER_STATUS);
    }

    /**
     * @return bool
     */
    public function isAutomaticInvoicingActive()
    {
        return (bool) $this->getValue(self::KEY_AUTOMATIC_INVOICING_ACTIVE);
    }
    /**
     * Get custom css url
     * @return string|null
     */
    public function getTheme()
    {
        return $this->getValue(self::KEY_DEFAULT_THEME);
    }
    /**
     * Get custom css url
     * @return string|null
     */
    public function getCustomCssUrl()
    {
        return $this->getValue(self::KEY_CUSTOM_CSS_URL);
    }

    /**
     * @return bool
     */
    public function isReplaceDefaultCss()
    {
        return (bool) $this->getValue(self::KEY_REPLACE_DEFAULT_CSS);
    }
}
