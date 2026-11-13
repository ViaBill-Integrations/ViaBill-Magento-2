<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Block\Adminhtml\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Viabillhq\Payment\Model\LegalNote;

/**
 * Read-only field that shows the legal note in the language selected in the
 * "Language" field, and follows that field when it is changed. It has no name,
 * so nothing is saved for it.
 */
class LegalNoteText extends Field
{
    /**
     * @var LegalNote
     */
    private $legalNote;

    /**
     * @param Context $context
     * @param LegalNote $legalNote
     * @param array $data
     */
    public function __construct(Context $context, LegalNote $legalNote, array $data = [])
    {
        $this->legalNote = $legalNote;
        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $texts = $this->legalNote->getDefaultTexts();
        $id = $element->getHtmlId();

        $html = sprintf(
            '<textarea id="%s" class="textarea admin__control-textarea" rows="2" readonly="readonly"'
            . ' style="background-color: #f6f7f7;" data-texts="%s">%s</textarea>',
            $this->escapeHtmlAttr($id),
            $this->escapeHtmlAttr(json_encode($texts)),
            $this->escapeHtml($texts[LegalNote::LANGUAGE_AUTO])
        );

        // Show the text of the selected language, now and when it is changed.
        $html .= '<script>(function () {'
            . 'var text = document.getElementById(' . json_encode($id) . ');'
            . 'var texts = JSON.parse(text.getAttribute("data-texts") || "{}");'
            . 'var selector = \'select[name$="[legal_note][fields][language][value]"]\';'
            . 'function update(select) {'
            . ' if (select && Object.prototype.hasOwnProperty.call(texts, select.value)) { text.value = texts[select.value]; }'
            . '}'
            . 'document.addEventListener("change", function (event) {'
            . ' if (event.target && event.target.matches && event.target.matches(selector)) { update(event.target); }'
            . '});'
            . 'function init() { update(document.querySelector(selector)); }'
            . 'if (document.readyState === "loading") { document.addEventListener("DOMContentLoaded", init); } else { init(); }'
            . '})();</script>';

        return $html;
    }

    /**
     * Nothing is stored for this field, so it has no "Use Default" checkbox.
     *
     * @param AbstractElement $element
     * @return bool
     */
    protected function _isInheritCheckboxRequired(AbstractElement $element)
    {
        return false;
    }
}
