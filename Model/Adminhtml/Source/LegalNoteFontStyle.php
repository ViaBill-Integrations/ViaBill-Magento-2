<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

class LegalNoteFontStyle implements \Magento\Framework\Option\ArrayInterface
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
                'value' => 'normal',
                'label' => __('Normal')
            ],
            [
                'value' => 'bold',
                'label' => __('Bold')
            ],
            [
                'value' => 'italic',
                'label' => __('Italic')
            ],
            [
                'value' => 'bold-italic',
                'label' => __('Bold italic')
            ],
        ];
    }
}
