<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Api\Data\TransactionInterface;
use Magento\Sales\Model\Order\Payment\Transaction\Repository;
use Magento\Store\Model\StoreManagerInterface;

class TransactionProvider
{
    const PREFIX_HASH_LENGTH = 7;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Repository
     */
    private $transactionRepository;

    /**
     * TransactionProvider constructor.
     *
     * @param ConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param Repository $transactionRepository
     */
    public function __construct(
        ConfigInterface $config,
        StoreManagerInterface $storeManager,
        Repository $transactionRepository
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * Generates transaction ID based on store-specific data and current time in microseconds.
     *
     * @param $orderId
     *
     * @return string
     */
    public function generateViabillTransactionId($orderId) : string
    {
        $apikey = $this->config->getValue('apikey');
        $storeId = $this->storeManager->getStore()->getId();
        $prefix = substr(sha1($apikey . $orderId . $storeId), -self::PREFIX_HASH_LENGTH);
        return uniqid($prefix, false);
    }

    /**
     * Provides authorize transaction for current payment.
     *
     * @param int $paymentId
     * @param int $orderId - is not used on Magento method, so could be empty
     *
     * @return bool|\Magento\Framework\Model\AbstractModel|mixed
     */
    public function getPaymentAuthorizeTransaction($paymentId, $orderId = 0)
    {
        return $this->transactionRepository
            ->getByTransactionType(TransactionInterface::TYPE_AUTH, $paymentId, $orderId);
    }
}
