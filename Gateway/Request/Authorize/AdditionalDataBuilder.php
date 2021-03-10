<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request\Authorize;

use Magento\Payment\Gateway\Request\BuilderInterface;

class AdditionalDataBuilder implements BuilderInterface
{
    /**
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $buildSubject = array_merge($buildSubject, $this->getClientAuthorizeOptions());
        return $buildSubject;
    }

    /**
     * @return array
     */
    private function getClientAuthorizeOptions()
    {
        return
        [
            'clientOptions' => [
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13' // @codingStandardsIgnoreLine
            ]
        ];
    }
}
