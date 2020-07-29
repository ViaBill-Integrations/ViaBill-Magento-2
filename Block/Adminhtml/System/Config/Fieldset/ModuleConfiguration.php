<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;

/**
 * Class ModuleConfiguration
 * @package Viabillhq\Payment\Block\Adminhtml\System\Config\Field
 */
class ModuleConfiguration extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if (!empty($this->_scopeConfig->getValue(
            AccountConfiguration::CONFIG_PATH_VIABILL_API_KEY,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ))) {
            return parent::render($element);
        }
    }
}
