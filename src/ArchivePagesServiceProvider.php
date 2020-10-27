<?php

namespace Genero\Sage\ArchivePages;

use Genero\Sage\ArchivePages\Integrations\Blade;
use Genero\Sage\ArchivePages\Integrations\Yoast;
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
        $this->app->singleton('archives.integrations.yoast', Yoast::class);
    }

    public function boot()
    {
        $this->app['archives.integrations.blade']->addBindings();
        $this->app['archives.integrations.yoast']->addBindings();
    }
}
