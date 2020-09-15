<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Product;

use Viabillhq\Payment\Model\PriceTag\PriceTagDataProvider;

/**
 * Class View
 * @package Viabillhq\Payment\Block\Product
 */
class ViewModel
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
     * @return string
     */
    public function getDataCurrency()
    {
        return $this->priceTag->getDataCurrency();
    }

    /**
     * @return string
     */
    public function getDataCountryCode()
    {
        return $this->priceTag->getDataCountryCode();
    }

    /**
     * @return string
     */
    public function getDataLanguage()
    {
        return $this->priceTag->getDataLanguage();
    }
}
