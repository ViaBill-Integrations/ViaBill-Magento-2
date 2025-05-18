<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request;

use Magento\Framework\Locale\Resolver;
use Magento\Payment\Gateway\ConfigInterface;

class ConfigDataBuilder extends ViabillRequestDataBuilder
{
    public const VIABILL_PROTOCOL_VERSION = '3.1';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var Resolver
     */
    private $localeResolver;

    /**
     * ConfigDataBuilder constructor.
     *
     * @param ConfigInterface $config
     * @param Resolver $localeResolver
     * @param array $requestFields
     */
    public function __construct(
        ConfigInterface $config,
        Resolver $localeResolver,
        array $requestFields
    ) {
        parent::__construct($requestFields);
        $this->config = $config;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get Protocol
     *
     * @return string
     */
    protected function getProtocol()
    {
        return self::VIABILL_PROTOCOL_VERSION;
    }

    /**
     * Get API Key
     *
     * @return mixed
     */    
    protected function getApikey()
    {
        $apiKey = $this->config->getValue('apikey');
       
        return $apiKey;
    }

    /**
     * Get Secret
     *
     * @return mixed
     */
    protected function getSecret()
    {
        return $this->config->getValue('secret');
    }

    /**
     * Check if this is a test transaction
     *
     * @return mixed
     */
    protected function getIsTestTransaction()
    {
        return (bool) $this->config->getValue('test_transaction');
    }

    /**
     * Get Locale
     *
     * @return mixed
     */
    protected function getLocale()
    {
        return $this->localeResolver->getLocale();
    }
}
