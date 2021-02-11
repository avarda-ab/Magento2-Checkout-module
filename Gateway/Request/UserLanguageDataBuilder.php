<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class TranIdDataBuilder
 */
class UserLanguageDataBuilder implements BuilderInterface
{
    /**
     * User language code
     */
    const USER_LANGUAGE_CODE = 'UserLanguageCode';

    /**
     * Allowed locale codes in Avarda
     *
     * @var string[]
     */
    protected $allowedLocaleCodes = [
        'sv_SE',
        'fi_FI',
    ];

    /**
     * The default locale code for Avarda
     *
     * @var string
     */
    protected $defaultLocaleCode = 'en-US';

    /**
     * Current active store needed to get locale code
     *
     * @var \Magento\Framework\Locale\Resolver $localeResolver
     */
    protected $localeResolver;

    /**
     * UserLanguageDataBuilder constructor.
     *
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     */
    public function __construct(
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function build(array $buildSubject)
    {
        return [self::USER_LANGUAGE_CODE => $this->getUserLanguageCode()];
    }

    /**
     * Get the locale code in format en_US and convert to en-US for Avarda
     *
     * @return string
     */
    public function getUserLanguageCode()
    {
        $localeCode = $this->localeResolver->getLocale();
        if (in_array($localeCode, $this->allowedLocaleCodes)) {
            return str_replace('_', '-', $localeCode);
        }

        return $this->defaultLocaleCode;
    }
}
