@php
    use Filament\Tables\Actions\Position as ActionsPosition;
    use Filament\Tables\Actions\RecordCheckboxPosition;
    use Filament\Tables\Filters\Layout as FiltersLayout;

    $actions = $getActions();
    $actionsPosition = $getActionsPosition();
    $actionsColumnLabel = $getActionsColumnLabel();
    $columns = $getColumns();
    $filterIndicators = collect($getFilters())
        ->mapWithKeys(fn (\Filament\Tables\Filters\BaseFilter $filter): array => [$filter->getName() => $filter->getIndicators()])
        ->filter(fn (array $indicators): bool => count($indicators))
        ->all();
    $header = $getHeader();
    $headerActions = $getHeaderActions();
    $heading = $getHeading();
    $description = $getDescription();
    $isReorderable = $isReorderable();
    $isReordering = $isReordering();
    $isColumnSearchVisible = $isSearchableByColumn();
    $isGlobalSearchVisible = $isSearchable();
    $isSelectionEnabled = $isSelectionEnabled();
    $recordCheckboxPosition = $getRecordCheckboxPosition();
    $isStriped = $isStriped();
    $isLoaded = $isLoaded();
    $hasFilters = $isFilterable();
    $filtersLayout = $getFiltersLayout();
    $hasFiltersPopover = $hasFilters && ($filtersLayout === FiltersLayout::Popover);
    $hasFiltersAboveContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::AboveContent, FiltersLayout::AboveContentCollapsible]);
    $records = $isLoaded ? $getRecords() : null;

    $isMasterDetailEnabled = method_exists($this, 'hasMasterDetailEnabled') && $this->hasMasterDetailEnabled();
@endphp

<div
    x-data="{
        selectedRecords: [],

        toggleSelectRecordsOnPage: function () {
            let keys = this.getRecordsOnPage()

            if (this.areRecordsSelected(keys)) {
                this.deselectRecords(keys)
                return
            }

            this.selectRecords(keys)
        },

        getRecordsOnPage: function () {
            let keys = []
            for (checkbox of $el.getElementsByClassName('filament-tables-record-checkbox')) {
                keys.push(checkbox.value)
            }
            return keys
        },

        selectRecords: function (keys) {
            for (key of keys) {
                if (this.isRecordSelected(key)) {
                    continue
                }
                this.selectedRecords.push(key)
            }
        },

        deselectRecords: function (keys) {
            for (key of keys) {
                let index = this.selectedRecords.indexOf(key)
                if (index === -1) {
                    continue
                }
                this.selectedRecords.splice(index, 1)
            }
        },

        isRecordSelected: function (key) {
            return this.selectedRecords.includes(key)
        },

        areRecordsSelected: function (keys) {
            return keys.every((key) => this.isRecordSelected(key))
        },

        mountBulkAction: function (name) {
            $wire.mountTableBulkAction(name, this.selectedRecords)
        },
    }"
    class="filament-tables-component"
    @if (! $isLoaded)
        wire:init="loadTable"
    @endif
