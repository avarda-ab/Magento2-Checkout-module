<?php
/**

 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Http\Client;

use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\HTTP\ZendClientFactory;
use Magento\Framework\Webapi\Exception as WebapiException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

/**
 * Class Zend
 * @api
 * @since 100.0.2
 */
class Zend implements ClientInterface
{
    /**
     * @var ZendClientFactory
     */
    private $clientFactory;

    /**
     * @var ConverterInterface | null
     */
    private $converter;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param ZendClientFactory $clientFactory
     * @param Logger $logger
     * @param ConverterInterface | null $converter
     */
    public function __construct(
        ZendClientFactory $clientFactory,
        Logger $logger,
        ConverterInterface $converter = null
    ) {
        $this->clientFactory = $clientFactory;
        $this->converter = $converter;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        $log = [
            'request' => $transferObject->getBody(),
            'request_uri' => $transferObject->getUri()
        ];
        $result = [];
        /** @var ZendClient $client */
        $client = $this->clientFactory->create();

        $client->setConfig($transferObject->getClientConfig());
        $client->setMethod($transferObject->getMethod());

        switch ($transferObject->getMethod()) {
            case \Zend_Http_Client::GET:
                $client->setParameterGet($transferObject->getBody());
                break;
            case \Zend_Http_Client::POST:
                $client->setParameterPost($transferObject->getBody());
                break;
            default:
                throw new \LogicException(
                    sprintf(
                        'Unsupported HTTP method %s',
                        $transferObject->getMethod()
                    )
                );
        }

        $client->setHeaders($transferObject->getHeaders());
        $client->setUrlEncodeBody(true);
        $client->setUri($transferObject->getUri());

        try {
            $response = $client->request();

            $result = $this->converter
                ? $this->converter->convert($response)
                : [$response->getBody()];
            $log['response'] = $result;
        } catch (\Zend_Http_Client_Exception $e) {
            throw new \Magento\Payment\Gateway\Http\ClientException(
                __($e->getMessage())
            );
        } catch (\Magento\Payment\Gateway\Http\ConverterException $e) {
            throw $e;
        } catch (WebapiException $e) {
            foreach ($e->getErrors() as $error) {
                $log['response']['errors'][] = $error->getMessage();
            }
            throw $e;
        } finally {
            $this->logger->debug($log);
        }

        return $result;
    }
}
