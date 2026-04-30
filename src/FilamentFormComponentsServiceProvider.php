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
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'filament-form-components');
    }
}
