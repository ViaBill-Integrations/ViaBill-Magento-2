<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request\GetCredentials;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Viabillhq\Payment\Gateway\Request\SubjectReader;

class RequestDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var array
     */
    private $requestFields = [];

    /**
     * GetCredentialsDataBuilder constructor.
     *
     * @param SubjectReader $subjectReader
     * @param array $requestFields
     */
    public function __construct(
        SubjectReader $subjectReader,
        array $requestFields
    ) {
        $this->subjectReader = $subjectReader;
        $this->requestFields = $requestFields;
    }

    /**
     * Build request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject)
    {
        $requestData = $this->subjectReader->readRequestData($buildSubject);
        return $this->mapFields($this->requestFields, $requestData);
    }

    /**
     * Map fields
     *
     * @param array $fields
     * @param array $dataSource
     *
     * @return array
     */
    private function mapFields($fields, $dataSource)
    {
        $result = [];
        foreach ($fields as $name => $dataSourceKey) {
            if (is_array($dataSourceKey)) {
                $result[$name] = array_values($this->mapFields($dataSourceKey, $dataSource));
            } else {
                $result[$name] = $dataSource[$dataSourceKey] ?? null;
            }
        }
        return $result;
    }
}
