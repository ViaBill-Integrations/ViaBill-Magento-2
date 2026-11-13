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
 * Text color of the legal note; empty means the text color of the theme.
 */
class LegalNoteColor extends Value
{
    /**
     * @return $this
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = trim((string) $this->getValue());
        $color = LegalNote::sanitizeColor($value);

        if ($value !== '' && $color === '') {
            throw new LocalizedException(
                __('The text color of the legal note is not valid. Use a color code such as #cc0000.')
            );
        }

        $this->setValue($color);

        return parent::beforeSave();
    }
}
