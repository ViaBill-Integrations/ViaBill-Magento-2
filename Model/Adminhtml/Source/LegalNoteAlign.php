<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

class LegalNoteAlign implements \Magento\Framework\Option\ArrayInterface
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
                'value' => '',
                'label' => __('Theme default')
            ],
            [
                'value' => 'left',
                'label' => __('Left')
            ],
            [
                'value' => 'center',
                'label' => __('Center')
            ],
            [
                'value' => 'right',
                'label' => __('Right')
            ],
        ];
    }
}
