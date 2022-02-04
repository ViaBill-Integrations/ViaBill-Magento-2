<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Controller\Payment;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\CommandManager;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Gateway\Command\ViabillCommandPool as CommandPool;
use Viabillhq\Payment\Gateway\Exception\ViabillException;
use Viabillhq\Payment\Gateway\Request\SubjectReader;
use Viabillhq\Payment\Model\OrderManagement\OrderManager;

class Authorize extends Action implements CsrfAwareActionInterface
{
    /**
     * @var SessionManagerInterface | Session
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandManager
     */
    private $commandManager;

    /**
     * @var OrderManager
     */
    private $orderManager;

    /**
     * Authorize constructor.
     *
     * @param Context $context
     * @param CommandManager $commandManager
     * @param SessionManagerInterface $checkoutSession
     * @param LoggerInterface $logger
     * @param OrderManager $orderManager
     */
    public function __construct(
        Context $context,
        CommandManager $commandManager,
        SessionManagerInterface $checkoutSession,
        LoggerInterface $logger,
        OrderManager $orderManager
    ) {
        $this->commandManager = $commandManager;
        $this->session = $checkoutSession;
        $this->logger = $logger;
        $this->orderManager = $orderManager;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $quote = $this->session->getQuote();
            $order = $this->orderManager->placeOrder($quote);
            $response = $this->commandManager->executeByCode(
                CommandPool::COMMAND_AUTHORIZE,
                $quote->getPayment(),
                [SubjectReader::ORDER => $order]
            );
            $resultJson->setData($response);
        } catch (ViabillException $e) {
            $resultJson->setHttpResponseCode($e->getCode());
            $resultJson->setData(['message' => $e->getMessage()]);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $resultJson->setHttpResponseCode($e->getCode());
            $resultJson->setData(['message' => $e->getMessage()]);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $resultJson->setData([
                'message' => __('An error occurred during request to Viabill. Please try again later.')
            ]);
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        }
        return $resultJson;
    }

    /**
     * @inheritdoc
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
