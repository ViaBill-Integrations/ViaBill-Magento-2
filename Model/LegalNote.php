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
 * It is always shown. The text is the default legal note, translated in the
 * module's i18n files, in the store view's language ("auto") or in a fixed
 * language. The merchant can only change how it looks, in the "Legal Note"
 * group of the ViaBill settings.
 */
class LegalNote
{
    const CONFIG_PATH_LANGUAGE = 'payment/viabill/legal_note_language';

    // Styling of the legal note, see getStyle()
    const CONFIG_PATH_FONT_SIZE = 'payment/viabill/legal_note_font_size';
    const CONFIG_PATH_FONT_STYLE = 'payment/viabill/legal_note_font_style';
    const CONFIG_PATH_COLOR = 'payment/viabill/legal_note_color';
    const CONFIG_PATH_ALIGN = 'payment/viabill/legal_note_align';
    const CONFIG_PATH_EXTRA_CSS = 'payment/viabill/legal_note_extra_css';
    const CONFIG_PATH_MARGIN_TOP = 'payment/viabill/legal_note_margin_top';
    const CONFIG_PATH_MARGIN_BOTTOM = 'payment/viabill/legal_note_margin_bottom';
    const CONFIG_PATH_MARGIN_LEFT = 'payment/viabill/legal_note_margin_left';

    const LANGUAGE_AUTO = 'auto';

    /**
     * Default font style: the same as the payment method name.
     */
    const DEFAULT_FONT_STYLE = 'normal';

    /**
     * Default margins of the legal note. It is shown right below the logo, so
     * 4px above it gives a small gap in any theme; negative margins would depend
     * on the theme, so none is used.
     */
    const DEFAULT_MARGINS = [
        self::CONFIG_PATH_MARGIN_TOP => '4px',
        self::CONFIG_PATH_MARGIN_BOTTOM => '0px',
        self::CONFIG_PATH_MARGIN_LEFT => '0px',
    ];

    const FONT_STYLES = ['normal', 'bold', 'italic', 'bold-italic'];

    const ALIGNMENTS = ['left', 'center', 'right'];

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
     * Return the legal note to show at the checkout: the default text in the
     * language of the language setting.
     *
     * @param int|null $storeId
     * @return string Escaped HTML.
     */
    public function getText($storeId = null)
    {
        $text = $this->getDefaultText($this->getConfigValue(self::CONFIG_PATH_LANGUAGE, $storeId));

        return (string) $this->escaper->escapeHtml($text, self::ALLOWED_TAGS);
    }

    /**
     * Return the default legal note in each language of the language setting.
     *
     * @return array Texts keyed by language ('auto', 'en', 'da', 'es').
     */
    public function getDefaultTexts()
    {
        $texts = [];
        foreach ([self::LANGUAGE_AUTO, 'en', 'da', 'es'] as $language) {
            $texts[$language] = $this->getDefaultText($language);
        }

        return $texts;
    }

    /**
     * Return a valid CSS margin, or empty string. A plain number is taken as
     * pixels, and negative values are allowed, e.g. to move the legal note
     * closer to the payment method name.
     *
     * @param mixed $value Value entered by the merchant
     * @return string
     */
    public static function sanitizeMargin($value)
    {
        $value = is_string($value) ? strtolower(trim($value)) : '';
        $value = str_replace(',', '.', $value);

        if (!preg_match('/^(-?)(\d+(?:\.\d+)?|\.\d+)\s*(px|em|rem|pt|%)?$/', $value, $matches)) {
            return '';
        }
        if ((float) $matches[2] == 0) {
            return '0px';
        }

        return $matches[1] . $matches[2] . (empty($matches[3]) ? 'px' : $matches[3]);
    }

    /**
     * Return the default of one of the margin settings.
     *
     * @param string $path One of the CONFIG_PATH_MARGIN_* paths
     * @return string
     */
    public static function getDefaultMargin($path)
    {
        return isset(self::DEFAULT_MARGINS[$path]) ? self::DEFAULT_MARGINS[$path] : '0px';
    }

    /**
     * Return the margin of one of the margin settings, or its default.
     *
     * @param string $path One of the CONFIG_PATH_MARGIN_* paths
     * @param int|null $storeId
     * @return string
     */
    private function getMargin($path, $storeId)
    {
        $margin = self::sanitizeMargin($this->getConfigValue($path, $storeId));

        return $margin !== '' ? $margin : self::getDefaultMargin($path);
    }

