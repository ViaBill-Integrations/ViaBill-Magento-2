<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Source;

use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Cache\Type\Config as ConfigCache;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Gateway\Command\ViabillCommandPool;

class MyViaBill
{
    /**
     * Url to MyViabill login page.
     */
    public const VIABILL_LOGIN_URL = 'https://my.viabill.com/en/#/auth/login';

    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var string
     */
    private $myViaBillUrl;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CacheInterface
     */
    private $cache;

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
        LoggerInterface $logger,
        CacheInterface $cache      
    ) {
        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * Get MyViabill URL
     *
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
     * Get Viabill Notifications (if any)
     *
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
        $result = [];
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
        $cacheKeyTimestamp = 'viabill_notifications_last_fetch';
        $cacheKeyHash = 'viabill_notifications_last_hash';
        $cacheLifetime = 86400; // 24 hours
        $allowDuplicates = true; // allow duplicate notification messages

        try {
            // Load from cache
            $lastFetched = $this->cache->load($cacheKeyTimestamp);
            $lastHash = $this->cache->load($cacheKeyHash);

            if ($lastFetched && (time() - (int)$lastFetched) < $cacheLifetime) {
                $this->logger->info('[ViaBill] Notifications skipped due to recent fetch.');
                $this->notifications = [];
                return;
            }

            // Perform actual fetch
            $result = $this->commandPool->get(ViabillCommandPool::COMMAND_ACCOUNT_GET_NOTIFICATIONS)->execute([]);
            $messages = $result['messages'] ?? [];

            // Check for duplicate response
            $currentHash = md5(json_encode($messages));
            if (!$allowDuplicates) {
                if ($currentHash === $lastHash) {
                    $this->logger->info('[ViaBill] Duplicate notification response detected — skipping.');
                    $this->notifications = [];
                    return;
                }
            }            

            // Save new hash + timestamp
            $this->cache->save($currentHash, $cacheKeyHash, [ConfigCache::CACHE_TAG], 0);
            $this->cache->save((string)time(), $cacheKeyTimestamp, [ConfigCache::CACHE_TAG], $cacheLifetime);

            $this->notifications = $messages;

        } catch (\Exception $e) {
            $this->logger->critical('[ViaBill] Notification fetch error: ' . $e->getMessage());
            $this->notifications = [];
        }
    }

}
