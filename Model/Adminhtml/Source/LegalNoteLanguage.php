<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

use Viabillhq\Payment\Model\LegalNote;

class LegalNoteLanguage implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => LegalNote::LANGUAGE_AUTO,
                'label' => __('Auto')
            ],
            [
                'value' => 'en',
                'label' => __('English')
            ],
            [
                'value' => 'da',
                'label' => __('Danish')
            ],
            [
                'value' => 'es',
                'label' => __('Spanish')
            ],
        ];
    }
}
