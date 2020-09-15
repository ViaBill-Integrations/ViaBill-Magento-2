<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Gateway\Command\ViabillCommandPool;
use Viabillhq\Payment\Gateway\Exception\ViabillException;
use Viabillhq\Payment\Gateway\Request\SubjectReader;
use Zend\Http\Response;

/**
 * Class Renew
 * @package Viabillhq\Payment\Controller\Adminhtml\Order
 */
class Renew extends Action
{
    /**
     * @var CommandPoolInterface
     */
    private $commandPool;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var LoggerInterface
     */
    private $orderRepository;

    /**
     * Renew constructor.
     *
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param OrderRepositoryInterface $orderRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        OrderRepositoryInterface $orderRepository,
        LoggerInterface $logger
    ) {
        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $requestParams = $this->getRequest()->getParams();
        try {
            $order = $this->orderRepository->get($requestParams['order_id']);
            /** @var Response $response */
            $this->commandPool->get(ViabillCommandPool::COMMAND_RENEW)
                ->execute([SubjectReader::ORDER => $order]);
            $order->addStatusHistoryComment(__('Renewed order on Viabill.'));
            $this->orderRepository->save($order);
            $this->messageManager->addSuccessMessage(__('The order has been successfully renewed.'));
        } catch (ViabillException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(
                __('An error occurred during order renewal. Please try again later.')
            );
        } finally {
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        }
        return $resultRedirect;
    }
}
