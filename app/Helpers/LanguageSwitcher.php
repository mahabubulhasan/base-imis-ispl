<?php
namespace App\Helpers;

use App\Models\Language\Language;
use Illuminate\Support\Facades\Cookie;

class LanguageSwitcher
{
    public static function language_switcher()
    {
        $defaultLang = 'en';
        $cookieLang = Cookie::get('app_language');


        // Check if the language in the cookie still exists in the database
        $languageExists = Language::where('status', 'true')->where('code', $cookieLang)->exists();

        if (!$languageExists) {
            // Remove the cookie if the selected language is deleted
            Cookie::queue(Cookie::forget('app_language'));
            $cookieLang = $defaultLang; // Reset to default language
        }

        $l = str_replace('_', '-', $cookieLang);

        $text = '<li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-expanded="false">' . strtoupper($l) . '</a>
            <div class="dropdown-menu dropdown-menu-left" style="min-width: 50px; padding: 5px 0; font-size: 14px;">';

        // Fetch only active languages
        $languages = Language::where('status', 'true')->get();

        foreach ($languages as $lng) {
            $text .= '<a class="dropdown-item" href="' . route('lang.switch') . '?lang=' . $lng->code . '" style="padding: 8px 15px; font-size: 13px;">' . strtoupper($lng->code) . '</a>';
        }

        $text .= '</div></li>';

        return $text;
    }
}



