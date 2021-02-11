<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferFactoryInterface;

class TransferFactory implements TransferFactoryInterface
{
    const BASIC_AUTHENTICATION_FORMAT = 'Basic %s';

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var \Magento\Payment\Gateway\Http\TransferBuilder
     */
    protected $transferBuilder;

    /**
     * @var \Avarda\Checkout\Gateway\Config\Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $method;

    /**
     * @var string
     */
    protected $uri;

    /**
     * TransferFactory constructor.
     *
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder
     * @param \Avarda\Checkout\Gateway\Config\Config $config
     * @param string $method
     * @param string $uri
     */
    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Payment\Gateway\Http\TransferBuilder $transferBuilder,
        \Avarda\Checkout\Gateway\Config\Config $config,
        $method = \Zend_Http_Client::POST,
        $uri = ''
    ) {
        $this->encryptor = $encryptor;
        $this->transferBuilder = $transferBuilder;
        $this->config = $config;
        $this->method = $method;
        $this->uri = $uri;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return \Magento\Payment\Gateway\Http\TransferInterface
     */
    public function create(array $request)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => $this->getBasicAuthorization(),
        ];

        return $this->transferBuilder
            ->setMethod($this->method)
            ->setUri($this->getUri())
            ->setHeaders($headers)
            ->setBody($request)
            ->build();
    }

    /**
     * Generate basic authorization string
     *
     * @return string
     */
    protected function getBasicAuthorization()
    {
        $sitePassword = $this->encryptor->decrypt(
            $this->config->getSitePassword()
        );

        // The Site Code and Site Password are concatenated into a single string
        // delimited by a colon.
        $authString = implode(
            ':',
            [
                $this->config->getSiteCode(),
                $sitePassword,
            ]
        );

        return sprintf(
            self::BASIC_AUTHENTICATION_FORMAT,
            base64_encode($authString)
        );
    }

    /**
     * Get URI for the request to call
     *
     * @return string
     */
    public function getUri()
    {
        return $this->config->getApplicationUrl() . $this->uri;
    }
}
