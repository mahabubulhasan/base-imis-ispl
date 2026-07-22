<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Cookie;
use Illuminate\Support\Facades\App;
use Streamstech\Ekpay\Config\Config;
use Streamstech\Ekpay\Config\ConfigKey;
use Streamstech\Ekpay\EkpayService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EkpayService::class, function ($app) {
            $config = new Config();
            $config
                ->set(ConfigKey::SUCCESS_URL, url("/payment/success"))
                ->set(ConfigKey::CANCEL_URL, url("/payment/cancel"))
                ->set(ConfigKey::FAIL_URL, url("/payment/failed"))
                ->set(ConfigKey::MERCHANT_REG_ID, config('ekpay.MERCHANT_REG_ID'))
                ->set(ConfigKey::MERCHANT_PAS_KEY, config('ekpay.MERCHANT_PAS_KEY'))
                ->set(ConfigKey::IPN_CHANNEL, '3') // 0=None, 1=Both, 2=Email, 3=API
                ->set(ConfigKey::IPN_URI, url("/payment/ipn"))
                ->set(ConfigKey::IPN_EMAIL, config('ekpay.IPN_EMAIL'))
                ->set(ConfigKey::MAC, config('ekpay.MAC'))
                ->set(Configkey::SANDBOX_ENABLED, config('ekpay.SANDBOX_ENABLED'));
            return new EkpayService($config);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
        $this->setAppLocale();
    }

    // function to set languge as base lang or selected lang
    private function setAppLocale()
    {
        $locale = 'en';

        if (!empty(Cookie::get('app_language'))) {
            try {
                $decrypted = \Crypt::decryptString(Cookie::get('app_language'));
                $locale = explode('|', $decrypted)[1];
            } catch (\Exception $e) {
            }
        }
        App::setLocale($locale);
    }
}
