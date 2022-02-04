<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Exception;

use Magento\Framework\Exception\LocalizedException;

/**
 * Exception type when message is ready to be displayed for customer
 */
class ViabillException extends LocalizedException
{
    public const DEFAULT_CODE = 400;
    public const DEFAULT_MESSAGE =
        'Couldn\'t process this request. Please try again later or contact a store administrator.';
}
