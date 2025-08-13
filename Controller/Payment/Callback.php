<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Model\Adminhtml\Source\DebugLevels;
use Viabillhq\Payment\Model\OrderManagement\OrderLocator;
use Viabillhq\Payment\Model\OrderManagement\OrderManager;
use Viabillhq\Payment\Model\Request\SignatureGenerator;
use Laminas\http\Response;

class Callback extends Action implements CsrfAwareActionInterface
{
    public const VIABILL_STATUS_APPROVED = 'APPROVED';

    public const VIABILL_STATUS_CANCELLED = 'CANCELLED';

    public const CANCEL_MESSAGE = 'Payment cancelled from Viabill.';

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
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $request = $this->getRequest()->getContent();
            $this->debugLog('Payment Callback: Raw Request is '. $request, DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
            $requestData = $this->jsonSerializer->unserialize($request);
            $this->validateRequest($requestData);
            $this->checkSignature($requestData);

            $orderId = $requestData['orderNumber'];
            $order = $this->orderLocator->get($orderId);
            $this->processOrder($order, $requestData);
            $result->setHttpResponseCode(Response::STATUS_CODE_204);
        } catch (\Exception $e) { // @codingStandardsIgnoreLine
            $this->logger->critical($e->getMessage());
            $result->setHttpResponseCode($e->getCode());
            $result->setData([
                'status' => $e->getCode(),
                'message' => __('An error occurred during callback processing.')
            ]);
        }
        return $result;
    }

    /**
     * Check Signature
     *
     * @param array $requestData
     *
     * @throws \Exception
     */
    private function checkSignature($requestData)
    {
        $requestSignature = $requestData['signature'];
        $secret = $this->paymentConfig->getValue('secret');
        $realSignature = $this->signatureGenerator->generateSignature($requestData + ['secret' => $secret]);
        if ($requestSignature !== $realSignature) {
            $this->debugLog(
                'Payment Callback: Request Signature '.$requestSignature.
                ' is not equal to Real signature '.$realSignature,
                DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC
            );
            throw new \Exception(__('Invalid request', 401)); // @codingStandardsIgnoreLine
        }
    }

    /**
     * Check if all required fields present
     *
     * @param array $requestData
     * @throws \InvalidArgumentException
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
            $this->debugLog('Payment Callback: Request Fields are missing '.
                '- Request Validation failed.', DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
            throw new \InvalidArgumentException('Invalid request parameters');
        }
    }

    /**
     * Process Order
     *
     * @param OrderInterface $order
     * @param array $requestData
     *
     * @return |null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processOrder($order, $requestData)
    {
        if (!($order && $order->getState() == Order::STATE_NEW)) {
            $this->debugLog(
                'Payment Callback: Order not new - returning null',
                DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC
            );
            return null;
        }

        if (isset($requestData['status'])) {
            $this->debugLog(
                'Payment Callback: Status is '.$requestData['status'],
                DebugLevels::DEBUG_LEVEL_PRIORITY_DEVELOPER
            );
        }

        $order_id = $order->getId();

        if ($requestData['status'] === self::VIABILL_STATUS_APPROVED) {
            $this->debugLog(
                'Payment Callback Action: Entering Approved Status for order '.$order_id,
                DebugLevels::DEBUG_LEVEL_PRIORITY_DEVELOPER
            );

            $this->orderManager->performAuthorize(
                $order,
                $requestData['transaction'],
                $requestData['amount']
            );

            $authorizeAndCapture = $this->paymentConfig->getValue('viabill_payment_action');
            if ($authorizeAndCapture == AbstractMethod::ACTION_AUTHORIZE_CAPTURE) {
                $this->debugLog(
                    'Payment Callback Action: Entering Authorize Capture for order '.$order_id,
                    DebugLevels::DEBUG_LEVEL_PRIORITY_DEVELOPER
                );

                $order = $this->orderLocator->get($order->getIncrementId());
                $this->orderManager->performCapture($order);
            }

            $this->debugLog(
                'Payment Callback Action: Notifying Order Manager',
                DebugLevels::DEBUG_LEVEL_PRIORITY_DEVELOPER
            );
            $this->orderManager->notify($order);
        } elseif ($requestData['status'] === self::VIABILL_STATUS_CANCELLED) {
            $this->debugLog(
                'Payment Callback Action: Entering Cancel Status for order '.$order_id,
                DebugLevels::DEBUG_LEVEL_PRIORITY_DEVELOPER
            );
            $this->orderManager->cancelOrder($order->getId(), self::CANCEL_MESSAGE);
        }
    }

    /**
     * Log debug info
     *
     * @param string $msg
     * @param int $debug_level
     */
    private function debugLog($msg, $debug_level = 1)
    {
        $this->logger->debug($msg, ['debug_level' => $debug_level]);
    }

    /**
     * Create CSRF validation exception
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @inheritdoc
     *
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function validateForCsrf(RequestInterface $request): ?bool //@codingStandardsIgnoreLine
    {
        return true;
    }
}
