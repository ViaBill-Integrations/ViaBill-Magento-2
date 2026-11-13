<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model\Adminhtml\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Viabillhq\Payment\Model\LegalNote;

/**
 * Margin of the legal note (top, bottom or left). It is stored in its
 * normalized form (e.g. "-12" becomes "-12px"); empty means the default.
 */
class LegalNoteMargin extends Value
{
    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string) $this->getValue());
        $margin = LegalNote::sanitizeMargin($value);

        if ($value !== '' && $margin === '') {
            throw new LocalizedException(
                __('The margin of the legal note is not valid. Use a value such as 4px, -12px or 0.5em.')
            );
        }

        $this->setValue($margin !== '' ? $margin : LegalNote::getDefaultMargin($this->getPath()));

        return parent::beforeSave();
    }
}
