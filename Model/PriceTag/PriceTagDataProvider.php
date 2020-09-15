<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Viabillhq\Payment\Model\PriceTag;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\Resolver as LocaleResolver;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\ScopeInterface;

class PriceTagDataProvider
{
    /**
     * Country config path.
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var LocaleResolver
     */
    private $localeResolver;

    /**
     * @var StoreInterface
     */
    private $store;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Allowed EU countries.
     *
     * @var array
     */
    private $allowedEUCountries = ['ES'];

    /**
     * PriceTagDataProvider constructor.
     *
     * @param ConfigInterface $config
     * @param LocaleResolver $localeResolver
     * @param StoreInterface $store
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ConfigInterface $config,
        LocaleResolver $localeResolver,
        StoreInterface $store,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->localeResolver = $localeResolver;
        $this->store = $store;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return string
     */
    public function getDataCurrency()
    {
        return $this->store->getCurrentCurrencyCode();
    }

    /**
     * Get country code if it is allowed.
     *
     * @return string
     */
    public function getDataCountryCode()
    {
        $countryCode = $this->getCountryByWebsite();
        if (in_array($countryCode, $this->allowedEUCountries)) {
            return $countryCode;
        } else {
            return null;
        }
    }

    /**
     * @return string
     */
    public function getDataLanguage()
    {
        $lang = $this->localeResolver->getLocale();

        return strstr($lang, '_', true);
    }

    /**
     * @return mixed
     */
    public function getPriceTagScript()
    {
        return $this->config->getValue('price_tag_script');
    }

    /**
     * Get Country code by website scope
     *
     * @return string
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
