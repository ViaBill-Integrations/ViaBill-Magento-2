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
 * Font size of the legal note. It is stored in its normalized form (e.g. "14"
 * becomes "14px"); empty means the font size of the theme.
 */
class LegalNoteFontSize extends Value
{
    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string) $this->getValue());
        $fontSize = LegalNote::sanitizeFontSize($value);

        if ($value !== '' && $fontSize === '') {
            throw new LocalizedException(
                __('The font size of the legal note is not valid. Use a value such as 14px, 0.9em or 90%.')
            );
        }

        $this->setValue($fontSize);

        return parent::beforeSave();
    }
}
