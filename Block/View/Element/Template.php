<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\View\Element;

class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * Convert to html
     *
     * @return string
     */
    public function _toHtml()
    {
        $store = $this->_storeManager->getStore();
        if (!$store->getConfig('payment/viabill/active')) {
            return '';
        }
        return parent::_toHtml();
    }
}
