<?php

namespace Kapital\Filament\FormComponents\Tables;

use Closure;

class MasterDetailConfig
{
    public function __construct(
        public bool $isEnabled = false,
        public ?string $livewireComponent = null,
        public array | Closure $parameters = [],
        public string | Closure | null $detailKey = null,
        public bool | Closure $isExpandable = true,
        public string | Closure $expandIcon = 'heroicon-o-chevron-right',
        public string | Closure $collapseIcon = 'heroicon-o-chevron-down',
        public string | Closure | null $detailWrapperClass = null,
        public string | Closure | null $relation = null,
        public Closure | null $query = null,
    ) {}
}
