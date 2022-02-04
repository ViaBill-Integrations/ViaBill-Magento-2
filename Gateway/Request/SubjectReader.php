<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;

class SubjectReader
{
    public const AMOUNT = 'amount';
    public const ORDER = 'subject_order';
    public const PAYMENT = 'payment';
    public const REQUEST = 'subject_request_data';

    /**
     * Get Subject Fields
     *
     * @return array
     */
    public function getSubjectFields() : array
    {
        return [
            self::ORDER,
            self::PAYMENT,
            self::REQUEST
        ];
    }

    /**
     * Read order
     *
     * @param array $subject
     *
     * @return null
     */
    public function readOrder($subject)
    {
        return $subject[self::ORDER] ?? null;
    }

    /**
     * Read amount
     *
     * @param array $subject
     *
     * @return null
     */
    public function readAmount($subject)
    {
        return $subject[self::AMOUNT] ?? null;
    }

    /**
     * Read payment
     *
     * @param array $subject
     *
     * @return PaymentDataObjectInterface|null
     */
    public function readPayment($subject)
    {
        if (isset($subject[self::PAYMENT])
            && $subject[self::PAYMENT] instanceof PaymentDataObjectInterface
        ) {
            return $subject[self::PAYMENT];
        }
        return null;
    }

    /**
     * Read request data from subject
     *
     * @param array $subject
     *
     * @return array|null
     */
    public function readRequestData($subject)
    {
        return $subject[self::REQUEST] ?? null;
    }
}
