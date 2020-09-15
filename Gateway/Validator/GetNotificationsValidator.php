<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class GetNotificationsValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     *
     * @param array $validationSubject
     *
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!empty($validationSubject['jsonData'])
            && isset($validationSubject['jsonData']['messages'])
        ) {
            return $this->createResult(true);
        }
        return $this->createResult(false);
    }
}