<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPool;

class ViabillCommandPool extends CommandPool //implements CommandPoolInterface
{
    public const COMMAND_AUTHORIZE = 'authorize';
    public const COMMAND_CAPTURE = 'capture';
    public const COMMAND_REFUND = 'refund';
    public const COMMAND_CANCEL = 'cancel';
    public const COMMAND_GET_COUNTRIES = 'getCountries';
    public const COMMAND_ACCOUNT_REGISTER = 'register';
    public const COMMAND_ACCOUNT_LOGIN = 'login';
    public const COMMAND_ACCOUNT_MY_VIABILL = 'myViabill';
    public const COMMAND_ACCOUNT_GET_NOTIFICATIONS = 'getNotifications';
    public const COMMAND_RENEW = 'renew';
}
