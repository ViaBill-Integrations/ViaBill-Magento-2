<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Model\OrderManagement\OrderLocator;
use Viabillhq\Payment\Model\OrderManagement\OrderManager;
use Viabillhq\Payment\Model\Request\SignatureGenerator;
use Zend\Http\Response;

/**
 * Class Callback
 * @package Viabillhq\Payment\Controller\Payment
 */
class Callback extends Action
{
    const VIABILL_STATUS_APPROVED = 'APPROVED';

    const VIABILL_STATUS_CANCELLED = 'CANCELLED';

    const CANCEL_MESSAGE = 'Payment cancelled from Viabill.';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ConfigInterface
     */
    private $paymentConfig;

    /**
     * @var SignatureGenerator
     */
    private $signatureGenerator;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var OrderLocator
     */
    private $orderLocator;

    /**
     * Callback constructor.
     *
     * @param Context $context
     * @param OrderManager $orderManager
     * @param OrderLocator $orderLocator
     * @param SerializerInterface $jsonSerializer
     * @param ConfigInterface $paymentConfig
     * @param SignatureGenerator $signatureGenerator
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        OrderManager $orderManager,
        OrderLocator $orderLocator,
        SerializerInterface $jsonSerializer,
        ConfigInterface $paymentConfig,
        SignatureGenerator $signatureGenerator,
        LoggerInterface $logger
    ) {
        $this->orderManager = $orderManager;
        $this->orderLocator = $orderLocator;
        $this->jsonSerializer = $jsonSerializer;
        $this->paymentConfig = $paymentConfig;
        $this->signatureGenerator = $signatureGenerator;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $request = $this->getRequest()->getContent();
            $requestData = $this->jsonSerializer->unserialize($request);
            $this->validateRequest($requestData);
            $this->checkSignature($requestData);

            $orderId = $requestData['orderNumber'];
            $order = $this->orderLocator->get($orderId);
            $this->processOrder($order, $requestData);
            $result->setHttpResponseCode(Response::STATUS_CODE_204);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result->setHttpResponseCode($e->getCode());
            $result->setData([
                'status' => $e->getCode(),
                'message' => __('An error occurred during callback processing.')
            ]);
        } finally {
            $this->logger->debug('Callback Request Content: ', [$this->getRequest()->getContent()]);
        }
        return $result;
    }

    /**
     * @param $requestData
     *
     * @throws \Exception
     */
    private function checkSignature($requestData)
    {
        $requestSignature = $requestData['signature'];
        $secret = $this->paymentConfig->getValue('secret');
        $realSignature = $this->signatureGenerator->generateSignature($requestData + ['secret' => $secret]);
        if ($requestSignature !== $realSignature) {
            throw new \Exception(__('Invalid request', 401));
        }
    }

    /**
     * Check if all required fields present
     *
     * @param $requestData
     * @throws \Exception
     */
    private function validateRequest($requestData)
    {
        if (!empty(array_diff(
            [
                'transaction',
                'orderNumber',
                'amount',
                'currency',
                'status',
                'time',
                'signature'
            ],
            array_keys($requestData)
        ))) {
            throw new \Exception('Invalid request parameters', 400);
        }
    }

    /**
     * @param $order
     * @param $requestData
     *
     * @return |null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processOrder($order, $requestData)
    {
        if (!($order && $order->getState() == Order::STATE_NEW)) {
            return null;
        }

        if ($requestData['status'] === self::VIABILL_STATUS_APPROVED) {
            $this->orderManager->performAuthorize(
                $order,
                $requestData['transaction'],
                $requestData['amount']
            );

            $authorizeAndCapture = $this->paymentConfig->getValue('viabill_payment_action');
            if ($authorizeAndCapture == AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {
                $order = $this->orderLocator->get($order->getIncrementId());
                $this->orderManager->performCapture($order);
            }

            $this->orderManager->notify($order);
        } elseif ($requestData['status'] === self::VIABILL_STATUS_CANCELLED) {
            $this->orderManager->cancelOrder($order->getId(), self::CANCEL_MESSAGE);
        }
    }
}
