<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Command;

use Magento\Payment\Gateway\Command\CommandPool;

class ViabillCommandPool extends CommandPool //implements CommandPoolInterface
{
    const COMMAND_AUTHORIZE = 'authorize';
    const COMMAND_CAPTURE = 'capture';
    const COMMAND_REFUND = 'refund';
    const COMMAND_CANCEL = 'cancel';
    const COMMAND_GET_COUNTRIES = 'getCountries';
    const COMMAND_ACCOUNT_REGISTER = 'register';
    const COMMAND_ACCOUNT_LOGIN = 'login';
    const COMMAND_ACCOUNT_MY_VIABILL = 'myViabill';
    const COMMAND_ACCOUNT_GET_NOTIFICATIONS = 'getNotifications';
    const COMMAND_RENEW = 'renew';
}
