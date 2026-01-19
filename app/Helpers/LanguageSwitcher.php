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
        $cookieLang = Cookie::get('app_language') ?: $defaultLang;

        // Temporary localhost-safe fallback: If language table doesn't exist, return simple default
        try {
            // Check if language table exists first by attempting a simple query
            // If this fails, we know the table doesn't exist
            $languages = Language::where('status', 'true')->get();
            
            // If we got here, table exists - validate cookie language
            if (!empty($cookieLang)) {
                $languageExists = Language::where('status', 'true')->where('code', $cookieLang)->exists();
                if (!$languageExists) {
                    Cookie::queue(Cookie::forget('app_language'));
                    $cookieLang = $defaultLang;
                }
            } else {
                $cookieLang = $defaultLang;
            }
        } catch (Throwable $e) {
            // Table missing or query failed - use defaults only
            $cookieLang = $defaultLang;
            $languages = collect([(object)['code' => 'en']]);
        }

        $l = str_replace('_', '-', $cookieLang);

        $text = '<li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="javascript:void(0)" data-toggle="dropdown" aria-expanded="false">' . strtoupper($l) . '</a>
            <div class="dropdown-menu dropdown-menu-left" style="min-width: 50px; padding: 5px 0; font-size: 14px;">';

        foreach ($languages as $lng) {
            $text .= '<a class="dropdown-item" href="' . route('lang.switch') . '?lang=' . $lng->code . '" style="padding: 8px 15px; font-size: 13px;">' . strtoupper($lng->code) . '</a>';
        }

        $text .= '</div></li>';

        return $text;
    }
}



