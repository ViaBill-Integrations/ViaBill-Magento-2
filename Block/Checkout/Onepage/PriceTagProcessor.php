<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Checkout\Onepage;

use Magento\Checkout\Block\Checkout\LayoutProcessorInterface;
use Magento\Payment\Gateway\ConfigInterface;

class PriceTagProcessor implements LayoutProcessorInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * PriceTagProcessor constructor.
     *
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @param array $jsLayout
     *
     * @return array
     */
    public function process($jsLayout)
    {
        if (!$this->config->getValue('active')
            || !$this->config->getValue('price_tag_show_on_checkout')) {
            $jsLayout['components']['checkout']['children']
            ['sidebar']['children']['summary']['children']
            ['itemsBefore']['children']['viabill']['config']
            ['componentDisabled'] = true;
        }
        return $jsLayout;
    }
}
