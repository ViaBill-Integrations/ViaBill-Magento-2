<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Controller\Adminhtml\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Exception;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\CommandPoolInterface;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Gateway\Command\ViabillCommandPool;
use Viabillhq\Payment\Gateway\Exception\ViabillException;
use Viabillhq\Payment\Gateway\Request\SubjectReader;
use Viabillhq\Payment\Model\Adminhtml\AccountConfiguration;
use Viabillhq\Payment\Model\Adminhtml\AdminNotification;

class GetCredentials extends Action
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
     * @var AdminNotification
     */
    private $adminNotification;

    /**
     * @var AccountConfiguration
     */
    private $accountConfiguration;

    /**
     * GetCredentials constructor.
     *
     * @param Context $context
     * @param CommandPoolInterface $commandPool
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CommandPoolInterface $commandPool,
        LoggerInterface $logger,
        AdminNotification $adminNotification,
        AccountConfiguration $accountConfiguration
    ) {
        $this->commandPool = $commandPool;
        $this->logger = $logger;
        $this->adminNotification = $adminNotification;
        $this->accountConfiguration = $accountConfiguration;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $requestParams = $this->getRequest()->getParams();
        try {
            $this->validateCommand($requestParams);
            $command = $requestParams['command'];
            $this->beforeRequest($command, $requestParams);
            $response = $this->commandPool->get($command)->execute([SubjectReader::REQUEST => $requestParams]);
            $this->afterRequest($command, $requestParams, $response);
            $this->messageManager->addSuccessMessage(
                __('You logged into your Viabill account.')
            );
        } catch (ViabillException $e) {
            $resultJson->setData(['errorMessage' => $e->getMessage()]);
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultJson->setData(['errorMessage' => $e->getMessage()]);
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $resultJson->setData([
                'errorMessage' => __('An error occurred during request to Viabill. Please try again later.')
            ]);
            $resultJson->setHttpResponseCode(Exception::HTTP_BAD_REQUEST);
        }
        return $resultJson;
    }

    /**
     * Validate request variable 'command'.
     * Throws exception if not permitted command is passed in request.
     *
     * @param $requestParams
     * @throws CommandException
     */
    private function validateCommand($requestParams)
    {
        $allowedCommands = [ViabillCommandPool::COMMAND_ACCOUNT_REGISTER, ViabillCommandPool::COMMAND_ACCOUNT_LOGIN];
        if (!array_key_exists('command', $requestParams)
            || !in_array($requestParams['command'], $allowedCommands)
        ) {
            throw new CommandException(__('Invalid command'));
        }
    }

    /**
     * @param string $command
     * @param array $requestParams
     */
    private function beforeRequest($command, &$requestParams)
    {
        if ($command === ViabillCommandPool::COMMAND_ACCOUNT_REGISTER) {
            $requestParams['affiliate'] = 'MAGENTO';
        }
    }

    /**
     * @param string $command
     */
    private function afterRequest($command)
    {
        if ($command === ViabillCommandPool::COMMAND_ACCOUNT_LOGIN) {
            $this->adminNotification->registerAdminNotifications();
        }
    }
}
