<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;

class AccountField extends \Magento\Config\Block\System\Config\Form\Field
{
    public const FRONTEND_CLASS_DISABLED_ON_LOG_IN = 'disabled_on_log_in';
    public const FRONTEND_CLASS_REQUIRED = 'account_required';

    /**
     * Render element html
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        if ($this->isDisabled($element)) {
            $element->setReadonly(true, true);
        }

        $isRequiredHtml = $this->isRequired($element) ? '<span class="viabill-asterisk">*</span>' : '';

        $html = '<td class="label"><label for="' .
            $element->getHtmlId() . '"><span' .
            $this->_renderScopeLabel($element) . '>' .
            $element->getLabel() .
            '</span></label>' . $isRequiredHtml . '</td>';
        $html .= $this->_renderValue($element);

        $html .= $this->_renderHint($element);

        return $this->_decorateRowHtml($element, $html);
    }

    /**
     * Check if required
     *
     * @param AbstractElement $element
     *
     * @return bool
     */
    private function isRequired(AbstractElement $element)
    {
        return $this->hasClass($element, self::FRONTEND_CLASS_REQUIRED);
    }

    /**
     * Check if disabled
     *
     * @param AbstractElement $element
     *
     * @return bool
     */
    private function isDisabled(AbstractElement $element)
    {
        $key = $this->getConfigData(AccountConfiguration::CONFIG_PATH_VIABILL_API_KEY);
        return (!empty($key) && $this->hasClass($element, self::FRONTEND_CLASS_DISABLED_ON_LOG_IN));
    }

    /**
     * Check if has class
     *
     * @param AbstractElement $element
     * @param string $frontendClass
     *
     * @return bool
     */
    private function hasClass(AbstractElement $element, $frontendClass)
    {
        $elementData = $element->getOriginalData();
        if (array_key_exists('frontend_class', $elementData)) {
            $classes = explode(' ', $elementData['frontend_class']);
            foreach ($classes as $class) {
                if ($class === $frontendClass) {
                    return true;
                }
            }
        }
        return false;
    }
}
