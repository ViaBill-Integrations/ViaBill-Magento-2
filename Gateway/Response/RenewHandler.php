<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Response;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Viabillhq\Payment\Gateway\Request\SubjectReader;

class RenewHandler implements HandlerInterface
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * RenewHandler constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param SubjectReader $subjectReader
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SubjectReader $subjectReader,
        TimezoneInterface $timezone
    ) {
        $this->subjectReader = $subjectReader;
        $this->orderRepository = $orderRepository;
        $this->timezone = $timezone;
    }

    /**
     * Handle renewal
     *
     * @param array $handlingSubject
     * @param array $responseBody
     */
    public function handle(array $handlingSubject, array $responseBody)
    {
        $order = $this->subjectReader->readOrder($handlingSubject);
        $expireTime = $this->timezone->date()->modify('+14 days')->format('Y-m-d H:i:s');
        $order->setPaymentAuthExpiration(strtotime($expireTime));
        $this->orderRepository->save($order);
    }
}
