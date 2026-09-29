<?php
/* Bangla and English. The English sentence is the key, so a page reads the
   same in the source as it does on screen, and anything not translated yet
   simply shows in English. Bangla is the default; the choice is a cookie. */

const LANGS = ['bn', 'en'];

function lang(): string
{
    static $lang = null;
    if ($lang === null) {
        $c    = $_COOKIE['tkl_lang'] ?? 'bn';
        $lang = in_array($c, LANGS, true) ? $c : 'bn';
        // the admin console is a staff tool and stays in English
        if (str_starts_with(basename($_SERVER['SCRIPT_NAME'] ?? ''), 'admin')) {
            $lang = 'en';
        }
    }
    return $lang;
}

function bangla_strings(): array
{
    static $bn = null;
    return $bn ??= require __DIR__ . '/lang_bn.php';
}

/**
 * The sentence in the reader's language. {name} style gaps are filled from
 * $vars after translating, so the Bangla can put them where Bangla wants them.
 */
function __(string $en, array $vars = []): string
{
    $text = lang() === 'bn' ? (bangla_strings()['text'][$en] ?? $en) : $en;
    if ($vars) {
        $pairs = [];
        foreach ($vars as $k => $v) {
            $pairs['{' . $k . '}'] = (string) $v;
        }
        $text = strtr($text, $pairs);
    }
    return $text;
}

/**
 * Notifications are written once, in English, so they read in whichever
 * language the person has picked when they open them. The known shapes are
 * matched and rebuilt in Bangla; anything else is shown as written.
 */
function __notice(string $text): string
{
    if (lang() !== 'bn') {
        return $text;
    }
    $text = bangla_strings()['text'][$text] ?? $text;
    foreach (bangla_strings()['patterns'] as $pattern => $bangla) {
        if (preg_match($pattern, $text)) {
            return preg_replace($pattern, $bangla, $text);
        }
    }
    return $text;
}

/** The button that flips the language, labelled in the language it switches to. */
function lang_button(string $class = 'tool lang-btn'): string
{
    $to = lang() === 'bn' ? 'en' : 'bn';
    return '<a class="' . $class . '" href="' . htmlspecialchars(lang_switch_url(), ENT_QUOTES, 'UTF-8') . '" hreflang="' . $to . '"'
         . ' lang="' . $to . '" aria-label="' . ($to === 'en' ? 'Read in English' : 'বাংলায় পড়ুন') . '">'
         . ($to === 'en' ? 'EN' : 'বাং') . '</a>';
}

/** The address to switch language from the current page. */
function lang_switch_url(): string
{
    $back = $_SERVER['REQUEST_URI'] ?? 'index.php';
    return 'language.php?to=' . (lang() === 'bn' ? 'en' : 'bn') . '&back=' . rawurlencode($back);
}
