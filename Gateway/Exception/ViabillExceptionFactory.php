<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Exception;

use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Zend\Http\Response;

/**
 * Class Factory
 * @package Viabillhq\Payment\Gateway\Exception
 */
class ViabillExceptionFactory
{
    /**
     * @var MessagesMap
     */
    private $messagesMap;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * ViabillExceptionFactory constructor.
     *
     * @param MessagesMap $messagesMap
     * @param SerializerInterface $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        MessagesMap $messagesMap,
        SerializerInterface $jsonSerializer,
        LoggerInterface $logger
    ) {
        $this->messagesMap = $messagesMap;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    /**
     * Generate exception depending on response.
     *
     * @param \Zend\Http\Response $response
     * @param $commandCode
     *
     * @return \Viabillhq\Payment\Gateway\Exception\ViabillException
     */
    public function create(Response $response, $commandCode)
    {
        $responseErrorMessage = $this->getResponseErrorMessage($response);
        $errorCode = $response->getStatusCode();
        $errorMessage = $this->messagesMap->getMessage($commandCode, $errorCode);
        if (!$errorMessage) {
            $errorCode = ViabillException::DEFAULT_CODE;
            $errorMessage = $responseErrorMessage ?? ViabillException::DEFAULT_MESSAGE;
        }
        $this->logger->critical($responseErrorMessage ?? $errorMessage);

        return new ViabillException(__($errorMessage), null, $errorCode);
    }

    /**
     * @param Response $response
     *
     * @return null|string
     */
    private function getResponseErrorMessage(Response $response)
    {
        try {
            $responseBody = $this->jsonSerializer->unserialize($response->getContent());
            if (is_array($responseBody) && array_key_exists('errors', $responseBody)) {
                foreach ($responseBody['errors'] as $message) {
                    if (!empty($message['field'])) {
                        $errorMessages[] = "\"{$message['field']}\" {$message['error']}";
                    } else {
                        $errorMessages[] = $message['error'];
                    }
                }
                $errorMessage = implode(', ', $errorMessages);
            } elseif (is_string($responseBody)) {
                $errorMessage = $responseBody;
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), [$response->getStatusCode(), $response->getReasonPhrase()]);
        }
        return $errorMessage ?? null;
    }
}
