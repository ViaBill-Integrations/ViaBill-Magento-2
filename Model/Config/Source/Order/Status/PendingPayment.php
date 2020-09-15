<?php

namespace Viabillhq\Payment\Model\Config\Source\Order\Status;

class PendingPayment extends \Magento\Sales\Model\Config\Source\Order\Status
{
    /**
     * @var string
     */
    protected $_stateStatuses = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT;
}