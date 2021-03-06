<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Command;

use Zend\Http\Response as ZendResponse;

class AuthorizeCommand extends GatewayCommand
{
    /**
     * @param ZendResponse $response
     *
     * @return bool
     */
    protected function canProceed(ZendResponse $response) : bool
    {
        return $response->isRedirect();
    }
}
