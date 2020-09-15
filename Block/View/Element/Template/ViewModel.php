<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\View\Element\Template;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Viabillhq\Payment\Model\PriceTag\PriceTagDataProvider;

/**
 * Class ViewModel
 * @package Viabillhq\Payment\Block\View\Element\Template
 */
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

    /**
     * @return string
     */
    public function getPriceTagScript()
    {
        return strip_tags($this->priceTag->getPriceTagScript());
    }
}
