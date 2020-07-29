<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Response;

use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Viabillhq\Payment\Model\Adminhtml\Source\Country;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;
use Viabillhq\Payment\Gateway\Request\SubjectReader;

/**
 * Class GetCredentialsHandler
 * @package Viabillhq\Payment\Gateway\Response
 */
class GetCredentialsHandler implements HandlerInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var AccountConfiguration
     */
    private $accountConfiguration;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * GetCredentialsHandler constructor.
     *
     * @param AccountConfiguration $accountConfiguration
     * @param SubjectReader $subjectReader
     * @param TypeListInterface $cacheTypeList
     */
    public function __construct(
        AccountConfiguration $accountConfiguration,
        SubjectReader $subjectReader,
        TypeListInterface $cacheTypeList
    ) {
        $this->accountConfiguration = $accountConfiguration;
        $this->subjectReader = $subjectReader;
        $this->cacheTypeList = $cacheTypeList;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $handlingSubject
     * @param array $response
     */
    public function handle(array $handlingSubject, array $responseBody)
    {
        $requestData = $this->subjectReader->readRequestData($handlingSubject);
        $configurationData = array_merge($requestData, $responseBody);
        $this->accountConfiguration->save($configurationData);
        $this->cacheTypeList->cleanType(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER);
    }
}
