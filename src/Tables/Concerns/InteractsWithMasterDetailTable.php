<?php

namespace Kapital\Filament\FormComponents\Tables\Concerns;

use Illuminate\Database\Eloquent\Model;
use Kapital\Filament\FormComponents\Resources\Table as ResourceTable;
use Kapital\Filament\FormComponents\Tables\MasterDetailConfig;
use Kapital\Filament\FormComponents\Tables\MasterDetailTable;

trait InteractsWithMasterDetailTable
{
    public array $expandedMasterDetailRecords = [];

    public function toggleMasterDetailRecord(string $recordKey): void
    {
        if (in_array($recordKey, $this->expandedMasterDetailRecords, true)) {
            $this->expandedMasterDetailRecords = array_values(array_filter(
                $this->expandedMasterDetailRecords,
                fn(string $key): bool => $key !== $recordKey
            ));

            return;
        }

        $this->expandedMasterDetailRecords[] = $recordKey;
    }

    public function isMasterDetailRecordExpanded(string $recordKey): bool
    {
        return in_array($recordKey, $this->expandedMasterDetailRecords, true);
    }

    public function getMasterDetailRecordKey(Model $record): string
    {
        $config = $this->resolveMasterDetailConfig();

        if (! $config) {
            return (string) $record->getKey();
        }

        if (is_callable($config->detailKey)) {
            return (string) value($config->detailKey, $record, $this);
        }

        if (is_string($config->detailKey) && $config->detailKey !== '') {
            return $config->detailKey . '-' . $record->getKey();
        }

        return (string) $record->getKey();
    }

    public function getMasterDetailParameters(Model $record): array
    {
        $config = $this->resolveMasterDetailConfig();

        if (! $config) {
            return [];
        }

        return (array) value($config->parameters, $record, $this);
    }

    public function getMasterDetailLivewireComponent(): ?string
    {
        return $this->resolveMasterDetailConfig()?->livewireComponent;
    }

    public function getMasterDetailExpandIcon(Model $record): string
    {
        $config = $this->resolveMasterDetailConfig();

        if (! $config) {
            return 'heroicon-o-chevron-right';
        }

        if ($this->isMasterDetailRecordExpanded($this->getMasterDetailRecordKey($record))) {
            return (string) value($config->collapseIcon, $record, $this);
        }

        return (string) value($config->expandIcon, $record, $this);
    }

    public function isMasterDetailRecordExpandable(Model $record): bool
    {
        $config = $this->resolveMasterDetailConfig();

        if (! $config) {
            return false;
        }

        return (bool) value($config->isExpandable, $record, $this);
    }

    public function getMasterDetailWrapperClass(Model $record): string
    {
        $config = $this->resolveMasterDetailConfig();

        if (! $config || ! $config->detailWrapperClass) {
            return 'p-4 bg-gray-50 dark:bg-gray-800/60';
        }

        return (string) value($config->detailWrapperClass, $record, $this);
    }

    public function hasMasterDetailEnabled(): bool
    {
        return (bool) $this->resolveMasterDetailConfig()?->isEnabled;
    }

    public function getMasterDetailColumnCount(): int
    {
        $columnCount = method_exists($this, 'getCachedTableColumns')
            ? count($this->getCachedTableColumns())
            : 0;

        if (method_exists($this, 'getCachedTableActions') && count($this->getCachedTableActions()) > 0) {
            $columnCount++;
        }

        $isSelectionEnabled = method_exists($this, 'isTableSelectionEnabled') ? $this->isTableSelectionEnabled() : false;
        $isReordering = method_exists($this, 'isTableReordering') ? $this->isTableReordering() : false;

        if ($isSelectionEnabled || $isReordering) {
            $columnCount++;
        }

        return max($columnCount, 1);
    }

    protected function resolveMasterDetailConfig(): ?MasterDetailConfig
    {
        if (! method_exists(static::class, 'getResource')) {
            return null;
        }

        $resourceClass = forward_static_call([static::class, 'getResource']);

        if (! is_string($resourceClass) || ! method_exists($resourceClass, 'table')) {
            return null;
        }

        $resourceTable = $resourceClass::table(ResourceTable::make());

        if (! $resourceTable instanceof ResourceTable) {
            return null;
        }

        return $resourceTable->getMasterDetailConfig();
    }

    protected function getTable(): \Filament\Tables\Table
    {
        $table = MasterDetailTable::make($this);

        $table->masterDetailConfig($this->resolveMasterDetailConfig());

        return $table;
    }
}
