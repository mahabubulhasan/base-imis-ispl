<?php
namespace App\Helpers;

use App\Models\Language\Language;
use Illuminate\Support\Facades\Cookie;
use Throwable;

class LanguageSwitcher
{
    public static function language_switcher()
    {
        $defaultLang = 'en';
        $cookieLang = Cookie::get('app_language');

        // If language table is unavailable (e.g., fresh setup), fail gracefully
        try {
            // Only check existence when cookieLang is present; otherwise skip
            if (!empty($cookieLang)) {
                $languageExists = Language::where('status', 'true')->where('code', $cookieLang)->exists();
                if (!$languageExists) {
                    Cookie::queue(Cookie::forget('app_language'));
                    $cookieLang = $defaultLang; // reset to default
                }
            } else {
                $cookieLang = $defaultLang;
            }
        } catch (Throwable $e) {
            // Table likely missing; render a minimal static switcher with default lang
            $cookieLang = $defaultLang;
            return '<li class="nav-item"><span class="nav-link">' . strtoupper($cookieLang) . '</span></li>';
        }

        $l = str_replace('_', '-', $cookieLang);

        $text = '<li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-expanded="false">' . strtoupper($l) . '</a>
            <div class="dropdown-menu dropdown-menu-left" style="min-width: 50px; padding: 5px 0; font-size: 14px;">';

        // Fetch only active languages; guard missing table
        try {
            $languages = Language::where('status', 'true')->get();
        } catch (Throwable $e) {
            $languages = collect();
        }

        foreach ($languages as $lng) {
            $text .= '<a class="dropdown-item" href="' . route('lang.switch') . '?lang=' . $lng->code . '" style="padding: 8px 15px; font-size: 13px;">' . strtoupper($lng->code) . '</a>';
        }

        $text .= '</div></li>';

        return $text;
    }
}



