<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml;

use Magento\Framework\Notification\NotifierInterface as NotifierPool;
use Viabillhq\Payment\Model\Adminhtml\Source\MyViaBill;
use Viabillhq\Payment\Model\Adminhtml\Source\Notifications;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\UrlInterface;

/**
 * Class AdminNotification
 * @package Viabillhq\Payment\Model\Adminhtml\Source
 */
class AdminNotification
{
    /**
     * @var NotifierPool
     */
    private $notifierPool;

    /**
     * @var MyViaBill
     */
    private $myViaBill;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * ViabillNotifications constructor.
     *
     * @param ConfigInterface $config
     * @param NotifierPool $notifierPool
     * @param MyViaBill $myViaBill
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ConfigInterface $config,
        NotifierPool $notifierPool,
        MyViaBill $myViaBill,
        UrlInterface $urlBuilder
    ) {
        $this->config = $config;
        $this->notifierPool = $notifierPool;
        $this->myViaBill = $myViaBill;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get messages from ViaBill and register admin notifications.
     */
    public function registerAdminNotifications()
    {
        $notifications = $this->myViaBill->getViaBillNotifications();
        foreach ($notifications as $message) {
            $this->addNotification($message);
        }
    }

    /**
     * @param string $message
     */
    private function addNotification($message = '')
    {
        $url = $this->urlBuilder->getUrl('adminhtml/notification/index');
        switch ($this->config->getValue('admin_notifications_severity')) {
            case Notifications::NOTIFICATION_PRIORITY_NOTICE:
                $this->notifierPool->addNotice('ViaBill', $message, $url, true);
                break;
            case Notifications::NOTIFICATION_PRIORITY_MINOR:
                $this->notifierPool->addMinor('ViaBill', $message, $url, true);
                break;
            case Notifications::NOTIFICATION_PRIORITY_MAJOR:
                $this->notifierPool->addMajor('ViaBill', $message, $url, true);
                break;
            case Notifications::NOTIFICATION_PRIORITY_CRITICAL:
                $this->notifierPool->addCritical('ViaBill', $message, $url, true);
                break;
            default:
                $this->notifierPool->addCritical('ViaBill', $message, $url, true);
        }
    }
}
