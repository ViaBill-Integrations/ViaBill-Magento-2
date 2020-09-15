<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Plugin\Payment\Gateway\Command;

/**
 * Class CommandCodeDeclaration
 * @package Viabillhq\Payment\Plugin
 */
class CommandPool
{
    /**
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


