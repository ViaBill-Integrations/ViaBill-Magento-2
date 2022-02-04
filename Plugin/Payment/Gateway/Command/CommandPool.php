<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Plugin\Payment\Gateway\Command;

class CommandPool
{
    /**
     * After get
     *
     * @param \Magento\Payment\Gateway\Command\CommandPool $commandPool
     * @param \Viabillhq\Payment\Gateway\Command\GatewayCommand $command
     * @param string $commandCode
     *
     * @return \Viabillhq\Payment\Gateway\Command\GatewayCommand
     */
    public function afterGet(
        \Magento\Payment\Gateway\Command\CommandPool $commandPool,
        \Viabillhq\Payment\Gateway\Command\GatewayCommand $command,
        $commandCode = ''
    ) {
        $command->setCode($commandCode);
        return $command;
    }
}