>
    <x-tables::container>
        <div class="filament-tables-header-container">
            @if ($header)
                {{ $header }}
            @elseif ($heading || $headerActions)
                <div class="px-2 pt-2">
                    <x-tables::header :actions="$isReordering ? [] : $headerActions" class="mb-2">
                        <x-slot name="heading">{{ $heading }}</x-slot>
                        <x-slot name="description">{{ $description }}</x-slot>
                    </x-tables::header>
                    <x-tables::hr />
                </div>
            @endif

            @if ($hasFiltersAboveContent)
                <div class="px-2 pt-2">
                    <x-tables::filters :form="$getFiltersForm()" />
                </div>
            @endif

            @if (count($filterIndicators))
                <x-tables::filters.indicators :indicators="$filterIndicators" />
            @endif
        </div>

        @if ($hasFiltersPopover)
            <div class="filament-tables-header-toolbar flex h-14 items-center justify-between p-2 gap-2">
                <div class="flex items-center gap-2">
                    <x-tables::bulk-actions
                        x-show="selectedRecords.length"
                        x-cloak
                        :actions="$getBulkActions()"
                    />
                </div>

                <div class="flex w-full items-center justify-end gap-2 md:max-w-md">
                    @if ($isGlobalSearchVisible)
                        <div class="filament-tables-search-container flex flex-1 items-center justify-end">
                            <x-tables::search-input />
                        </div>
                    @endif

                    <x-tables::filters.popover
                        :form="$getFiltersForm()"
                        :indicators-count="count($filterIndicators)"
                        class="shrink-0"
                    />
                </div>
            </div>
        @endif

        <x-tables::table>
            <x-slot name="header">
                @if ($isReordering)
                    <th></th>
                @else
                    @if ($isMasterDetailEnabled)
                        <th class="w-5"></th>
                    @endif

                    @if (count($actions) && in_array($actionsPosition, [ActionsPosition::BeforeCells, ActionsPosition::BeforeColumns]))
                        <th class="w-5">{{ $actionsColumnLabel }}</th>
                    @endif

                    @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells)
                        <x-tables::checkbox.cell>
                            <x-tables::checkbox :label="__('tables::table.fields.bulk_select_page.label')" x-on:click="toggleSelectRecordsOnPage" />
                        </x-tables::checkbox.cell>
                    @endif
                @endif

                @foreach ($columns as $column)
                    <x-tables::header-cell
                        :extra-attributes="$column->getExtraHeaderAttributes()"
                        :is-sort-column="$getSortColumn() === $column->getName()"
                        :name="$column->getName()"
                        :alignment="$column->getAlignment()"
                        :sortable="$column->isSortable() && (! $isReordering)"
                        :sort-direction="$getSortDirection()"
                        class="filament-table-header-cell-{{ \Illuminate\Support\Str::of($column->getName())->camel()->kebab() }}"
                    >
                        {{ $column->getLabel() }}
                    </x-tables::header-cell>
                @endforeach

                @if (! $isReordering)
                    @if (count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]))
                        <th class="w-5 text-right">{{ $actionsColumnLabel }}</th>
                    @endif

                    @if ($isMasterDetailEnabled)
                        <th class="w-5"></th>
                    @endif

                    @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
                        <x-tables::checkbox.cell>
                            <x-tables::checkbox :label="__('tables::table.fields.bulk_select_page.label')" x-on:click="toggleSelectRecordsOnPage" />
                        </x-tables::checkbox.cell>
                    @endif
                @endif
            </x-slot>

            @if ($isColumnSearchVisible)
                <x-tables::row>
                    @if ($isReordering)
                        <td></td>
                    @else
                        @if ($isMasterDetailEnabled)
                            <td></td>
                        @endif

                        @if (count($actions) && in_array($actionsPosition, [ActionsPosition::BeforeCells, ActionsPosition::BeforeColumns]))
                            <td></td>
                        @endif

                        @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells)
                            <td></td>
                        @endif
                    @endif

                    @foreach ($columns as $column)
                        <x-tables::cell class="px-4 py-1">
                            @if ($column->isIndividuallySearchable())
                                <x-tables::search-input wire-model="tableColumnSearchQueries.{{ $column->getName() }}" />
                            @endif
                        </x-tables::cell>
                    @endforeach

                    @if (! $isReordering)
                        @if (count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]))
                            <td></td>
                        @endif

                        @if ($isMasterDetailEnabled)
                            <td></td>
                        @endif

                        @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
                            <td></td>
                        @endif
                    @endif
                </x-tables::row>
            @endif

            @foreach ($records ?? [] as $record)
                @php
                    $recordAction = $getRecordAction($record);
                    $recordKey = $getRecordKey($record);
                    $recordUrl = $getRecordUrl($record);
                    $detailRecordKey = method_exists($this, 'getMasterDetailRecordKey') ? $this->getMasterDetailRecordKey($record) : null;
                    $isDetailExpanded = $detailRecordKey && method_exists($this, 'isMasterDetailRecordExpanded') ? $this->isMasterDetailRecordExpanded($detailRecordKey) : false;
                    $isDetailExpandable = method_exists($this, 'isMasterDetailRecordExpandable') ? $this->isMasterDetailRecordExpandable($record) : false;
                @endphp

                <x-tables::row
                    :record-action="$recordAction"
                    :record-url="$recordUrl"
                    :wire:key="$this->id . '.table.records.' . $recordKey"
                    :wire:sortable.item="$isReordering ? $recordKey : null"
                    :wire:sortable.handle="$isReordering"
                    :striped="$isStriped"
                >
                    <x-tables::reorder.cell :class="\Illuminate\Support\Arr::toCssClasses(['hidden' => ! $isReordering])">
                        @if ($isReordering)
                            <x-tables::reorder.handle />
                        @endif
                    </x-tables::reorder.cell>

                    @if ($isMasterDetailEnabled)
                        <x-tables::actions.cell :class="\Illuminate\Support\Arr::toCssClasses(['hidden' => $isReordering])">
                            @if ($isDetailExpandable && $detailRecordKey)
                                <button
                                    type="button"
                                    wire:click="toggleMasterDetailRecord('{{ $detailRecordKey }}')"
                                    class="inline-flex items-center justify-center text-gray-500 hover:text-primary-600"
                                >
                                    <x-dynamic-component :component="$this->getMasterDetailExpandIcon($record)" class="h-5 w-5" />
                                </button>
                            @endif
                        </x-tables::actions.cell>
                    @endif

                    @if (count($actions) && $actionsPosition === ActionsPosition::BeforeCells)
                        <x-tables::actions.cell :class="\Illuminate\Support\Arr::toCssClasses(['hidden' => $isReordering])">
                            <x-tables::actions :actions="$actions" :record="$record" />
                        </x-tables::actions.cell>
                    @endif

                    @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells)
                        @if ($isRecordSelectable($record))
                            <x-tables::checkbox.cell :class="\Illuminate\Support\Arr::toCssClasses(['hidden' => $isReordering])">
                                <x-tables::checkbox
                                    x-model="selectedRecords"
                                    :value="$recordKey"
                                    :label="__('tables::table.fields.bulk_select_record.label', ['key' => $recordKey])"
                                    class="filament-tables-record-checkbox"
                                />
                            </x-tables::checkbox.cell>
                        @else
                            <x-tables::cell />
                        @endif
                    @endif

                    @foreach ($columns as $column)
                        @php
                            $column->record($record);
                            $column->rowLoop($loop);
                        @endphp
                        <x-tables::cell
                            wire:key="{{ $this->id }}.table.record.{{ $recordKey }}.column.{{ $column->getName() }}"
                            class="filament-table-cell-{{ \Illuminate\Support\Str::of($column->getName())->camel()->kebab() }}"
                            :attributes="\Filament\Support\prepare_inherited_attributes($column->getExtraCellAttributeBag())"
                        >
                            <x-tables::columns.column
                                :column="$column"
                                :record="$record"
                                :record-action="$recordAction"
                                :record-key="$recordKey"
                                :record-url="$recordUrl"
                                :is-click-disabled="$column->isClickDisabled() || $isReordering"
                            />
                        </x-tables::cell>
                    @endforeach

                    @if (count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]))
                        <x-tables::actions.cell :class="\Illuminate\Support\Arr::toCssClasses(['hidden' => $isReordering])">
                            <x-tables::actions :actions="$actions" :record="$record" />
                        </x-tables::actions.cell>
                    @endif

                    @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
                        @if ($isRecordSelectable($record))
                            <x-tables::checkbox.cell :class="\Illuminate\Support\Arr::toCssClasses(['hidden' => $isReordering])">
                                <x-tables::checkbox
                                    x-model="selectedRecords"
                                    :value="$recordKey"
                                    :label="__('tables::table.fields.bulk_select_record.label', ['key' => $recordKey])"
                                    class="filament-tables-record-checkbox"
                                />
                            </x-tables::checkbox.cell>
                        @else
                            <x-tables::cell />
                        @endif
                    @endif
                </x-tables::row>

                @if ($isMasterDetailEnabled && $isDetailExpanded)
                    <x-tables::row :wire:key="$this->id . '.table.records.' . $recordKey . '.detail'">
                        <x-tables::cell :colspan="$this->getMasterDetailColumnCount()" class="p-0">
                            <div class="{{ $this->getMasterDetailWrapperClass($record) }}">
                                @php
                                    $masterDetailComponent = $this->getMasterDetailLivewireComponent();
                                    $masterDetailParameters = $this->getMasterDetailParameters($record);
                                @endphp

                                @if (filled($masterDetailComponent))
                                    @livewire($masterDetailComponent, $masterDetailParameters, key($detailRecordKey))
                                @endif
                            </div>
                        </x-tables::cell>
                    </x-tables::row>
                @endif
            @endforeach
        </x-tables::table>

        @if ($records instanceof \Illuminate\Contracts\Pagination\Paginator &&
            (! $records instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator) || $records->total()))
            <div
                @class([
                    'filament-tables-pagination-container border-t p-2',
                    'dark:border-gray-700' => config('tables.dark_mode'),
                ])
            >
                <x-tables::pagination
                    :paginator="$records"
                    :records-per-page-select-options="$getRecordsPerPageSelectOptions()"
                />
            </div>
        @endif
    </x-tables::container>

    <form wire:submit.prevent="callMountedTableAction">
        @php
            $action = $getMountedAction();
        @endphp

        <x-tables::modal
            :id="$this->id . '-table-action'"
            :wire:key="$action ? $this->id . '.table.actions.' . $action->getName() . '.modal' : null"
            :visible="filled($action)"
            :width="$action?->getModalWidth()"
            :slide-over="$action?->isModalSlideOver()"
            :close-by-clicking-away="$action?->isModalClosedByClickingAway()"
            display-classes="block"
            x-init="livewire = $wire.__instance"
            x-on:modal-closed.stop="
                if ('mountedTableAction' in livewire?.serverMemo.data) livewire.set('mountedTableAction', null)
                if ('mountedTableActionRecord' in livewire?.serverMemo.data) livewire.set('mountedTableActionRecord', null)
            "
        >
            @if ($action)
                @if ($heading = $action->getModalHeading())
                    <x-slot name="heading">{{ $heading }}</x-slot>
                @endif

                @if ($subheading = $action->getModalSubheading())
                    <x-slot name="subheading">{{ $subheading }}</x-slot>
                @endif

                {{ $action->getModalContent() }}

                @if ($action->hasFormSchema())
                    {{ $getMountedActionForm() }}
                @endif

                {{ $action->getModalFooter() }}

                @if (count($action->getModalActions()))
                    <x-slot name="footer">
                        <x-tables::modal.actions :full-width="$action->isModalCentered()">
                            @foreach ($action->getModalActions() as $modalAction)
                                {{ $modalAction }}
                            @endforeach
                        </x-tables::modal.actions>
                    </x-slot>
                @endif
            @endif
        </x-tables::modal>
    </form>

    <form wire:submit.prevent="callMountedTableBulkAction">
        @php
            $action = $getMountedBulkAction();
        @endphp

        <x-tables::modal
            :id="$this->id . '-table-bulk-action'"
            :wire:key="$action ? $this->id . '.table.bulk-actions.' . $action->getName() . '.modal' : null"
            :visible="filled($action)"
            :width="$action?->getModalWidth()"
            :slide-over="$action?->isModalSlideOver()"
            :close-by-clicking-away="$action?->isModalClosedByClickingAway()"
            display-classes="block"
            x-init="livewire = $wire.__instance"
            x-on:modal-closed.stop="if ('mountedTableBulkAction' in livewire?.serverMemo.data) livewire.set('mountedTableBulkAction', null)"
        >
            @if ($action)
                @if ($heading = $action->getModalHeading())
                    <x-slot name="heading">{{ $heading }}</x-slot>
                @endif

                @if ($subheading = $action->getModalSubheading())
                    <x-slot name="subheading">{{ $subheading }}</x-slot>
                @endif

                {{ $action->getModalContent() }}

                @if ($action->hasFormSchema())
                    {{ $getMountedBulkActionForm() }}
                @endif

                {{ $action->getModalFooter() }}

                @if (count($action->getModalActions()))
                    <x-slot name="footer">
                        <x-tables::modal.actions :full-width="$action->isModalCentered()">
                            @foreach ($action->getModalActions() as $modalAction)
                                {{ $modalAction }}
                            @endforeach
                        </x-tables::modal.actions>
                    </x-slot>
                @endif
            @endif
        </x-tables::modal>
    </form>

    @if (! $this instanceof \Filament\Tables\Contracts\RendersFormComponentActionModal)
        {{ $this->modal }}
    @endif
</div>

