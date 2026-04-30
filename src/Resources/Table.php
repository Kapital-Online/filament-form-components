<?php

namespace Kapital\Filament\FormComponents\Resources;

use Closure;
use Filament\Resources\Table as BaseTable;
use Kapital\Filament\FormComponents\Tables\MasterDetailConfig;

class Table extends BaseTable
{
    protected ?MasterDetailConfig $masterDetailConfig = null;

    public function masterDetailLivewire(
        string $component,
        array | Closure $parameters = [],
        string | Closure | null $key = null,
        bool | Closure $expandable = true,
        string | Closure $expandIcon = 'heroicon-o-chevron-right',
        string | Closure $collapseIcon = 'heroicon-o-chevron-down',
        string | Closure | null $wrapperClass = null
    ): static {
        $this->masterDetailConfig = new MasterDetailConfig(
            isEnabled: true,
            livewireComponent: $component,
            parameters: $parameters,
            detailKey: $key,
            isExpandable: $expandable,
            expandIcon: $expandIcon,
            collapseIcon: $collapseIcon,
            detailWrapperClass: $wrapperClass,
        );

        return $this;
    }

    public function masterDetailRelation(string | Closure $relation): static
    {
        $config = $this->masterDetailConfig ?? new MasterDetailConfig();
        $config->relation = $relation;
        $config->isEnabled = true;

        $this->masterDetailConfig = $config;

        return $this;
    }

    public function masterDetailQuery(Closure $query): static
    {
        $config = $this->masterDetailConfig ?? new MasterDetailConfig();
        $config->query = $query;
        $config->isEnabled = true;

        $this->masterDetailConfig = $config;

        return $this;
    }

    public function getMasterDetailConfig(): ?MasterDetailConfig
    {
        return $this->masterDetailConfig;
    }
}
