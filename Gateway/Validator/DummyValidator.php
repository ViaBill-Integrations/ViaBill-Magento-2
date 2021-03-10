<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;

/**
 * Class DummyValidator
 *
 * Validator for requests which don't expect any data in response
 */
class DummyValidator extends AbstractValidator
{
    /**
     * @inheritdoc
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        return $this->createResult(true);
    }
}
