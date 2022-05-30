<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Command;

use Laminas\http\Response as LaminasResponse;

class AuthorizeCommand extends GatewayCommand
{
    /**
     * Check if user can proceed
     *
     * @param LaminasResponse $response
     *
     * @return bool
     */
    protected function canProceed(LaminasResponse $response) : bool
    {
        return $response->isRedirect();
    }
}
