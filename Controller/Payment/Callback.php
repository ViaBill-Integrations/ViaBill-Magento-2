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
     * Callback fields covered by the ViaBill signature, in signature order.
     */
    public const SIGNED_FIELDS = ['transaction', 'orderNumber', 'amount', 'currency', 'status', 'time'];

    /**
     * Payment methods of this module.
     */
    public const VIABILL_PAYMENT_METHODS = ['viabill', 'viabill_try'];

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
            $this->validateOrder($order, $requestData);
            $this->processOrder($order, $requestData);
            $result->setHttpResponseCode(Response::STATUS_CODE_204);
        } catch (\Exception $e) { // @codingStandardsIgnoreLine
            $this->logger->critical($e->getMessage());
            // Only use the exception code when it is an HTTP error status.
            $code = (int) $e->getCode();
            if ($code < 400 || $code > 599) {
                $code = Response::STATUS_CODE_400;
            }
            $result->setHttpResponseCode($code);
            $result->setData([
                'status' => $code,
                'message' => __('An error occurred during callback processing.')
            ]);
        }
        return $result;
    }

    /**
     * Check Signature
     *
     * Only the signed callback fields are used, together with the configured
     * secret, so a request can never supply its own secret.
     *
     * @param array $requestData
     *
     * @throws \Exception
     */
    private function checkSignature($requestData)
    {
        $secret = (string) $this->paymentConfig->getValue('secret');
        if ($secret === '') {
            $this->debugLog(
                'Payment Callback: no API secret configured - signature can not be verified.',
                DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC
            );
            throw new \Exception('Invalid request', 401); // @codingStandardsIgnoreLine
        }

        $signedFields = [];
        foreach (self::SIGNED_FIELDS as $field) {
            $signedFields[$field] = (string) $requestData[$field];
        }
        $signedFields['secret'] = $secret;

        $realSignature = $this->signatureGenerator->generateSignature($signedFields);
        $requestSignature = strtolower(trim((string) $requestData['signature']));
        if (!hash_equals($realSignature, $requestSignature)) {
            $this->debugLog(
                'Payment Callback: invalid signature for order ' . $signedFields['orderNumber'],
                DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC
            );
            throw new \Exception('Invalid request', 401); // @codingStandardsIgnoreLine
        }
    }

    /**
     * Check if all required fields present
     *
     * @param mixed $requestData
     * @throws \InvalidArgumentException
     */
    private function validateRequest($requestData)
    {
        if (!is_array($requestData)) {
            $this->debugLog('Payment Callback: Request is not a JSON object '.
                '- Request Validation failed.', DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
            throw new \InvalidArgumentException('Invalid request parameters', 400);
        }
        foreach (array_merge(self::SIGNED_FIELDS, ['signature']) as $field) {
            if (!isset($requestData[$field])
                || !is_scalar($requestData[$field])
                || (string) $requestData[$field] === ''
            ) {
                $this->debugLog('Payment Callback: Request Fields are missing '.
                    '- Request Validation failed.', DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
                throw new \InvalidArgumentException('Invalid request parameters', 400);
            }
        }
    }

    /**
     * Check that the callback belongs to a ViaBill order and matches its amount and currency
     *
     * @param \Magento\Sales\Api\Data\OrderInterface|null $order
     * @param array $requestData
     * @throws \InvalidArgumentException
     */
    private function validateOrder($order, $requestData)
    {
        if (!$order) {
            $this->debugLog('Payment Callback: unknown order '.$requestData['orderNumber'],
                DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
            throw new \InvalidArgumentException('Unknown order', 404);
        }

        $payment = $order->getPayment();
        if (!$payment || !in_array($payment->getMethod(), self::VIABILL_PAYMENT_METHODS, true)) {
            $this->debugLog('Payment Callback: order '.$requestData['orderNumber'].
                ' is not paid with ViaBill', DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
            throw new \InvalidArgumentException('Invalid order', 400);
        }

        $amountMatches = is_numeric($requestData['amount'])
            && abs((float) $requestData['amount'] - (float) $order->getGrandTotal()) < 0.005;
        $currencyMatches = strtoupper((string) $requestData['currency'])
            === strtoupper((string) $order->getOrderCurrencyCode());
        if (!$amountMatches || !$currencyMatches) {
            $this->debugLog('Payment Callback: amount or currency does not match order '.
                $requestData['orderNumber'], DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
            throw new \InvalidArgumentException('Amount or currency mismatch', 400);
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
