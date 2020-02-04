<?php
/**
 * Defines a namespace for the class.
 */
namespace App\Providers;

/**
 * @uses
 */
use Illuminate\Support\ServiceProvider;
use App\Library\Services\ClickSend\ClickSendApi;

/**
 * Class ClickSendServiceProvider
 *
 * @package App\Providers
 */
class ClickSendServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register services.
     * Sets up the Singleton instance of the SMS service
     */
    public function register()
    {
        $this->app->singleton(ClickSendApi::class, function () {

            // uses configuration stored in /config/services.ph [clicksend]
            $config = config('services.clicksend');

            return new ClickSendApi($config['username'], $config['api_key'], $config['sms_from']);
        });
    }

    /**
     * Bootstrap services.
     * (Provider)
     *
     * @return array
     */
    public function boot()
    {
        return [ClickSendApi::class];
    }
}
