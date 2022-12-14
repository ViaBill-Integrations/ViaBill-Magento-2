<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request\Refund;

use Magento\Payment\Gateway\ConfigInterface;
use Viabillhq\Payment\Gateway\Request\SubjectReader;
use Viabillhq\Payment\Gateway\Request\ViabillRequestDataBuilder;
use Viabillhq\Payment\Model\TransactionProvider;
use Viabillhq\Payment\Gateway\Exception\ViabillException;

class TransactionDataBuilder extends ViabillRequestDataBuilder
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var TransactionProvider
     */
    private $transactionProvider;

    /**
     * TransactionDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param TransactionProvider $transactionProvider
     * @param array $requestFields
     */
    public function __construct(
        SubjectReader $subjectReader,
        TransactionProvider $transactionProvider,
        array $requestFields
    ) {
        parent::__construct($requestFields);
        $this->subjectReader = $subjectReader;
        $this->transactionProvider = $transactionProvider;
    }

    /**
     * Get currency
     *
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getCurrency(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        return $paymentDO->getOrder()->getCurrencyCode();
    }

    /**
     * Get Amount
     *
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getAmount(array $buildSubject)
    {
        $amount = $this->subjectReader->readAmount($buildSubject);
        if ($amount) {
            return (string) round($amount, 2);
        } else {
            return 0;
        }
    }

    /**
     * Get invoice amount
     *
     * @param array $buildSubject
     *
     * @return string
     */
    protected function getInvoiceAmount(array $buildSubject)
    {
        return $this->getAmount($buildSubject);
    }

    /**
     * Get payment transaction id
     *
     * @param array $buildSubject
     *
     * @return string
     * @throws ViabillException
     */
    protected function getPaymentTransactionId(array $buildSubject)
    {
        $payment = $this->subjectReader->readPayment($buildSubject)->getPayment();
        $transaction = $this->transactionProvider->getPaymentAuthorizeTransaction($payment->getId());
        if (!$transaction) {
            throw new ViabillException(
                __('Order doesn\'t have Viabill transaction and can\'t be invoiced right now')
            );
        }
        return (string) $transaction->getTxnId();
    }

    /**
     * Get order authorized transaction id
     *
     * @param array $buildSubject
     *
     * @return mixed
     */
    protected function getOrderAuthorizeTransactionId(array $buildSubject)
    {
        $order = $this->subjectReader->readOrder($buildSubject);
        $transaction = $this->transactionProvider->getPaymentAuthorizeTransaction($order->getPayment()->getId());
        return $transaction->getTxnId();
    }
}
