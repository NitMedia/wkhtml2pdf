<?php namespace Nitmedia\Wkhtml2pdf;

use Illuminate\Support\ServiceProvider;

class L5Wkhtml2pdfServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
     public function boot()
     {
         $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('Wkhtml2pdf.php')
        ], 'config');
     }
     
    public function register()
    {
        $this->app->singleton('wkhtml2pdf', function($app)
        {
            return new Wkhtml2pdf(new LaravelConfig($app['config']), new LaravelView($app['view']));
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('wkhtml2pdf');
    }
}
