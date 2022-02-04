<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Request;

class SignatureGenerator
{
    /**
     * @var string
     */
    private $signaturePattern;

    /**
     * SignatureGenerator constructor.
     *
     * @param string $signaturePattern
     */
    public function __construct(
        string $signaturePattern = ''
    ) {
        $this->signaturePattern = $signaturePattern;
    }

    /**
     * Check if signature is needed
     *
     * @return bool
     */
    public function isSignatureNeeded() : bool
    {
        return !empty($this->signaturePattern);
    }

    /**
     * Generate signature
     *
     * @param array $fields
     *
     * @return string
     */
    public function generateSignature(array $fields) : string
    {
        $signatureParts = [];
        $patternParts = explode('#', $this->signaturePattern);
        foreach ($patternParts as $part) {
            if (isset($fields[$part])) {
                $signatureParts[] = $fields[$part];
            }
        }
        $signature = implode('#', $signatureParts);
        return hash('sha256', $signature);
    }
}
