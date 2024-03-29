<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config\Fieldset;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;

class ModuleConfiguration extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    // Hide "try before you buy" payment option in backend settings
    public const TRY_BEFORE_YOU_BUY_SHOW_SETTING_OPTION = 1;

    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element_id = $element->getId();
        if (!empty($element_id)) {
            if (strpos($element_id, 'viabill_try')!==false) {
                if (!self::TRY_BEFORE_YOU_BUY_SHOW_SETTING_OPTION) {                    
                    return;
                } else {
                    $country_code = $this->_scopeConfig->getValue(
                        AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_COUNTRY,
                        ScopeConfigInterface::SCOPE_TYPE_DEFAULT
                    );
                    if (!empty($country_code)) {
                        $country_code = strtoupper($country_code);
                        switch ($country_code) {
                            case 'ES':
                            case 'ESP':
                            case 'SPAIN':
                                // ignore Spain for the moment,
                                // as currently is not available
                                return;
                                break;
                        }
                    }
                }
            }
        }

        if (!empty($this->_scopeConfig->getValue(
            AccountConfiguration::CONFIG_PATH_VIABILL_API_KEY,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        ))) {
            return parent::render($element);
        }
    }
}
