<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Plugin\Sales\Model\Services;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Model\Service\CreditmemoService as CoreCreditmemoService;

class CreditmemoService
{
    /**
     * Before refund
     *
     * @param CoreCreditmemoService $creditmemoService
     * @param CreditmemoInterface $creditmemo
     * @param bool $offlineRequested
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeRefund(
        CoreCreditmemoService $creditmemoService,
        CreditmemoInterface $creditmemo,
        $offlineRequested = false
    ) {
        $paymentMethod = $creditmemo->getOrder()->getPayment()->getMethod();
        if ($paymentMethod === 'viabill' && $offlineRequested) {
            throw new LocalizedException(__('Viabill payment method does not support Refund Offline'));
        }

        return [$creditmemo, $offlineRequested];
    }
}
