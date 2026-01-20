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
use Monolog\LogRecord;

/**
 * ViaBill debug log handler.
 *
 * Uses module configuration to decide whether a given record should be handled.
 */
class Debug extends Base
{
    /**
     * Logging level for this handler.
     *
     * @var int
     */
    protected $loggerType = Logger::DEBUG;

    /**
     * Log file path (relative to Magento root).
     *
     * @var string
     */
    protected $fileName = '/var/log/viabill_debug.log';

    /**
     * Module configuration.
     *
     * @var ConfigInterface
     */
    private $config;

    /**
     * Debug constructor.
     *
     * @param DriverInterface  $filesystem Filesystem driver used by the base handler
     * @param ConfigInterface  $config     Module configuration provider
     * @param string|null      $filePath   Optional custom file path
     */
    public function __construct(
        DriverInterface $filesystem,
        ConfigInterface $config,
        $filePath = null
    ) {
        $this->config = $config;
        parent::__construct($filesystem, $filePath);
    }

    /**
     * Determine if this handler should handle the given record.
     *
     * In Monolog 3 the record is a LogRecord object.
     *
     * @param LogRecord $record
     * @return bool
     */
    public function isHandling($record): bool
    {
        // Read module debug level from config
        $config_debug_level = (int) $this->config->getValue('debug');
        if ($config_debug_level <= 0) {
            return false;
        }

        // Optional per-record debug level from context
        $record_debug_level = 0;
        if (isset($record->context['debug_level'])) {
            $record_debug_level = (int) $record->context['debug_level'];
        }

        // If record specifies a debug_level, honor it against config
        if ($record_debug_level && ($config_debug_level >= $record_debug_level)) {
            return true;
        }

        // Monolog 3
        if ($record instanceof LogRecord) {
            // use $record->level, $record->message, etc.
            return parent::isHandling($record);
        }

        // Monolog 2 (array record)
        if (is_array($record)) {
            return parent::isHandling($record);
        }

        return false;
    }

}
