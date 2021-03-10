<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

class GetCredentialsValidator extends AbstractValidator
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
        if (isset($validationSubject['jsonData'])
            && array_key_exists('key', $validationSubject['jsonData'])
            && array_key_exists('secret', $validationSubject['jsonData'])
            && array_key_exists('pricetagScript', $validationSubject['jsonData'])
        ) {
            return $this->createResult(true);
        }
        return $this->createResult(false);
    }
}
