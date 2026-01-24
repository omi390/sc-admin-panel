<?php

use Illuminate\Support\Str;
use Modules\BusinessSettingsModule\Entities\BusinessSettings;
use Modules\BusinessSettingsModule\Entities\DataSetting;
use Modules\BusinessSettingsModule\Entities\LoginSetup;
use Modules\ProviderManagement\Entities\ProviderSetting;
use Modules\UserManagement\Entities\User;

if (!function_exists('business_config')) {
    function business_config($key, $settings_type)
    {
        try {
            $config = BusinessSettings::where('key_name', $key)->where('settings_type', $settings_type)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (!function_exists('login_setup')) {
    function login_setup($key)
    {
        try {
            $config = LoginSetup::where('key', $key)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}

if (!function_exists('data_config')) {
    function data_config($key, $settings_type)
    {
        try {
            $config = DataSetting::where('key', $key)->where('type', $settings_type)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}


if (!function_exists('provider_config')) {
    function provider_config($key, $settings_type, $provider_id)
    {
        try {
            $config = ProviderSetting::where('key_name', $key)->where('settings_type', $settings_type)->where('provider_id', $provider_id)->first();
        } catch (Exception $exception) {
            return null;
        }

        return (isset($config)) ? $config : null;
    }
}


if (!function_exists('pagination_limit')) {
    function pagination_limit()
    {
        try {
            if (!session()->has('pagination_limit')) {
                $limit = BusinessSettings::where('key_name', 'pagination_limit')->where('settings_type', 'business_information')->first()->live_values;
                session()->put('pagination_limit', $limit);
            } else {
                $limit = session('pagination_limit');
            }
        } catch (Exception $exception) {
            return 10;
        }

        return $limit;
    }
}

if (!function_exists('currency_code')) {
    function currency_code(): string
    {
        $code = business_config('currency_code', 'business_information')['live_values'];
        return $code ?? 'USD';
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        $code = business_config('currency_code', 'business_information')['live_values'];
        $symbol = '$';
        foreach (CURRENCIES as $currency) {
            if ($currency['code'] == $code) {
                $symbol = $currency['symbol'];
            }
        }

        return $symbol;
    }
}

if (!function_exists('with_currency_symbol')) {
    function with_currency_symbol($value): string
    {
        $position = business_config('currency_symbol_position', 'business_information')['live_values']??'right';
        $decimal_point = business_config('currency_decimal_point', 'business_information')['live_values']??2;
        $code = business_config('currency_code', 'business_information')['live_values'];
        $symbol = '$';
        foreach (CURRENCIES as $currency) {
            if ($currency['code'] == $code) {
                $symbol = $currency['symbol'];
            }
        }

        if($position == 'left') {
            return $symbol . number_format($value, $decimal_point, '.', ',');
        } else {
            return number_format($value, $decimal_point, '.', ',') . $symbol;
        }

    }
}

if (!function_exists('with_decimal_point')) {
    function with_decimal_point($value): float
    {
        $decimal_point = business_config('currency_decimal_point', 'business_information')['live_values']??2;
        return (float)(number_format($value, $decimal_point, '.', ''));
    }
}

if (!function_exists('generate_referer_code')) {
    function generate_referer_code() {
        $ref_code = strtoupper(Str::random(10));

        if (User::where('ref_code', '=', $ref_code)->exists()) {
            return generate_referer_code();
        }

        return $ref_code;
    }
}

if (!function_exists('getLanguageCode')) {
    function getLanguageCode(string $country_code): string
    {
        $locales = array(
            'en-English(default)',
            'af-Afrikaans',
            'sq-Albanian - shqip',
            'am-Amharic - አማርኛ',
            'ar-Arabic - العربية',
            'an-Aragonese - aragonés',
            'hy-Armenian - հայերեն',
            'ast-Asturian - asturianu',
            'az-Azerbaijani - azərbaycan dili',
            'eu-Basque - euskara',
            'be-Belarusian - беларуская',
            'bn-Bengali - বাংলা',
            'bs-Bosnian - bosanski',
            'br-Breton - brezhoneg',
            'bg-Bulgarian - български',
            'ca-Catalan - català',
            'ckb-Central Kurdish - کوردی (دەستنوسی عەرەبی)',
            'zh-Chinese - 中文',
            'zh-HK-Chinese (Hong Kong) - 中文（香港）',
            'zh-CN-Chinese (Simplified) - 中文（简体）',
            'zh-TW-Chinese (Traditional) - 中文（繁體）',
            'co-Corsican',
            'hr-Croatian - hrvatski',
            'cs-Czech - čeština',
            'da-Danish - dansk',
            'nl-Dutch - Nederlands',
            'en-AU-English (Australia)',
            'en-CA-English (Canada)',
            'en-IN-English (India)',
            'en-NZ-English (New Zealand)',
            'en-ZA-English (South Africa)',
            'en-GB-English (United Kingdom)',
            'en-US-English (United States)',
            'eo-Esperanto - esperanto',
            'et-Estonian - eesti',
            'fo-Faroese - føroyskt',
            'fil-Filipino',
            'fi-Finnish - suomi',
            'fr-French - français',
            'fr-CA-French (Canada) - français (Canada)',
            'fr-FR-French (France) - français (France)',
            'fr-CH-French (Switzerland) - français (Suisse)',
            'gl-Galician - galego',
            'ka-Georgian - ქართული',
            'de-German - Deutsch',
            'de-AT-German (Austria) - Deutsch (Österreich)',
            'de-DE-German (Germany) - Deutsch (Deutschland)',
            'de-LI-German (Liechtenstein) - Deutsch (Liechtenstein)
            ',
            'de-CH-German (Switzerland) - Deutsch (Schweiz)',
            'el-Greek - Ελληνικά',
            'gn-Guarani',
            'gu-Gujarati - ગુજરાતી',
            'ha-Hausa',
            'haw-Hawaiian - ʻŌlelo Hawaiʻi',
            'he-Hebrew - עברית',
            'hi-Hindi - हिन्दी',
            'hu-Hungarian - magyar',
            'is-Icelandic - íslenska',
            'id-Indonesian - Indonesia',
            'ia-Interlingua',
            'ga-Irish - Gaeilge',
            'it-Italian - italiano',
            'it-IT-Italian (Italy) - italiano (Italia)',
            'it-CH-Italian (Switzerland) - italiano (Svizzera)',
            'ja-Japanese - 日本語',
            'kn-Kannada - ಕನ್ನಡ',
            'kk-Kazakh - қазақ тілі',
            'km-Khmer - ខ្មែរ',
            'ko-Korean - 한국어',
            'ku-Kurdish - Kurdî',
            'ky-Kyrgyz - кыргызча',
            'lo-Lao - ລາວ',
            'la-Latin',
            'lv-Latvian - latviešu',
            'ln-Lingala - lingála',
            'lt-Lithuanian - lietuvių',
            'mk-Macedonian - македонски',
            'ms-Malay - Bahasa Melayu',
            'ml-Malayalam - മലയാളം',
            'mt-Maltese - Malti',
            'mr-Marathi - मराठी',
            'mn-Mongolian - монгол',
            'ne-Nepali - नेपाली',
            'no-Norwegian - norsk',
            'nb-Norwegian Bokmål - norsk bokmål',
            'nn-Norwegian Nynorsk - nynorsk',
            'oc-Occitan',
            'or-Oriya - ଓଡ଼ିଆ',
            'om-Oromo - Oromoo',
            'ps-Pashto - پښتو',
            'fa-Persian - فارسی',
            'pl-Polish - polski',
            'pt-Portuguese - português',
            'pt-BR-Portuguese (Brazil) - português (Brasil)',
            'pt-PT-Portuguese (Portugal) - português (Portugal)',
            'pa-Punjabi - ਪੰਜਾਬੀ',
            'qu-Quechua',
            'ro-Romanian - română',
            'mo-Romanian (Moldova) - română (Moldova)',
            'rm-Romansh - rumantsch',
            'ru-Russian - русский',
            'gd-Scottish Gaelic',
            'sr-Serbian - српски',
            'sh-Serbo-Croatian - Srpskohrvatski',
            'sn-Shona - chiShona',
            'sd-Sindhi',
            'si-Sinhala - සිංහල',
            'sk-Slovak - slovenčina',
            'sl-Slovenian - slovenščina',
            'so-Somali - Soomaali',
            'st-Southern Sotho',
            'es-Spanish - español',
            'es-AR-Spanish (Argentina) - español (Argentina)',
            'es-419-Spanish (Latin America) - español (Latinoamérica)
            ',
            'es-MX-Spanish (Mexico) - español (México)',
            'es-ES-Spanish (Spain) - español (España)',
            'es-US-Spanish (United States) - español (Estados Unidos)
            ',
            'su-Sundanese',
            'sw-Swahili - Kiswahili',
            'sv-Swedish - svenska',
            'tg-Tajik - тоҷикӣ',
            'ta-Tamil - தமிழ்',
            'tt-Tatar',
            'te-Telugu - తెలుగు',
            'th-Thai - ไทย',
            'ti-Tigrinya - ትግርኛ',
            'to-Tongan - lea fakatonga',
            'tr-Turkish - Türkçe',
            'tk-Turkmen',
            'tw-Twi',
            'uk-Ukrainian - українська',
            'ur-Urdu - اردو',
            'ug-Uyghur',
            'uz-Uzbek - o‘zbek',
            'vi-Vietnamese - Tiếng Việt',
            'wa-Walloon - wa',
            'cy-Welsh - Cymraeg',
            'fy-Western Frisian',
            'xh-Xhosa',
            'yi-Yiddish',
            'yo-Yoruba - Èdè Yorùbá',
            'zu-Zulu - isiZulu',
        );

        foreach ($locales as $locale) {
            $locale_region = explode('-',$locale);
            if ($country_code == $locale_region[0]) {
                return $locale_region[0];
            }
        }

        return "en";
    }
}

if (!function_exists('auto_translator')) {
    function auto_translator($q, $sl, $tl): array|string
    {
        $res = file_get_contents("https://translate.googleapis.com/translate_a/single?client=gtx&ie=UTF-8&oe=UTF-8&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&dt=at&sl=" . $sl . "&tl=" . $tl . "&hl=hl&q=" . urlencode($q), $_SERVER['DOCUMENT_ROOT'] . "/transes.html");
        $res = json_decode($res);
        return str_replace('_',' ',$res[0][0][0]);
    }
}

if (!function_exists('language_load')) {
    function language_load()
    {
        if (\session()->has('language_settings')) {
            $language = \session('language_settings');
        } else {
            $language = BusinessSettings::where('key_name', 'system_language')->first();
            \session()->put('language_settings', $language);
        }
        return $language;
    }
}

if (!function_exists('provider_language_load')) {
    function provider_language_load()
    {
        if (\session()->has('provider_language_settings')) {
            $language = \session('provider_language_settings');
        } else {
            $language = BusinessSettings::where('key_name', 'system_language')->first();
            \session()->put('provider_language_settings', $language);
        }
        return $language;
    }
}

if (!function_exists('landing_language_load')) {
    function landing_language_load()
    {
        if (\session()->has('landing_language_settings')) {
            $language = \session('landing_language_settings');
        } else {
            $language = BusinessSettings::where('key_name', 'system_language')->first();
            \session()->put('landing_language_settings', $language);
        }
        return $language;
    }
}

if (!function_exists('get_language_name')) {
    function get_language_name($key)
    {
        $languages = array(
            "af" => "Afrikaans",
            "sq" => "Albanian - shqip",
            "am" => "Amharic - አማርኛ",
            "ar" => "Arabic - العربية",
            "an" => "Aragonese - aragonés",
            "hy" => "Armenian - հայերեն",
            "ast" => "Asturian - asturianu",
            "az" => "Azerbaijani - azərbaycan dili",
            "eu" => "Basque - euskara",
            "be" => "Belarusian - беларуская",
            "bn" => "Bengali - বাংলা",
            "bs" => "Bosnian - bosanski",
            "br" => "Breton - brezhoneg",
            "bg" => "Bulgarian - български",
            "ca" => "Catalan - català",
            "ckb" => "Central Kurdish - کوردی (دەستنوسی عەرەبی)",
            "zh" => "Chinese - 中文",
            "zh-HK" => "Chinese (Hong Kong) - 中文（香港）",
            "zh-CN" => "Chinese (Simplified) - 中文（简体）",
            "zh-TW" => "Chinese (Traditional) - 中文（繁體）",
            "co" => "Corsican",
            "hr" => "Croatian - hrvatski",
            "cs" => "Czech - čeština",
            "da" => "Danish - dansk",
            "nl" => "Dutch - Nederlands",
            "en" => "English",
            "en-AU" => "English (Australia)",
            "en-CA" => "English (Canada)",
            "en-IN" => "English (India)",
            "en-NZ" => "English (New Zealand)",
            "en-ZA" => "English (South Africa)",
            "en-GB" => "English (United Kingdom)",
            "en-US" => "English (United States)",
            "eo" => "Esperanto - esperanto",
            "et" => "Estonian - eesti",
            "fo" => "Faroese - føroyskt",
            "fil" => "Filipino",
            "fi" => "Finnish - suomi",
            "fr" => "French - français",
            "fr-CA" => "French (Canada) - français (Canada)",
            "fr-FR" => "French (France) - français (France)",
            "fr-CH" => "French (Switzerland) - français (Suisse)",
            "gl" => "Galician - galego",
            "ka" => "Georgian - ქართული",
            "de" => "German - Deutsch",
            "de-AT" => "German (Austria) - Deutsch (Österreich)",
            "de-DE" => "German (Germany) - Deutsch (Deutschland)",
            "de-LI" => "German (Liechtenstein) - Deutsch (Liechtenstein)",
            "de-CH" => "German (Switzerland) - Deutsch (Schweiz)",
            "el" => "Greek - Ελληνικά",
            "gn" => "Guarani",
            "gu" => "Gujarati - ગુજરાતી",
            "ha" => "Hausa",
            "haw" => "Hawaiian - ʻŌlelo Hawaiʻi",
            "he" => "Hebrew - עברית",
            "hi" => "Hindi - हिन्दी",
            "hu" => "Hungarian - magyar",
            "is" => "Icelandic - íslenska",
            "id" => "Indonesian - Indonesia",
            "ia" => "Interlingua",
            "ga" => "Irish - Gaeilge",
            "it" => "Italian - italiano",
            "it-IT" => "Italian (Italy) - italiano (Italia)",
            "it-CH" => "Italian (Switzerland) - italiano (Svizzera)",
            "ja" => "Japanese - 日本語",
            "kn" => "Kannada - ಕನ್ನಡ",
            "kk" => "Kazakh - қазақ тілі",
            "km" => "Khmer - ខ្មែរ",
            "ko" => "Korean - 한국어",
            "ku" => "Kurdish - Kurdî",
            "ky" => "Kyrgyz - кыргызча",
            "lo" => "Lao - ລາວ",
            "la" => "Latin",
            "lv" => "Latvian - latviešu",
            "ln" => "Lingala - lingála",
            "lt" => "Lithuanian - lietuvių",
            "mk" => "Macedonian - македонски",
            "ms" => "Malay - Bahasa Melayu",
            "ml" => "Malayalam - മലയാളം",
            "mt" => "Maltese - Malti",
            "mr" => "Marathi - मराठी",
            "mn" => "Mongolian - монгол",
            "ne" => "Nepali - नेपाली",
            "no" => "Norwegian - norsk",
            "nb" => "Norwegian Bokmål - norsk bokmål",
            "nn" => "Norwegian Nynorsk - nynorsk",
            "oc" => "Occitan",
            "or" => "Oriya - ଓଡ଼ିଆ",
            "om" => "Oromo - Oromoo",
            "ps" => "Pashto - پښتو",
            "fa" => "Persian - فارسی",
            "pl" => "Polish - polski",
            "pt" => "Portuguese - português",
            "pt-BR" => "Portuguese (Brazil) - português (Brasil)",
            "pt-PT" => "Portuguese (Portugal) - português (Portugal)",
            "pa" => "Punjabi - ਪੰਜਾਬੀ",
            "qu" => "Quechua",
            "ro" => "Romanian - română",
            "mo" => "Romanian (Moldova) - română (Moldova)",
            "rm" => "Romansh - rumantsch",
            "ru" => "Russian - русский",
            "gd" => "Scottish Gaelic",
            "sr" => "Serbian - српски",
            "sh" => "Serbo-Croatian - Srpskohrvatski",
            "sn" => "Shona - chiShona",
            "sd" => "Sindhi",
            "si" => "Sinhala - සිංහල",
            "sk" => "Slovak - slovenčina",
            "sl" => "Slovenian - slovenščina",
            "so" => "Somali - Soomaali",
            "st" => "Southern Sotho",
            "es" => "Spanish - español",
            "es-AR" => "Spanish (Argentina) - español (Argentina)",
            "es-419" => "Spanish (Latin America) - español (Latinoamérica)",
            "es-MX" => "Spanish (Mexico) - español (México)",
            "es-ES" => "Spanish (Spain) - español (España)",
            "es-US" => "Spanish (United States) - español (Estados Unidos)",
            "su" => "Sundanese",
            "sw" => "Swahili - Kiswahili",
            "sv" => "Swedish - svenska",
            "tg" => "Tajik - тоҷикӣ",
            "ta" => "Tamil - தமிழ்",
            "tt" => "Tatar",
            "te" => "Telugu - తెలుగు",
            "th" => "Thai - ไทย",
            "ti" => "Tigrinya - ትግርኛ",
            "to" => "Tongan - lea fakatonga",
            "tr" => "Turkish - Türkçe",
            "tk" => "Turkmen",
            "tw" => "Twi",
            "uk" => "Ukrainian - українська",
            "ur" => "Urdu - اردو",
            "ug" => "Uyghur",
            "uz" => "Uzbek - o‘zbek",
            "vi" => "Vietnamese - Tiếng Việt",
            "wa" => "Walloon - wa",
            "cy" => "Welsh - Cymraeg",
            "fy" => "Western Frisian",
            "xh" => "Xhosa",
            "yi" => "Yiddish",
            "yo" => "Yoruba - Èdè Yorùbá",
            "zu" => "Zulu - isiZulu",
        );
        return array_key_exists($key, $languages) ? $languages[$key] : $key;
    }
}

if (!function_exists('get_push_notification_message')) {
    function get_push_notification_message($key, $settings_type, $lang='en')
    {
        try {
            $config = BusinessSettings::where('key_name', $key)->where('settings_type', $settings_type)->
            with(['translations'=>function($query)use($lang){
                $query->where('locale', $lang);
            }])->first();
        } catch (Exception $exception) {
            return null;
        }

        if($config){
            if ($config->live_values[$key.'_status'] == 0) {
                return 0;
            }
            $message = $key.'_'.'message';
            return count($config->translations) > 0 ? $config->translations[0]['value'] : $config->live_values[$message];
        }else{
            return false;
        }
    }
}


