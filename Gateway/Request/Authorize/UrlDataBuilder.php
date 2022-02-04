<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request\Authorize;

use Viabillhq\Payment\Gateway\Request\ViabillRequestDataBuilder;
use Viabillhq\Payment\Model\UrlProvider;

class UrlDataBuilder extends ViabillRequestDataBuilder
{
    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * UrlDataBuilder constructor.
     *
     * @param UrlProvider $urlProvider
     * @param array $requestFields
     */
    public function __construct(
        UrlProvider $urlProvider,
        array $requestFields
    ) {
        parent::__construct($requestFields);
        $this->urlProvider = $urlProvider;
    }

    /**
     * Get success url
     *
     * @return string
     */
    protected function getSuccessUrl()
    {
        return $this->urlProvider->getUrl('checkout/onepage/success');
    }

    /**
     * Get cancel url
     *
     * @return string
     */
    protected function getCancelUrl()
    {
        return $this->urlProvider->getUrl('viabill/payment/cancel');
    }

    /**
     * Get callback url
     *
     * @return mixed|string
     */
    protected function getCallbackUrl()
    {
        return $this->urlProvider->getCallbackUrl('viabill/payment/callback');
    }
}