    /**
     * Return the inline style of the legal note, built from the styling
     * settings, e.g. "font-weight:600;color:#cc0000". It is not escaped.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getStyle($storeId = null)
    {
        $declarations = [];

        $fontSize = self::sanitizeFontSize($this->getConfigValue(self::CONFIG_PATH_FONT_SIZE, $storeId));
        if ($fontSize !== '') {
            $declarations[] = 'font-size:' . $fontSize;
        }

        $fontStyle = $this->getConfigValue(self::CONFIG_PATH_FONT_STYLE, $storeId);
        if (!in_array($fontStyle, self::FONT_STYLES, true)) {
            $fontStyle = self::DEFAULT_FONT_STYLE;
        }
        $declarations[] = 'font-weight:' . (strpos($fontStyle, 'bold') !== false ? '600' : 'normal');
        $declarations[] = 'font-style:' . (strpos($fontStyle, 'italic') !== false ? 'italic' : 'normal');

        $color = self::sanitizeColor($this->getConfigValue(self::CONFIG_PATH_COLOR, $storeId));
        if ($color !== '') {
            $declarations[] = 'color:' . $color;
        }

        $align = $this->getConfigValue(self::CONFIG_PATH_ALIGN, $storeId);
        if (in_array($align, self::ALIGNMENTS, true)) {
            $declarations[] = 'text-align:' . $align;
        }

        $declarations[] = 'margin-top:' . $this->getMargin(self::CONFIG_PATH_MARGIN_TOP, $storeId);
        $declarations[] = 'margin-bottom:' . $this->getMargin(self::CONFIG_PATH_MARGIN_BOTTOM, $storeId);
        $declarations[] = 'margin-left:' . $this->getMargin(self::CONFIG_PATH_MARGIN_LEFT, $storeId);

        // The extra CSS comes last, so it can override the controls above.
        $extraCss = self::sanitizeExtraCss($this->getConfigValue(self::CONFIG_PATH_EXTRA_CSS, $storeId));
        if ($extraCss !== '') {
            $declarations[] = $extraCss;
        }

        return implode(';', $declarations);
    }

    /**
     * Return a valid CSS font size, or empty string. A plain number is taken
     * as pixels.
     *
     * @param mixed $value Value entered by the merchant
     * @return string
     */
    public static function sanitizeFontSize($value)
    {
        $value = is_string($value) ? strtolower(trim($value)) : '';
        $value = str_replace(',', '.', $value);

        if (!preg_match('/^(\d+(?:\.\d+)?|\.\d+)\s*(px|em|rem|pt|%)?$/', $value, $matches)) {
            return '';
        }
        if ((float) $matches[1] <= 0) {
            return '';
        }

        return $matches[1] . (empty($matches[2]) ? 'px' : $matches[2]);
    }

    /**
     * Return a valid CSS color (hex or a color name), or empty string.
     *
     * @param mixed $value Value entered by the merchant
     * @return string
     */
    public static function sanitizeColor($value)
    {
        $value = is_string($value) ? trim($value) : '';

        return preg_match('/^(#[0-9a-fA-F]{3}|#[0-9a-fA-F]{6}|[a-zA-Z]{3,20})$/', $value) ? $value : '';
    }

    /**
     * Return the extra CSS declarations (e.g. "padding: 8px; border: 1px solid
     * #ccc") in a form that is safe to print inside a style attribute. Only
     * declarations are supported, so anything that could open a new rule or
     * a tag is removed.
     *
     * @param mixed $css Value entered by the merchant
     * @return string
     */
    public static function sanitizeExtraCss($css)
    {
        if (!is_string($css) || trim($css) === '') {
            return '';
        }

        $css = strip_tags($css);
        $css = preg_replace('#/\*.*?\*/#s', '', $css);
        $css = str_replace(['{', '}', '<', '>', '\\'], '', $css);
        $css = preg_replace('/expression\s*\(|javascript\s*:|@import/i', '', $css);
        $css = preg_replace('/\s+/', ' ', $css);

        return trim($css, " ;\t\n\r");
    }

    /**
     * Return a setting of the store view as a string.
     *
     * @param string $path
     * @param int|null $storeId
     * @return string
     */
    private function getConfigValue($path, $storeId)
    {
        return (string) $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
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
