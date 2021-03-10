<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Logger\Handler;

use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Critical extends Base
{
    const DEFAULT_CRITICAL_FILE_NAME = '/var/log/viabill_critical.log';

    /**
     * Critical constructor.
     *
     * @param DriverInterface $filesystem
     * @param string $fileName
     * @param int $loggerType
     * @param null $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        $fileName = self::DEFAULT_CRITICAL_FILE_NAME,
        $loggerType = Logger::CRITICAL,
        $filePath = null
    ) {
        $this->fileName = $fileName;
        $this->loggerType = $loggerType;
        parent::__construct($filesystem, $filePath);
    }
}
