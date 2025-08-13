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
    public const TRY_BEFORE_YOU_BUY_SHOW_SETTING_OPTION = 0;

    /**
     * Render element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string|null
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $elementId = $element->getId();

        // If this is "try before you buy" block
        if (!empty($elementId) && strpos($elementId, 'viabill_try') !== false) {
            if (!self::TRY_BEFORE_YOU_BUY_SHOW_SETTING_OPTION) {
                return null;
            }

            $countryCode = $this->_scopeConfig->getValue(
                AccountConfiguration::CONFIG_PATH_VIABILL_ACCOUNT_COUNTRY,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
            if (!empty($countryCode)) {
                $countryCode = strtoupper($countryCode);
                // Skip for restricted countries
                if ($countryCode === 'ES'
                    || $countryCode === 'ESP'
                    || $countryCode === 'SPAIN'
                ) {
                    return null;
                }
            }
        }

        $apiKey = $this->_scopeConfig->getValue(
            AccountConfiguration::CONFIG_PATH_VIABILL_API_KEY,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        if (empty($apiKey)) {
            return null;
        }

        return parent::render($element);
    }
}
