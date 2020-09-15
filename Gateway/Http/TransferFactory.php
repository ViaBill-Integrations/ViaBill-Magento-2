<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Http;

use Magento\Payment\Gateway\Http\TransferBuilder;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Viabillhq\Payment\Model\UrlProvider;

/**
 * Class TransferFactory
 * @package Viabillhq\Payment\Gateway\Http
 */
class TransferFactory implements TransferFactoryInterface
{
    /**
     * @var TransferBuilder
     */
    private $transferBuilder;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $endpointUrl;

    /**
     * @var string[]
     */
    private $urlParams;

    /**
     * TransferFactory constructor.
     *
     * @param TransferBuilder $transferBuilder
     * @param UrlProvider $urlProvider
     * @param string $method
     * @param string $endpointUrl
     * @param array $urlParams
     */
    public function __construct(
        TransferBuilder $transferBuilder,
        UrlProvider $urlProvider,
        string $method,
        string $endpointUrl,
        array $urlParams = []
    ) {
        $this->transferBuilder = $transferBuilder;
        $this->urlProvider = $urlProvider;
        $this->method = $method;
        $this->endpointUrl = $endpointUrl;
        $this->urlParams = $urlParams;
    }

    /**
     * Builds gateway transfer object
     *
     * @param array $request
     * @return TransferInterface
     */
    public function create(array $request)
    {
        if (isset($request['clientOptions'])) {
            $this->transferBuilder->setClientConfig($request['clientOptions']);
            unset($request['clientOptions']);
        }
        return $this->transferBuilder
            ->setBody($request)
            ->setMethod($this->method)
            ->setUri($this->getUrl($request))
            ->build();
    }

    /**
     * Generating Url.
     *
     * @param $request
     *
     * @return string
     */
    private function getUrl(array $request = [])
    {
        $endpointUrl = $this->endpointUrl;
        /** Binding url parameters if they were specified */
        foreach ($this->urlParams as $paramValue) {
            if (isset($request[$paramValue])) {
                $endpointUrl = str_replace(':' . $paramValue, $request[$paramValue], $endpointUrl);
            }
        }
        return $this->urlProvider->getViabillApiUrl($endpointUrl);
    }
}
