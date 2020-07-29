<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Command;

use Magento\Framework\Phrase;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Payment\Gateway\Command\CommandException;
use Magento\Payment\Gateway\Command\ResultInterface;
use Magento\Payment\Gateway\CommandInterface;
use Magento\Payment\Gateway\Http\ClientException;
use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ConverterException;
use Magento\Payment\Gateway\Http\TransferFactoryInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Gateway\Validator\ValidatorInterface;
use Psr\Log\LoggerInterface;
use Viabillhq\Payment\Gateway\Exception\ViabillExceptionFactory;
use Zend\Http\Response as ZendResponse;

/**
 * Class GatewayCommand
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GatewayCommand implements CommandInterface
{
    /**
     * @var BuilderInterface
     */
    private $requestBuilder;

    /**
     * @var TransferFactoryInterface
     */
    private $transferFactory;

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var HandlerInterface
     */
    private $handler;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SerializerInterface
     */
    private $jsonSerializer;

    /**
     * @var ViabillExceptionFactory
     */
    private $exceptionFactory;

    /**
     * @var string
     */
    private $code;

    /**
     * GatewayCommand constructor.
     *
     * @param BuilderInterface $requestBuilder
     * @param TransferFactoryInterface $transferFactory
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param SerializerInterface $jsonSerializer
     * @param HandlerInterface|null $handler
     * @param ValidatorInterface|null $validator
     * @param ViabillExceptionFactory $exceptionFactory
     */
    public function __construct(
        BuilderInterface $requestBuilder,
        TransferFactoryInterface $transferFactory,
        ClientInterface $client,
        LoggerInterface $logger,
        SerializerInterface $jsonSerializer,
        HandlerInterface $handler = null,
        ValidatorInterface $validator = null,
        ViabillExceptionFactory $exceptionFactory
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->transferFactory = $transferFactory;
        $this->client = $client;
        $this->handler = $handler;
        $this->validator = $validator;
        $this->logger = $logger;
        $this->jsonSerializer = $jsonSerializer;
        $this->exceptionFactory = $exceptionFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $commandSubject
     *
     * @return array|bool|float|int|ResultInterface|null|string
     * @throws ClientException
     * @throws CommandException
     * @throws ConverterException
     * @throws \Viabillhq\Payment\Gateway\Exception\ViabillInternalException
     */
    public function execute(array $commandSubject)
    {
        $transfer = $this->transferFactory->create(
            $this->requestBuilder->build($commandSubject)
        );

        $result = $this->client->placeRequest($transfer);

        /** @var ZendResponse $response */
        $response = $result['response'];

        $this->logger->debug(
            'Request data: ',
            [
                $transfer->getUri(),
                $transfer->getMethod(),
                $transfer->getBody(),
                $response
            ]
        );

        if (!$this->canProceed($response)) {
            throw $this->exceptionFactory->create($response, $this->code);
        }
        $responseBody = $this->getResponseBody($response);

        /** Validating Success response body by specific command validators */
        if ($this->validator !== null) {
            $validationResult = $this->validator->validate(
                array_merge($commandSubject, ['jsonData' => $responseBody])
            );
            if (!$validationResult->isValid()) {
                $this->logExceptions($validationResult->getFailsDescription());
                throw new CommandException(
                    __('Transaction validation failed.')
                );
            }
        }

        /** Handling response after validation is success */
        if ($this->handler) {
            $this->handler->handle($commandSubject, $responseBody);
        }

        return $responseBody;
    }

    /**
     * @param $commandCode
     */
    public function setCode($commandCode)
    {
        $this->code = $commandCode;
    }

    /**
     * @param ZendResponse $response
     *
     * @return bool
     */
    protected function canProceed(ZendResponse $response) : bool
    {
        return $response->isSuccess();
    }

    /**
     * @param Phrase[] $fails
     *
     * @return void
     */
    private function logExceptions(array $fails)
    {
        foreach ($fails as $failPhrase) {
            $this->logger->critical((string) $failPhrase);
        }
    }

    /**
     * @param ZendResponse $response
     *
     * @return array
     */
    private function getResponseBody(ZendResponse $response) : array
    {
        if ($response->getStatusCode() !== ZendResponse::STATUS_CODE_204) {
            return $this->jsonSerializer->unserialize($response->getContent());
        }
        return [];
    }
}
