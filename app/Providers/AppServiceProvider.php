<?php

namespace App\Providers;

use App\Services\DatabaseSchemaEnsurer;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! $this->app->runningUnitTests()) {
            $this->app->make(DatabaseSchemaEnsurer::class)->ensure();
        }

        \Illuminate\Support\Facades\App::setLocale(config('app.locale', 'pt_BR'));

        if ($this->app->environment('local') && ! $this->app->runningInConsole()) {
            $request = request();
            if ($request->hasHeader('Host')) {
                URL::forceRootUrl($request->getScheme().'://'.$request->getHttpHost());
            }
        }
    }
}
