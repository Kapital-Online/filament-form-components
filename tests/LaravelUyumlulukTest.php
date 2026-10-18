<?php

namespace Kapital\Filament\FormComponents\Tests;

use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Kapital\Filament\FormComponents\Components\Select;
use Kapital\Filament\FormComponents\FilamentFormComponentsServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase;

class LaravelUyumlulukTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [LivewireServiceProvider::class, SupportServiceProvider::class, FormsServiceProvider::class, FilamentFormComponentsServiceProvider::class];
    }

    public function test_coklu_secimde_pasif_secenek_bilgisi_korunur(): void
    {
        $select = Select::make('urunler')->multiple()->options([1 => 'Aktif', 2 => 'Pasif'])
            ->disableOptionWhen(fn ($value): bool => (int) $value === 2);

        $this->assertSame([
            ['label' => 'Aktif', 'value' => '1', 'disabled' => false],
            ['label' => 'Pasif', 'value' => '2', 'disabled' => true],
        ], $select->getOptionsForJs());
    }

    public function test_coklu_secim_sinirlari_alpine_ve_dogrulamaya_aktarilir(): void
    {
        $select = Select::make('urunler')->multiple()->minSelectable(1)->maxSelectable(3);

        $this->assertSame(1, $select->getMinSelectable());
        $this->assertContains('min:1', $select->getValidationRules());
        $this->assertContains('max:3', $select->getValidationRules());
        $this->assertSame('3', $select->getExtraAlpineAttributeBag()->get('max-item-count'));
        $this->assertArrayHasKey('filament-form-components', $this->app['view']->getFinder()->getHints());
    }
}
