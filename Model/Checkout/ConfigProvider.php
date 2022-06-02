<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Payment\Gateway\ConfigInterface;
use Viabillhq\Payment\Model\PriceTag\PriceTagDataProvider;
use Viabillhq\Payment\Model\UrlProvider;

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
    
    /**
     * @var Repository
     */
    private $repository;

    /**
     * SessionWrapper constructor.
     *
     * @param UrlProvider $urlProvider
     * @param PriceTagDataProvider $priceTag
     * @param ConfigInterface $config
     * @param Repository $repository
     */
    public function __construct(
        UrlProvider $urlProvider,
        PriceTagDataProvider $priceTag,
        ConfigInterface $config,
        Repository $repository
    ) {
        $this->urlProvider = $urlProvider;
        $this->priceTag = $priceTag;
        $this->config = $config;
        $this->repository = $repository;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {

        $logo_filename = 'ViaBill_Logo';
        $lang = strtoupper($this->priceTag->getDataLanguage());
        switch ($lang) {
            case 'EN': $logo_filename ='ViaBill_Logo_EN'; break;
            case 'ES': $logo_filename ='ViaBill_Logo_ES'; break;
            case 'DA': $logo_filename ='ViaBill_Logo_DA'; break;
            default: $logo_filename ='ViaBill_Logo'; break;
        }
        
        $config = [
            'payment' => [
                'viabill' => [
                    'authorizeUrl' => $this->getAuthorizeUrl(),
                    'description' => $this->config->getValue('description'),
                    'logo' => $this->repository->getUrl('Viabillhq_Payment::images/'.$logo_filename.'.png'),
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
     * Get Authorize URL
     *
     * @return string
     */
    private function getAuthorizeUrl()
    {
        return $this->urlProvider->getUrl('viabill/payment/authorize', ['_secure' => true]);
    }
}
