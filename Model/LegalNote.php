<?php
/**
 * Copyright © ViaBill. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Viabillhq\Payment\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Framework\File\Csv;
use Magento\Framework\Module\Dir;
use Magento\Framework\Module\Dir\Reader;
use Magento\Store\Model\ScopeInterface;

/**
 * Legal note (credit warning) shown next to the ViaBill payment methods at the
 * checkout.
 *
 * It is configured in the "Legal Note" group of the ViaBill settings: it can be
 * switched off, and the merchant can replace the default text with their own
 * text per store view. The default text is translated in the module's i18n
 * files and is shown either in the store view's language ("auto") or in a
 * fixed language.
 */
class LegalNote
{
    const CONFIG_PATH_ENABLED = 'payment/viabill/legal_note_enabled';
    const CONFIG_PATH_TEXT = 'payment/viabill/legal_note_text';
    const CONFIG_PATH_LANGUAGE = 'payment/viabill/legal_note_language';

    const LANGUAGE_AUTO = 'auto';

    /**
     * Default legal note. It must be identical to the text in getDefaultText(),
     * which is the one the translation tools pick up.
     */
    const DEFAULT_TEXT = 'Warning! Borrow responsibly - it costs money.';

    /**
     * HTML tags the legal note may contain, e.g. a link to the terms.
     */
    const ALLOWED_TAGS = ['a', 'b', 'strong', 'em', 'i', 'br', 'p', 'span'];

    /**
     * Locales of the module's i18n files for the fixed languages.
     *
     * @var array
     */
    private static $locales = [
        'da' => 'da_DK',
        'es' => 'es_ES',
    ];

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Reader
     */
    private $moduleReader;

    /**
     * @var Csv
     */
    private $csv;

    /**
     * Translations read from the module's i18n files, keyed by locale.
     *
     * @var array
     */
    private $translations = [];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Escaper $escaper
     * @param Reader $moduleReader
     * @param Csv $csv
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Escaper $escaper,
        Reader $moduleReader,
        Csv $csv
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->escaper = $escaper;
        $this->moduleReader = $moduleReader;
        $this->csv = $csv;
    }

    /**
     * Return the legal note to show at the checkout: the merchant's own text
     * if set, otherwise the default text. Returns empty string if switched off.
     *
     * @param int|null $storeId
     * @return string Escaped HTML.
     */
    public function getText($storeId = null)
    {
        if (!$this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLED, ScopeInterface::SCOPE_STORE, $storeId)) {
            return '';
        }

        $text = trim((string) $this->scopeConfig->getValue(self::CONFIG_PATH_TEXT, ScopeInterface::SCOPE_STORE, $storeId));

        if ($text === '') {
            $language = (string) $this->scopeConfig->getValue(
                self::CONFIG_PATH_LANGUAGE,
                ScopeInterface::SCOPE_STORE,
                $storeId
            );
            $text = $this->getDefaultText($language);
        }

        return (string) $this->escaper->escapeHtml($text, self::ALLOWED_TAGS);
    }

    /**
     * Return the default legal note in the given language.
     *
     * @param string $language 'auto' (the store view's language), 'en', 'da' or 'es'
     * @return string
     */
    public function getDefaultText($language = self::LANGUAGE_AUTO)
    {
        if ($language === 'en') {
            return self::DEFAULT_TEXT;
        }

        if (isset(self::$locales[$language])) {
            return $this->translateFromFile(self::$locales[$language], self::DEFAULT_TEXT);
        }

        return (string) __('Warning! Borrow responsibly - it costs money.');
    }

    /**
     * Return the translation of a text from the module's i18n file of the given
     * locale, or the text itself if it is not translated there. The file is
     * read directly, so the locale does not have to be used by any store view.
     *
     * @param string $locale
     * @param string $text
     * @return string
     */
    private function translateFromFile($locale, $text)
    {
        if (!isset($this->translations[$locale])) {
            $this->translations[$locale] = [];
            $file = $this->moduleReader->getModuleDir(Dir::MODULE_I18N_DIR, 'Viabillhq_Payment')
                . '/' . $locale . '.csv';

            if (is_file($file)) {
                try {
                    $this->translations[$locale] = $this->csv->getDataPairs($file);
                } catch (\Exception $e) {
                    $this->translations[$locale] = [];
                }
            }
        }

        return isset($this->translations[$locale][$text]) ? (string) $this->translations[$locale][$text] : $text;
    }
}
