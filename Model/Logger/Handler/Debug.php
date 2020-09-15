<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Magento\Payment\Gateway\ConfigInterface;
use Monolog\Logger;

/**
 * Class Debug
 * @package Viabillhq\Payment\Model\Logger\Handler
 */
class Debug extends Base
{
    const DEFAULT_DEBUG_FILE_NAME = '/var/log/viabill_debug.log';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Debug constructor.
     *
     * @param DriverInterface $filesystem
     * @param ConfigInterface $config
     * @param string $fileName
     * @param int $loggerType
     * @param null $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        ConfigInterface $config,
        $fileName = self::DEFAULT_DEBUG_FILE_NAME,
        $loggerType = Logger::DEBUG,
        $filePath = null
    ) {
        $this->config = $config;
        $this->fileName = $fileName;
        $this->loggerType = $loggerType;
        parent::__construct($filesystem, $filePath);
    }

    /**
     * {@inheritdoc}
     *
     * @param array $record
     *
     * @return bool
     */
    public function isHandling(array $record)
    {
        if ($this->config && (bool) $this->config->getValue('debug')) {
            return parent::isHandling($record);
        }
        return false;
    }
}
