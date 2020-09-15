<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\OrderManagement;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Sales\Model\Order\Payment\Processor as PaymentProcessor;
use Viabillhq\Payment\Model\Checkout\SessionWrapper;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class OrderPlace
 * @package Viabillhq\Model\OrderManagement
 */
class OrderManager
{
    /**
     * Warning message for Transaction Test Mode enabled.
     */
    const WARNING_MESSAGE = 'This order was placed in ViaBill Test Mode and should not be shipped! '.
                            'If you have any questions about this order, ' .
                            'please contact <a href=https://viabill.com/ target=_blank>ViaBill Support</a>.';

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var OrderManagementInterface
     */
    private $orderManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var PaymentProcessor
     */
    private $paymentProcessor;

    /**
     * @var SessionWrapper
     */
    private $sessionWrapper;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * OrderManager constructor.
     *
     * @param CartManagementInterface $cartManagement
     * @param ConfigInterface $config
     * @param OrderRepositoryInterface $orderRepository
     * @param PaymentProcessor $paymentProcessor
     * @param OrderManagementInterface $orderManagement
     * @param SessionWrapper $sessionWrapper
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        CartManagementInterface $cartManagement,
        ConfigInterface $config,
        OrderRepositoryInterface $orderRepository,
        PaymentProcessor $paymentProcessor,
        OrderManagementInterface $orderManagement,
        SessionWrapper $sessionWrapper,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->cartManagement = $cartManagement;
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->paymentProcessor = $paymentProcessor;
        $this->orderManagement = $orderManagement;
        $this->sessionWrapper = $sessionWrapper;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param Quote $quote
     *
     * @return OrderInterface
     * @throws CouldNotSaveException
     */
    public function placeOrder(Quote $quote) : OrderInterface
    {
        $quote->collectTotals();
        $this->sessionWrapper->determineCheckoutMethod($quote);
        $orderId = $this->cartManagement->placeOrder($quote->getId());

        /** @var Order $order */
        $order = $this->orderRepository->get($orderId);

        $state = Order::STATE_NEW;
        $status = $this->config->getValue('order_status_before_authorization');

        if (!$status) {
            $status = $order->getConfig()->getStateDefaultStatus($state);
        }

        $order->setState($state);
        $order->setStatus($status);

        $this->orderRepository->save($order);

        if ($this->testTransactionCheck() == true) {
            $order = $this->orderRepository->get($order->getId());
            $order->addStatusHistoryComment(self::WARNING_MESSAGE);
            $order->setCustomerNote(self::WARNING_MESSAGE);

            $this->orderRepository->save($order);
        }

        return $order;
    }

    /**
     * @param OrderInterface $order
     * @param $transactionId
     * @param $amountAuthorized
     */
    public function performAuthorize(OrderInterface $order, $transactionId, $amountAuthorized)
    {
        /** @var \Magento\Sales\Model\Order $order */
        if ($order->getState() !== Order::STATE_NEW) {
            return;
        }

        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setTransactionId($transactionId);
        $payment->setIsTransactionClosed(false);
        $this->paymentProcessor->authorize($payment, false, $amountAuthorized);
        $payment->setAmountAuthorized($amountAuthorized);
        $order->setState(Order::STATE_PENDING_PAYMENT);
        if ($this->config->getValue('order_status_after_authorization')) {
            $order->setStatus($this->config->getValue('order_status_after_authorization'));
        } else {
            $order->setStatus($order->getConfig()->getStateDefaultStatus(Order::STATE_PENDING_PAYMENT));
        }
        $this->orderRepository->save($order);
    }

    /**
     * Cancel current order by session data
     *
     * @param $orderId
     *
     * @param string $comment
     *
     * @return bool
     */
    public function cancelOrder($orderId, $comment = '')
    {
        $order = $this->orderRepository->get($orderId);
        if ($order->getId() && $order->getState() != Order::STATE_CANCELED) {
            $order->registerCancellation($comment)->save();
            return true;
        }
    }

    /**
     * @param OrderInterface $order
     */
    public function setPendingPaymentState(OrderInterface $order)
    {
        $order->setState(Order::STATE_PENDING_PAYMENT);
        $this->orderRepository->save($order);
    }

    /**
     * Capture.
     *
     * @param OrderInterface $order
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function performCapture(OrderInterface $order)
    {
        // preconditions
        $totalDue = $order->getTotalDue();
        $baseTotalDue = $order->getBaseTotalDue();

        /** @var Payment $payment */
        $payment = $order->getPayment();
        $payment->setAmountAuthorized($totalDue);
        $payment->setBaseAmountAuthorized($baseTotalDue);

        // do capture
        $this->paymentProcessor->capture($payment, null);
        if ($this->config->getValue('order_status_after_capture')
            && $payment->getOrder()->getStatus() != Order::STATUS_FRAUD) {
            $order->setStatus($this->config->getValue('order_status_after_capture'));
        }
        $this->orderRepository->save($order);
    }

    /**
     * Send order conformation email if not sent
     *
     * @param Order|OrderInterface $order
     */
    public function notify($order)
    {
        if ($order->getCanSendNewEmailFlag() && !$order->getEmailSent()) {
            $this->orderManagement->notify($order->getEntityId());
        }
    }

    /**
     * Check if test transaction is enabled.
     *
     * @return bool
     */
    private function testTransactionCheck()
    {
        return $this->scopeConfig->getValue(
            'payment/viabill/test_transaction',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
