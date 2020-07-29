<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request;

use Magento\Framework\Locale\Resolver;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class ConfigDataBuilder
 * @package Viabillhq\Payment\Gateway\Request
 */
class ConfigDataBuilder extends ViabillRequestDataBuilder
{
    const VIABILL_PROTOCOL_VERSION = '3.1';

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
     * @return string
     */
    protected function getProtocol()
    {
        return self::VIABILL_PROTOCOL_VERSION;
    }

    /**
     * @return mixed
     */
    protected function getApikey()
    {
        return $this->config->getValue('apikey');
    }

    /**
     * @return mixed
     */
    protected function getSecret()
    {
        return $this->config->getValue('secret');
    }

    /**
     * @return mixed
     */
    protected function getIsTestTransaction()
    {
        return (bool) $this->config->getValue('test_transaction');
    }

    /**
     * @return mixed
     */
    protected function getLocale()
    {
        return $this->localeResolver->getLocale();
    }
}
