<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Observer\Backend\Model\Auth;

use Magento\Framework\Event\ObserverInterface;
use Viabillhq\Payment\Model\Adminhtml\AdminNotification;

class ViabillNotifications implements ObserverInterface
{
    /**
     * @var AdminNotification
     */
    private $adminNotification;

    /**
     * ViabillNotifications constructor.
     *
     * @param AdminNotification $adminNotification
     */
    public function __construct(
        AdminNotification $adminNotification
    ) {
        $this->adminNotification = $adminNotification;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->adminNotification->registerAdminNotifications();
    }
}