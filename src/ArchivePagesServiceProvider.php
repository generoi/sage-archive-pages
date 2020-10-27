<?php

namespace Genero\Sage\ArchivePages;

use Genero\Sage\ArchivePages\Integrations\Blade;
use Roots\Acorn\ServiceProvider;

class ArchivePagesServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('archives', ArchivePages::class);
        $this->app->singleton('archives.integrations.blade', Blade::class);
    }

    public function boot()
    {
        $this->app['archives.integrations.blade']->addBindings();
    }
}
