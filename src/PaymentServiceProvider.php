<?php
namespace Rahabit\Payment;

use Illuminate\Support\ServiceProvider;


class PaymentServiceProvider extends ServiceProvider
{
    public function boot(){
//        dd('Package Works Well !');
        $this->publishes([
            __DIR__ . '/../config/payment.php' => config_path('payment.php')
        ]);

        $this->publishes([
            __DIR__ . '/../config/RahabitPayment.php' => config_path('RahabitPayment.php')
        ]);


        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->publishes([
            __DIR__.'/../database/migrations' => $this->app->databasePath().'/migrations',
        ], 'migrations');




        $this->loadViewsFrom(__DIR__.'/../resources/views', 'rahabitpayment');
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/rahabitpayment'),
        ], 'views');

        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/rahabitpayment'),
        ], 'assets');

//        if (app('config')->get('app.env', 'production') !== 'production') {
//            $this->loadRoutesFrom(__DIR__.'/Gateways/Test/routes.php');
//        }

    }

    public function register()
    {
//        $this->app->singleton(Payment::class,function (){
//            return new Payment();
//        });
    }
}
