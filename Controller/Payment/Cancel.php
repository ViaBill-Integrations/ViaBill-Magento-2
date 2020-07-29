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
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Model\OrderManagement\OrderManager;
use Zend\Http\Response;

/**
 * Class Callback
 * @package Viabillhq\Payment\Controller\Payment
 */
class Cancel extends Action implements CsrfAwareActionInterface
{
    const CANCEL_MESSAGE = 'Payment cancelled from Viabill.';

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
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $this->messageManager->addErrorMessage(__('Payment cancelled from Viabill.'));

        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $orderId = $this->checkoutSession->getLastRealOrder()->getId();
            $this->orderManager->cancelOrder($orderId, self::CANCEL_MESSAGE);
            $this->checkoutSession->restoreQuote();
            $result->setPath('checkout/cart');
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $result->setHttpResponseCode(Response::STATUS_CODE_500);
        } finally {
            $this->logger->debug('Cancel Request Content: ' . $this->getRequest()->getContent());
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @param RequestInterface $request
     *
     * @return null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException //@codingStandardsIgnoreLine
    {
        return null;
    }

    /**
     * {@inheritdoc}
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
