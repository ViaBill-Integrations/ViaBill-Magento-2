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
use Magento\Framework\Session\SessionManagerInterface;
use Viabillhq\Payment\Model\Adminhtml\Source\DebugLevels;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Model\OrderManagement\OrderManager;
use Laminas\http\Response;

class Cancel extends Action implements CsrfAwareActionInterface
{
    public const CANCEL_MESSAGE = 'Payment cancelled from Viabill.';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * @var SessionManagerInterface
     */
    private $checkoutSession;

    /**
     * Cancel constructor.
     *
     * @param Context $context
     * @param OrderManager $orderManager
     * @param LoggerInterface $logger
     * @param SessionManagerInterface $checkoutSession
     */
    public function __construct(
        Context $context,
        OrderManager $orderManager,
        LoggerInterface $logger,
        SessionManagerInterface $checkoutSession
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->orderManager = $orderManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $cancel_msg_literal = self::CANCEL_MESSAGE;
        $this->messageManager->addErrorMessage(__($cancel_msg_literal));

        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $this->debugLog(
                'Payment Cancel Action for order '.$orderId,
                DebugLevels::DEBUG_LEVEL_PRIORITY_DEVELOPER
            );
            $this->orderManager->cancelOrder($orderId, $cancel_msg_literal);
            $this->checkoutSession->restoreQuote();
            $result->setPath('checkout/cart');
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result->setHttpResponseCode(Response::STATUS_CODE_500);
        } finally {
            $this->debugLog('Cancel Request Content: ' .
                $this->getRequest()->getContent(), DebugLevels::DEBUG_LEVEL_PRIORITY_BASIC);
        }
        return $result;
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
     * @inheritdoc
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
