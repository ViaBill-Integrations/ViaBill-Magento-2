<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

class DebugLevels implements \Magento\Framework\Option\ArrayInterface
{
    const DEBUG_LEVEL_PRIORITY_NONE = 0;
    const DEBUG_LEVEL_PRIORITY_BASIC = 1;
    const DEBUG_LEVEL_PRIORITY_DEVELOPER = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('--Please Select--')],
            ['value' => self::DEBUG_LEVEL_PRIORITY_NONE, 'label' => __('None')],
            ['value' => self::DEBUG_LEVEL_PRIORITY_BASIC, 'label' => __('Basic')],
            ['value' => self::DEBUG_LEVEL_PRIORITY_DEVELOPER, 'label' => __('Developer')],
        ];
    }
}
