<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class AuthorizeValidator extends AbstractValidator
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
        $isValid = $validationSubject['jsonData']['url'] ?? false;
        $errorMessages = $isValid ? [] : [__('Gateway response error. Incorrect authorize payment parameters.')];

        return $this->createResult($isValid, $errorMessages);
    }
}
