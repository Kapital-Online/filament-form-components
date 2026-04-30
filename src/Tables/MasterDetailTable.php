<?php

namespace Kapital\Filament\FormComponents\Tables;

use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table as BaseTable;

class MasterDetailTable extends BaseTable
{
    protected string $view = 'filament-form-components::tables.master-detail-index';

    protected ?MasterDetailConfig $masterDetailConfig = null;

    public static function make(HasTable $livewire): static
    {
        return app(static::class, ['livewire' => $livewire]);
    }

    public function masterDetailConfig(?MasterDetailConfig $config): static
    {
        $this->masterDetailConfig = $config;

        return $this;
    }

    public function getMasterDetailConfig(): ?MasterDetailConfig
    {
        return $this->masterDetailConfig;
    }
}
