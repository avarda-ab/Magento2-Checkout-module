<?php
/**
 * @copyright Copyright © 2021 Avarda. All rights reserved.
 * @package   Avarda_Checkout
 */
namespace Avarda\Checkout\Gateway\Http\Converter;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\ConverterInterface;
use Magento\Framework\Webapi\Exception as WebapiException;

/**
 * Class JsonConverter
 * @api
 * @since 100.0.2
 */
class JsonConverter implements ConverterInterface
{
    const WEBAPI_EXCEPTION_NAME = 'Avarda Check-out 2 API exception';
    const WEBAPI_EXCEPTION_MESSAGE = '%message (code: %code)';

    /**
     * Converts gateway response to array structure
     *
     * @param \Zend_Http_Response $response
     * @return array
     * @throws ConverterException
     */
    public function convert($response)
    {
        if ($response->isError()) {
            $this->throwErrors($response);
        }

        try {
            $convertedResponse = json_decode($response->getBody());
            if (!is_array($convertedResponse)) {
                return [$convertedResponse];
            }

            return $convertedResponse;
        } catch(\Exception $e) {
            throw new ConverterException(
                __('Something went wrong with Avarda checkout. Please try again later.')
            );
        }
    }

    /**
     * There are multiple types of errors, this function makes them into a
     * reliable format.
     *
     * @param \Zend_Http_Response $response
     * @throws WebapiException
     * @return void
     */
    public function throwErrors($response)
    {
        $body = json_decode($response->getBody(), true);
        $errors = [];
        if ($response->getStatus() === WebapiException::HTTP_UNAUTHORIZED) {
            $errors[] = new AuthorizationException(
                __('Failed to authorize Avarda checkout payment.')
            );
        } elseif (isset($body['CheckOutErrorCode'])) {
            $errorCode = $body['CheckOutErrorCode'];
            foreach ($body['Errors'] as $message) {
                $errors[] = new LocalizedException(
                    __(self::WEBAPI_EXCEPTION_MESSAGE, [
                        'code' => $errorCode,
                        'message' => __($message),
                    ])
                );
            }
        } elseif (is_array($body)) {
            foreach ($body as $error) {
                $errors[] = new LocalizedException(
                    __(self::WEBAPI_EXCEPTION_MESSAGE, [
                        'code' => isset($error['ErrorCode']) ? $error['ErrorCode'] : '',
                        'message' => isset($error['ErrorMessage']) ? __($error['ErrorMessage']) : '',
                    ])
                );
            }
        }

        if (empty($errors)) {
            $message = isset($body['Message']) ? $body['Message'] : 'An unknown error occurred';
            $errors[] = new LocalizedException(
                __(self::WEBAPI_EXCEPTION_MESSAGE, [
                    'code' => '0',
                    'message' => __($message),
                ])
            );
        }

        throw new WebapiException(
            __('Something went wrong with Avarda checkout. Please try again later.'),
            0,
            (int) $response->getStatus(),
            [],
            self::WEBAPI_EXCEPTION_NAME,
            $errors
        );
    }
}
