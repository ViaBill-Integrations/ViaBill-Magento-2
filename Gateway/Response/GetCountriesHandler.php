<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Viabillhq\Payment\Model\Adminhtml\Source\Country;

class GetCountriesHandler implements HandlerInterface
{
    /**
     * @var Country
     */
    private $country;

    /**
     * @param Country $country
     */
    public function __construct(Country $country)
    {
        $this->country = $country;
    }

    /**
     * @inheritdoc
     *
     * @param array $handlingSubject
     * @param array $responseBody
     */
    public function handle(array $handlingSubject, array $responseBody)
    {
        $this->country->setOptions($responseBody);
    }
}
