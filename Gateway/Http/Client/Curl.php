<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Http\Client;

use Magento\Framework\HTTP\Adapter\Curl as MagentoCurl;
use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Psr\Log\LoggerInterface;
use Zend\Http\Response as ZendResponse;

/**
 * Class Curl
 */
class Curl implements ClientInterface
{
    /**
     * @var CurlFactory
     */
    private $adapterFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    public $headers = [];

    /**
     * Curl constructor.
     *
     * @param CurlFactory $adapterFactory
     * @param Json $jsonSerializer
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurlFactory $adapterFactory,
        Json $jsonSerializer,
        LoggerInterface $logger
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
    }

    /**
     * @param TransferInterface $transfer
     *
     * @return array
     * @throws \Exception
     */
    public function placeRequest(TransferInterface $transfer)
    {
        try {
            $adapter = null;
            /** @var MagentoCurl $adapter */
            $adapter = $this->adapterFactory->create();
            $options = $this->getBasicOptions($transfer);
            $adapter->setOptions($options);

            // set request params
            $adapter->write(
                $transfer->getMethod(),
                $transfer->getUri(),
                '1.1',
                $this->getHeaders($transfer->getHeaders()),
                $this->jsonSerializer->serialize($transfer->getBody())
            );
            // send request
            $responseSting = $adapter->read();
            $response = ZendResponse::fromString($responseSting);
            if ($response->getStatusCode() === ZendResponse::STATUS_CODE_302) {
                $response->setContent(
                    $this->jsonSerializer->serialize(['url' => $this->getRedirectUrl()])
                );
            }
            return ['response' => $response];
        } catch (\Throwable $t) {
            $this->logger->critical($t->__toString());
            throw new \Exception($t->getMessage(), $t->getCode(), $t); //@codingStandardsIgnoreLine
        } finally {
            $adapter ? $adapter->close() : null;
        }
    }

    /**
     * @param $curl
     * @param $headerLine
     *
     * @return int
     */
    public function handleResponseHeaders($curl, $headerLine)
    {
        $this->headers[] = $headerLine;
        return strlen($headerLine);
    }

    /**
     * @param TransferInterface $transfer
     *
     * @return array
     */
    private function getBasicOptions($transfer)
    {
        return $transfer->getClientConfig() + [
            CURLOPT_TIMEOUT => '30',
            CURLOPT_HEADERFUNCTION => [&$this, 'handleResponseHeaders']
        ];
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    private function getHeaders(array $headers) : array
    {
        return array_merge(['content-type: application/json'], $headers);
    }

    /**
     * @return string | null
     */
    private function getRedirectUrl()
    {
        foreach ($this->headers as $headerString) {
            if (false !== strpos($headerString, 'Location')) {
                $location = explode(' ', $headerString);
                return $location[1] ?? null;
            }
        }
    }
}
