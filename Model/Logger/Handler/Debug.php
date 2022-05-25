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

class Debug extends Base
{

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * @var string
     */
    protected $fileName = '/var/log/viabill_debug.log';

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * Debug constructor.
     *
     * @param DriverInterface $filesystem
     * @param ConfigInterface $config
     * @param string $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        ConfigInterface $config,
        $filePath = null
    ) {
        $this->config = $config;
        /*
        $this->fileName = $fileName;
        $this->loggerType = $loggerType;
        */
        parent::__construct($filesystem, $filePath);
    }

    /**
     * @inheritdoc
     *
     * @param array $record
     *
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        $record_debug_level = 0;

        if ($this->config) {
            $config_debug_level = (int) $this->config->getValue('debug');
            if (empty($config_debug_level)) {
                return false;
            }
        } else {
            return false;
        }

        if (!empty($record)) {
            if (isset($record['context'])) {
                if (isset($record['context']['debug_level'])) {
                    $record_debug_level = (int) $record['context']['debug_level'];
                }
            }
        }
        
        if ($this->config) {
            if ($config_debug_level > 0) {
                if ($record_debug_level && ($config_debug_level >= $record_debug_level)) {
                    return true;
                }
            }
        }

        return parent::isHandling($record);
    }
}
