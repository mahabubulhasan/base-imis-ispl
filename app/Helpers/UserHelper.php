<?php

use Illuminate\Support\Facades\DB;
use App\Enums\UserStatus;
use App\Models\User;
use Spatie\Permission\Models\Role;
if (!function_exists('get_user_status_description')) {
    /**
     * Get current cart uuid
     *
     * @return mixed
     */
    function get_user_status_description(int $status)
    {
        return UserStatus::getDescription($status);
    }
}


if (!function_exists('get_current_user_roles')) {
    /**
     * Get current cart uuid
     *
     * @return mixed
     */
    function get_current_user_roles()
    {
        $userDetail = User::findorfail(Auth::id());
        $userRoles = array();
        foreach($userDetail->roles as $role) {
          $userRoles[] = $role->name;
        }
        return $userRoles;
    }
}


if (!function_exists('get_current_user_created_at')) {
    /**
     * Get current cart uuid
     *
     * @return mixed
     */
    function get_current_user_created_at()
    {
        $userDetail = User::findorfail(Auth::id());

        return $userRoles;
    }
}

if (!function_exists('moveOthersToEnd')) {
    function moveOthersToEnd(array $array): array
    {
        $othersKey = array_search('Others', $array);
        if ($othersKey !== false) {
            unset($array[$othersKey]);
            $array[$othersKey] = 'Others';
        }
        return $array;
    }
}

if (!function_exists('translate_number')) {
    /**
     * Convert number to Bangla using Intl NumberFormatter
     *
     * @param int|float $number Input number
     * @return string Number formatted in Bangla digits
     */
    function translate_number($number, $locale = 'bn_BD'): string
    {
        // Create formatter for Bangla (Bangladesh) locale
        $formatter = new NumberFormatter($locale, NumberFormatter::DECIMAL);

        // Format the number
        return $formatter->format($number);
    }
}