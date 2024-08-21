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
     * @param AdminNotification $adminNotification
     * @param AccountConfiguration $accountConfiguration
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
     * Execute action
     *
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
            $resultJson->setData($response);
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
     * Validate request variable 'command'. Throws exception if not permitted command is passed in request.
     *
     * @param array $requestParams
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
     * Before request
     *
     * @param string $command
     * @param array $requestParams
     * @throws LocalizedException
     */
    private function beforeRequest($command, &$requestParams)
    {
        if ($command === ViabillCommandPool::COMMAND_ACCOUNT_REGISTER) {
            $requestParams['affiliate'] = 'MAGENTO';
            // check if tax id is valid or needs a modification
            $taxId = $this->sanitizeTaxId($requestParams['tax_id'], $requestParams['country']);
            if (empty($taxId)) {
                throw new LocalizedException(__('Invalid Tax ID. Please make sure it is not empty and contains a valid value.'));
            } else {
                $requestParams['tax_id'] = $taxId;
            }
        }
    }

    /**
     * After request
     *
     * @param string $command
     */
    private function afterRequest($command)
    {
        if ($command === ViabillCommandPool::COMMAND_ACCOUNT_LOGIN) {
            $this->adminNotification->registerAdminNotifications();
        }
    }

    /**
     * Sanitize and format the Tax ID (if given)
     * 
     * @param string $taxId
     * @param string $country
     * 
     * @return string
     */
    public function sanitizeTaxId($taxId, $country) {
        $taxId = str_replace(array(' ','-'), '', trim($taxId));
        if ($country == 'ES') {        
         $regex_with_prefix = '/^ES[0-9A-Z]*/';
         if (preg_match($regex_with_prefix, $taxId)) {
           return $taxId;
         }
         $regex_without_prefix = '/^[0-9A-Z]+/';
         if (preg_match($regex_without_prefix, $taxId)) {
           return 'ES'.$taxId;
         }
        } else if ($country == 'DK') {
          $regex_with_prefix = '/^DK[0-9]{8}$/';
          if (preg_match($regex_with_prefix, $taxId)) {
           return $taxId;
          }
          $regex_without_prefix = '/^[0-9]{8}$/';
          if (preg_match($regex_without_prefix, $taxId)) {
           return 'DK'.$taxId;
          }
        }
        return '';
     }
}
