<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Plugin\Sales\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Order\Invoice as CoreInvoice;

class Invoice
{
    /**
     * @param CoreInvoice $subject
     *
     * @throws LocalizedException
     */
    public function beforeRegister(CoreInvoice $subject)
    {
        $captureCase = $subject->getRequestedCaptureCase();
        $paymentMethod = $subject->getOrder()->getPayment()->getMethod();

        if ($captureCase === CoreInvoice::CAPTURE_OFFLINE && $paymentMethod === 'viabill') {
            throw new LocalizedException(__('Viabill payment method does not support Capture Offline'));
        }
    }
}
