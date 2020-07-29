<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Gateway\Command\ViabillCommandPool;

class MyViaBill
{
    /**
     * Url to MyViabill login page.
     */
    const VIABILL_LOGIN_URL = 'https://my.viabill.com/en/#/auth/login';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var string
     */
    private $myViaBillUrl;

    /**
     * @var string
     */
    private $logger;

    /**
     * @var array
     */
    private $notifications;

    /**
     * MyViaBill constructor.
     *
     * @param CommandPoolInterface $commandPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        CommandPoolInterface $commandPool,
        LoggerInterface $logger
    ) {
        $this->commandPool = $commandPool;
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getMyViaBillUrl()
    {
        if (!$this->myViaBillUrl) {
            $this->loadMyViaBillUrl();
        }
        return $this->myViaBillUrl;
    }

    /**
     * @return array
     */
    public function getViaBillNotifications()
    {
        if (!$this->notifications) {
            $this->loadNotifications();
        }
        return $this->notifications;
    }

    /**
     * Loads MyViaBill Url
     */
    private function loadMyViaBillUrl()
    {
        try {
            $result = $this->commandPool->get(ViabillCommandPool::COMMAND_ACCOUNT_MY_VIABILL)->execute([]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        } finally {
            $this->myViaBillUrl = $result['url'] ?? self::VIABILL_LOGIN_URL;
        }
    }

    /**
     * Loads ViaBill Notifications
     */
    private function loadNotifications()
    {
        try {
            $result = $this->commandPool->get(ViabillCommandPool::COMMAND_ACCOUNT_GET_NOTIFICATIONS)->execute([]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        } finally {
            $this->notifications = $result['messages'] ?? [];
        }
    }
}
