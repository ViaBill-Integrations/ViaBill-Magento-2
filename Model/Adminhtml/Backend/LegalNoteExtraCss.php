<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Backend;

use Magento\Framework\App\Config\Value;
use Viabillhq\Payment\Model\LegalNote;

/**
 * Extra CSS of the legal note. Only CSS declarations are kept, see
 * LegalNote::sanitizeExtraCss().
 */
class LegalNoteExtraCss extends Value
{
    /**
     * @return $this
     */
    public function beforeSave()
    {
        $this->setValue(LegalNote::sanitizeExtraCss((string) $this->getValue()));

        return parent::beforeSave();
    }
}
