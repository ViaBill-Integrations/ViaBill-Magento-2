<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model;

use Magento\Framework\Url;
use Magento\Framework\UrlInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Store\Model\StoreManagerInterface;

class UrlProvider
{
    /**
     * @var string
     */
    private static $productionApiBaseUrl = 'https://secure.viabill.com';

    /**
     * @var string
     */
    private static $forgotPasswordUrl = 'https://viabill.com/auth/forgot?lang=';

    /**
     * @var string
     */
    private static $termsAndConditionsUrl = 'https://www.viabill.com/trade-terms/';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var UrlInterface|Url
     */
    private $frontendUrlBuilder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * UrlProvider constructor.
     *
     * @param ConfigInterface $config
     * @param UrlInterface $urlBuilder
     * @param UrlInterface $frontendUrlBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ConfigInterface $config,
        UrlInterface $urlBuilder,
        UrlInterface $frontendUrlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->frontendUrlBuilder = $frontendUrlBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * @return string
     */
    private function getViabillApiBaseUrl() : string
    {
        return self::$productionApiBaseUrl;
    }

    /**
     * @param $url
     *
     * @return string
     */
    public function getViabillApiUrl($url) : string
    {
        return $this->getViabillApiBaseUrl() . $url;
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     *
     * @return mixed|string
     */
    public function getCallbackUrl($routePath = null, $routeParams = null)
    {
        $url = $this->urlBuilder->getUrl($routePath, $routeParams);
        if ($this->config->getValue('debug')) {
            $externalUrl = $this->config->getValue('callback_debug_base_url');
            if (!empty($externalUrl)) {
                $url = str_replace(
                    $this->urlBuilder->getBaseUrl(),
                    $externalUrl,
                    $url
                );
                if ($this->config->getValue('callback_xdebug_session_enable')) {
                    $url .= '?XDEBUG_SESSION_START=1';
                }
            }
        }
        return $url;
    }

    /**
     * @param $localeCode
     *
     * @return string
     */
    public function getForgotPasswordUrl($localeCode) : string
    {
        return  self::$forgotPasswordUrl . $localeCode;
    }

    /**
     * @return string
     */
    public function getTermsAndConditionsUrl() : string
    {
        return self::$termsAndConditionsUrl;
    }

    /**
     * @param null $routePath
     * @param null $routeParams
     *
     * @return string
     */
    public function getUrl($routePath = null, $routeParams = null) : string
    {
        return $this->urlBuilder->getUrl($routePath, $routeParams);
    }
}
