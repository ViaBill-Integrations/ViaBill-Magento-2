<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

/**
 * Class Notifications
 * @package Viabillhq\Payment\Model\Adminhtml\Source
 */
class Notifications implements \Magento\Framework\Option\ArrayInterface
{
    const NOTIFICATION_PRIORITY_NOTICE = 1;
    const NOTIFICATION_PRIORITY_MINOR = 2;
    const NOTIFICATION_PRIORITY_MAJOR = 3;
    const NOTIFICATION_PRIORITY_CRITICAL = 4;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('--Please Select--')],
            ['value' => self::NOTIFICATION_PRIORITY_CRITICAL, 'label' => __('Critical')],
            ['value' => self::NOTIFICATION_PRIORITY_MAJOR, 'label' => __('Major')],
            ['value' => self::NOTIFICATION_PRIORITY_MINOR, 'label' => __('Minor')],
            ['value' => self::NOTIFICATION_PRIORITY_NOTICE, 'label' => __('Notice')]
        ];
    }
}
