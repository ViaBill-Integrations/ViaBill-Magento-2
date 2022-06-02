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

class TransferFactory implements TransferFactoryInterface
{
    /**
     * The ViaBill module version
     */
    public const ADDON_VERSION = '4.0.19';

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
     * @param array $request
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
        /** Add platform details, if needed */
        if (strpos($endpointUrl, '/api/addon/magento/notifications')!== false) {
            $platform = 'magento';
            // Get Magento Version
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $productMetadata =
                $objectManager->get('Magento\Framework\App\ProductMetadataInterface'); // @codingStandardsIgnoreLine
            $magento_version = $productMetadata->getVersion();
            $module_version = self::ADDON_VERSION;
            $platform_version = $magento_version;
            
            $endpointUrl = $endpointUrl .'&platform='.urlencode($platform).'&platform_ver='.urlencode($platform_version).'&module_ver='.urlencode($module_version);
        }
        return $this->urlProvider->getViabillApiUrl($endpointUrl);        
    }
}
