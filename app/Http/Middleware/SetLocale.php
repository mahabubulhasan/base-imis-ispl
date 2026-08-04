<?php

// Last Modified: August 4, 2026
// Developed By: Streams Tech Ltd.
// Description: Middleware to set application locale based on app_language cookie or user preference

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $language = 'en'; // Default language

        // Check if app_language cookie is set
        $cookieValue = $request->cookie('app_language');

        if (!empty($cookieValue)) {
            try {
                // Extract language from cookie format "timestamp|language"
                $parts = explode('|', $cookieValue);
                if (count($parts) === 2 && in_array($parts[1], ['en', 'bn'])) {
                    $language = $parts[1];
                }
            } catch (\Exception $e) {
                // If parsing fails, use default
                $language = 'en';
            }
        }

        // Set application locale
        app()->setLocale($language);

        return $next($request);
    }
}
