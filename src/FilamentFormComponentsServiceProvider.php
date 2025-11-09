<?php

namespace Kapital\Filament\FormComponents;

use Illuminate\Support\ServiceProvider;

class FilamentFormComponentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // This package doesn't need to publish anything
        // Components are used directly via their namespaced class names
    }
}
