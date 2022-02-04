<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;

class ViabillRequestDataBuilder implements BuilderInterface
{
    /**
     * @var array
     */
    private $requestFields = [];

    /**
     * ViabillRequestDataBuilder constructor.
     *
     * @param array $requestFields
     */
    public function __construct(
        array $requestFields
    ) {
        $this->requestFields = $requestFields;
    }

    /**
     * Build the request
     *
     * @param array $buildSubject
     *
     * @return array
     * @throws \Exception
     */
    public function build(array $buildSubject)
    {
        $transactionData = [];
        foreach ($this->requestFields as $apiField => $field) {
            $transactionData[$apiField] = $this->getFieldData($field, $buildSubject);
        }
        return array_merge($buildSubject, $transactionData);
    }

    /**
     * Get the field data
     *
     * @param string $field
     * @param array $buildSubject
     *
     * @return mixed
     * @throws \Exception
     */
    private function getFieldData($field, array $buildSubject = [])
    {
        $method = 'get' . ucfirst($field);
        if (method_exists($this, $method)) {
            return $this->{$method}($buildSubject);
        }
        throw new \Exception( // @codingStandardsIgnoreLine
            sprintf('Method "%s" does not exist', $method)
        );
    }
}
