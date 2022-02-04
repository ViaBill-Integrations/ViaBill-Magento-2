<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\View\Element\Template;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Viabillhq\Payment\Model\PriceTag\PriceTagDataProvider;

class ViewModel implements ArgumentInterface
{
    /**
     * @var PriceTagDataProvider
     */
    private $priceTag;

    /**
     * ViewModel constructor.
     *
     * @param PriceTagDataProvider $priceTag
     */
    public function __construct(
        PriceTagDataProvider $priceTag
    ) {
        $this->priceTag = $priceTag;
    }

    /**
     * Get Data Currency
     *
     * @return string
     */
    public function getDataCurrency()
    {
        return $this->priceTag->getDataCurrency();
    }

    /**
     * Get Data Country Code
     *
     * @return string
     */
    public function getDataCountryCode()
    {
        return $this->priceTag->getDataCountryCode();
    }

    /**
     * Get Data Language
     *
     * @return string
     */
    public function getDataLanguage()
    {
        return $this->priceTag->getDataLanguage();
    }

    /**
     * Get PriceTags Script
     *
     * @return string
     */
    public function getPriceTagScript()
    {
        return strip_tags($this->priceTag->getPriceTagScript());
    }
}
