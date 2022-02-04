<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Gateway\Request;

use Magento\Framework\ObjectManager\TMap;
use Magento\Framework\ObjectManager\TMapFactory;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Viabillhq\Payment\Model\Request\SignatureGenerator;

class RequestBuilderComposite implements BuilderInterface
{
    /**
     * @var BuilderInterface[] | TMap
     */
    private $builders;

    /**
     * @var array
     */
    private $unsetFields;

    /**
     * @var SignatureGenerator
     */
    private $signatureGenerator;

    /**
     * @var string
     */
    private $signatureFieldName;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * RequestBuilderComposite constructor.
     *
     * @param TMapFactory $tmapFactory
     * @param SignatureGenerator $signatureGenerator
     * @param SubjectReader $subjectReader
     * @param array $builders
     * @param array $unsetFields
     * @param string $signatureFieldName
     */
    public function __construct(
        TMapFactory $tmapFactory,
        SignatureGenerator $signatureGenerator,
        SubjectReader $subjectReader,
        array $builders = [],
        array $unsetFields = ['secret'],
        string $signatureFieldName = 'signature'
    ) {
        $this->builders = $tmapFactory->create(
            [
                'array' => $builders,
                'type' => BuilderInterface::class
            ]
        );
        $this->unsetFields = $unsetFields;
        $this->subjectReader = $subjectReader;
        $this->signatureGenerator = $signatureGenerator;
        $this->signatureFieldName = $signatureFieldName;
    }

    /**
     * Build request
     *
     * @param array $buildSubject
     *
     * @return array
     */
    public function build(array $buildSubject) : array
    {
        $result = [];
        foreach ($this->builders as $builder) {
            $result = $this->merge($result, $builder->build($buildSubject));
        }
        $result = $this->sign($result);
        return $this->cleanUp($result);
    }

    /**
     * Merge function for builders
     *
     * @param array $result
     * @param array $builder
     * @return array
     */
    private function merge(array $result, array $builder)
    {
        return array_replace_recursive($result, $builder);
    }

    /**
     * Sign data
     *
     * @param array $data
     *
     * @return mixed
     */
    private function sign($data)
    {
        if ($this->signatureGenerator->isSignatureNeeded()) {
            $data[$this->signatureFieldName] = $this->signatureGenerator->generateSignature($data);
        }
        return $data;
    }

    /**
     * Clean up data
     *
     * @param array $data
     *
     * @return mixed
     */
    private function cleanUp($data)
    {
        $unsetFields = array_merge($this->unsetFields, $this->subjectReader->getSubjectFields());
        foreach ($unsetFields as $field) {
            if (array_key_exists($field, $data)) {
                unset($data[$field]);
            }
        }
        return $data;
    }
}
