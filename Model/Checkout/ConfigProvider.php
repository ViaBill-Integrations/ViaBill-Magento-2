<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Viabillhq\Payment\Model\PriceTag\PriceTagDataProvider;
use Viabillhq\Payment\Model\UrlProvider;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * @var PriceTagDataProvider
     */
    private $priceTag;

    /**
     * @var ConfigInterface
     */
    private $config;

    public function __construct(
        UrlProvider $urlProvider,
        PriceTagDataProvider $priceTag,
        ConfigInterface $config
    ) {
        $this->urlProvider = $urlProvider;
        $this->priceTag = $priceTag;
        $this->config = $config;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $config = [
            'payment' => [
                'viabill' => [
                    'authorizeUrl' => $this->getAuthorizeUrl(),
                    'description' => $this->config->getValue('description'),
                    'priceTagScript' => $this->config->getValue('price_tag_script'),
                    'priceTag' => [
                        'language' => $this->priceTag->getDataLanguage(),
                        'currency' => $this->priceTag->getDataCurrency(),
                        'countryCode' => $this->priceTag->getDataCountryCode()
                    ]
                ]
            ]
        ];

        return $config;
    }

    /**
     * @return string
     */
    private function getAuthorizeUrl()
    {
        return $this->urlProvider->getUrl('viabill/payment/authorize', ['_secure' => true]);
    }
}
