<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request\Authorize;

use Magento\Payment\Gateway\ConfigInterface;
use Viabillhq\Payment\Gateway\Request\SubjectReader;
use Viabillhq\Payment\Gateway\Request\ViabillRequestDataBuilder;
use Viabillhq\Payment\Model\TransactionProvider;

class TransactionDataBuilder extends ViabillRequestDataBuilder
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var TransactionProvider
     */
    private $transactionProvider;

    /**
     * TransactionDataBuilder constructor.
     *
     * @param ConfigInterface $config
     * @param SubjectReader $subjectReader
     * @param TransactionProvider $transactionProvider
     * @param array $requestFields
     */
    public function __construct(
        ConfigInterface $config,
        SubjectReader $subjectReader,
        TransactionProvider $transactionProvider,
        array $requestFields
    ) {
        parent::__construct($requestFields);
        $this->config = $config;
        $this->subjectReader = $subjectReader;
        $this->transactionProvider = $transactionProvider;
    }

    /**
     * @param array $buildSubject
     *
     * @return mixed
     */
    protected function getOrderIncrementId(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        return $order->getIncrementId();
    }

    /**
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getCurrency(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        return $order->getOrderCurrencyCode();
    }

    /**
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getAmount(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        return (string)round($order->getGrandTotal(), 2);
    }

    /**
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getAuthTransactionId(array $buildSubject)
    {
        $orderId = $this->getOrderIncrementId($buildSubject);
        return $this->transactionProvider->generateViabillTransactionId($orderId);
    }
}
