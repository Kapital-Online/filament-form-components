@php
    use Filament\Support\Facades\FilamentView;
    use Filament\Tables\Enums\ActionsPosition;
    use Filament\Tables\Enums\FiltersLayout;
    use Filament\Tables\Enums\RecordCheckboxPosition;
    use Illuminate\Support\Str;

    $actions = $getActions();
    $flatActionsCount = count($getFlatActions());
    $actionsAlignment = $getActionsAlignment();
    $actionsPosition = $getActionsPosition();
    $actionsColumnLabel = $getActionsColumnLabel();
    $columns = $getColumns();
    // Revizyon: Filament 3 Indicator::getColor() için düz Indicator listesi (eski nested map hata veriyordu)
    $filterIndicators = $getFilterIndicators();

    $header = $getHeader();
    $headerActions = array_filter(
        $getHeaderActions(),
        fn (\Filament\Tables\Actions\Action | \Filament\Tables\Actions\BulkAction | \Filament\Tables\Actions\ActionGroup $action): bool => $action->isVisible(),
    );
    $headerActionsPosition = $getHeaderActionsPosition();
    $heading = $getHeading();
    $bulkActions = array_filter(
        $getBulkActions(),
        fn (\Filament\Tables\Actions\BulkAction | \Filament\Tables\Actions\ActionGroup $action): bool => $action->isVisible(),
    );
    $description = $getDescription();
    $isReorderable = $isReorderable();
    $isReordering = $isReordering();
    $isColumnSearchVisible = $isSearchableByColumn();
    $isGlobalSearchVisible = $isSearchable();
    $isSearchOnBlur = $isSearchOnBlur();
    $isSelectionEnabled = $isSelectionEnabled();
    $selectsCurrentPageOnly = $selectsCurrentPageOnly();
    $recordCheckboxPosition = $getRecordCheckboxPosition();
    $isStriped = $isStriped();
    $isLoaded = $isLoaded();
    $hasFilters = $isFilterable();
    $filtersLayout = $getFiltersLayout();
    $filtersTriggerAction = $getFiltersTriggerAction();
    $hasFiltersDialog = $hasFilters && in_array($filtersLayout, [FiltersLayout::Dropdown, FiltersLayout::Modal]);
    $hasFiltersAboveContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::AboveContent, FiltersLayout::AboveContentCollapsible]);
    $records = $isLoaded ? $getRecords() : null;
    $searchDebounce = $getSearchDebounce();
    $allSelectableRecordsCount = ($isSelectionEnabled && $isLoaded) ? $getAllSelectableRecordsCount() : null;
    $page = $this->getTablePage();

    $isMasterDetailEnabled = method_exists($this, 'hasMasterDetailEnabled') && $this->hasMasterDetailEnabled();

    $columnsCount = count($columns);

    if (count($actions) && (! $isReordering)) {
        $columnsCount++;
    }

    if ($isSelectionEnabled || $isReordering) {
        $columnsCount++;
    }

    if ($isMasterDetailEnabled) {
        $columnsCount++;
    }
@endphp

<div
    @if (! $isLoaded)
        wire:init="loadTable"
    @endif
    @if (FilamentView::hasSpaMode())
        x-load="visible"
    @else
        x-load
    @endif
    x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('table', 'filament/tables') }}"
    x-data="table"
    @class([
        'fi-ta',
        'animate-pulse' => $records === null,
    ])
>
    <x-filament-tables::container>
        <div
            @if (! ($heading || $description || count($headerActions) || $isReorderable || $isGlobalSearchVisible || $hasFilters || count($filterIndicators)))
                x-cloak
            @endif
            class="fi-ta-header-ctn divide-y divide-gray-200 dark:divide-white/10"
        >
            @if ($header)
                {{ $header }}
            @elseif (($heading || $description || $headerActions) && (! $isReordering))
                <x-filament-tables::header
                    :actions="$isReordering ? [] : $headerActions"
                    :actions-position="$headerActionsPosition"
                    :description="$description"
                    :heading="$heading"
                />
            @endif

            @if ($hasFiltersAboveContent)
                <div class="fi-ta-filters-above-content-ctn grid px-4 py-4 sm:px-6">
                    <x-filament-tables::filters
                        :apply-action="$getFiltersApplyAction()"
                        :form="$getFiltersForm()"
                    />
                </div>
            @endif

            <div
                class="fi-ta-header-toolbar flex items-center justify-between gap-x-4 px-4 py-3 sm:px-6"
            >
                <div class="flex shrink-0 items-center gap-x-4">
                    @if ($isReorderable)
                        <span x-show="! selectedRecords.length">
                            {{ $getReorderRecordsTriggerAction($isReordering) }}
                        </span>
                    @endif

                    @if ((! $isReordering) && count($bulkActions))
                        <x-filament-tables::actions
                            :actions="$bulkActions"
                            x-cloak
                            x-show="selectedRecords.length"
                        />
                    @endif
                </div>

                @if ($isGlobalSearchVisible || $hasFiltersDialog)
                    <div class="ms-auto flex items-center gap-x-4">
                        @if ($isGlobalSearchVisible)
                            <x-filament-tables::search-field
                                :debounce="$searchDebounce"
                                :on-blur="$isSearchOnBlur"
                                :placeholder="$getSearchPlaceholder()"
                            />
                        @endif

                        @if ($hasFiltersDialog)
                            <x-filament-tables::filters.dialog
                                :active-filters-count="$activeFiltersCount"
                                :apply-action="$getFiltersApplyAction()"
                                :form="$getFiltersForm()"
                                :layout="$filtersLayout"
                                :max-height="$getFiltersFormMaxHeight()"
                                :trigger-action="$filtersTriggerAction"
                                :width="$getFiltersFormWidth()"
                            />
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($isSelectionEnabled && $isLoaded)
            <x-filament-tables::selection.indicator
                :all-selectable-records-count="$allSelectableRecordsCount"
                :colspan="$columnsCount"
                :page="$page"
                :select-current-page-only="$selectsCurrentPageOnly"
                x-bind:hidden="! selectedRecords.length"
                x-show="selectedRecords.length"
            />
        @endif

        @if (count($filterIndicators))
            <x-filament-tables::filters.indicators
                :indicators="$filterIndicators"
            />
        @endif

        <div class="fi-ta-content relative divide-y divide-gray-200 overflow-x-auto dark:divide-white/10 dark:border-t-white/10">
            @if (($records !== null) && count($records))
                <x-filament-tables::table
                    :reorderable="$isReorderable"
                    :reorder-animation-duration="$getReorderAnimationDuration()"
                >
                    <x-slot name="header">
                        @if ($isReordering)
                            <th></th>
                        @else
                            @if ($isMasterDetailEnabled)
                                <th class="w-5"></th>
                            @endif

                            @if (count($actions) && $actionsPosition === ActionsPosition::BeforeCells)
                                <th
                                    aria-label="{{ trans_choice('filament-tables::table.columns.actions.label', $flatActionsCount) }}"
                                    class="fi-ta-actions-header-cell w-1"
                                ></th>
                            @endif

                            @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::BeforeCells)
                                <x-filament-tables::selection.cell tag="th">
                                    <x-filament-tables::selection.checkbox
                                        :wire:key="$this->getId() . '.table.bulk-select-page.checkbox.' . Str::random()"
                                        :label="__('filament-tables::table.fields.bulk_select_page.label')"
                                        x-bind:checked="
                                            const recordsOnPage = getRecordsOnPage()

                                            if (recordsOnPage.length && areRecordsSelected(recordsOnPage)) {
                                                $el.checked = true

                                                return 'checked'
                                            }

                                            $el.checked = false

                                            return null
                                        "
                                        x-on:click="toggleSelectRecordsOnPage"
                                        class="fi-ta-page-checkbox"
                                    />
                                </x-filament-tables::selection.cell>
                            @endif
                        @endif

                        @foreach ($columns as $column)
                            <x-filament-tables::header-cell
                                :actively-sorted="$getSortColumn() === $column->getName()"
                                :alignment="$column->getAlignment()"
                                :name="$column->getName()"
                                :sortable="$column->isSortable() && (! $isReordering)"
                                :sort-direction="$getSortDirection()"
                                :wrap="$column->isHeaderWrapped()"
                                class="fi-table-header-cell-{{ Str::of($column->getName())->camel()->kebab() }}"
                            >
                                {{ $column->getLabel() }}
                            </x-filament-tables::header-cell>
                        @endforeach

                        @if (! $isReordering)
                            @if (count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]))
                                <th
                                    aria-label="{{ trans_choice('filament-tables::table.columns.actions.label', $flatActionsCount) }}"
                                    class="fi-ta-actions-header-cell w-1"
                                ></th>
                            @endif

                            @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
                                <x-filament-tables::selection.cell tag="th">
                                    <x-filament-tables::selection.checkbox
                                        :wire:key="$this->getId() . '.table.bulk-select-page.checkbox.' . Str::random()"
                                        :label="__('filament-tables::table.fields.bulk_select_page.label')"
                                        x-on:click="toggleSelectRecordsOnPage"
                                        class="fi-ta-page-checkbox"
                                    />
                                </x-filament-tables::selection.cell>
                            @endif
                        @endif
                    </x-slot>

                    @if ($isColumnSearchVisible)
                        <x-filament-tables::row>
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
                                <x-filament-tables::cell class="{{ $column->isIndividuallySearchable() ? 'min-w-48 px-3 py-2' : '' }}">
                                    @if ($column->isIndividuallySearchable())
                                        <x-filament-tables::search-field
                                            :debounce="$searchDebounce"
                                            :on-blur="$isSearchOnBlur"
                                            wire-model="tableColumnSearches.{{ $column->getName() }}"
                                        />
                                    @endif
                                </x-filament-tables::cell>
                            @endforeach

                            @if (! $isReordering)
                                @if (count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]))
                                    <td></td>
                                @endif

                                @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells)
                                    <td></td>
                                @endif
                            @endif
                        </x-filament-tables::row>
                    @endif

                    @foreach ($records as $record)
                        @php
                            $recordAction = $getRecordAction($record);
                            $recordKey = $getRecordKey($record);
                            $recordUrl = $getRecordUrl($record);
                            $openRecordUrlInNewTab = $shouldOpenRecordUrlInNewTab($record);

                            $detailRecordKey = method_exists($this, 'getMasterDetailRecordKey') ? $this->getMasterDetailRecordKey($record) : null;
                            $isDetailExpanded = $detailRecordKey && method_exists($this, 'isMasterDetailRecordExpanded') ? $this->isMasterDetailRecordExpanded($detailRecordKey) : false;
                            $isDetailExpandable = method_exists($this, 'isMasterDetailRecordExpandable') ? $this->isMasterDetailRecordExpandable($record) : false;
                        @endphp

                        <x-filament-tables::row
                            :record-action="$recordAction"
                            :record-url="$recordUrl"
                            :striped="$isStriped"
                            :wire:key="$this->getId() . '.table.records.' . $recordKey"
                            :x-sortable-handle="$isReordering"
                            :x-sortable-item="$isReordering ? $recordKey : null"
                        >
                            @if ($isReordering)
                                <x-filament-tables::reorder.cell>
                                    <x-filament-tables::reorder.handle />
                                </x-filament-tables::reorder.cell>
                            @endif

                            @if ($isMasterDetailEnabled && (! $isReordering))
                                <x-filament-tables::actions.cell>
                                    @if ($isDetailExpandable && $detailRecordKey)
                                        <button
                                            type="button"
                                            wire:click="toggleMasterDetailRecord('{{ $detailRecordKey }}')"
                                            class="inline-flex items-center justify-center text-gray-500 hover:text-primary-600"
                                        >
                                            <x-dynamic-component :component="$this->getMasterDetailExpandIcon($record)" class="h-5 w-5" />
                                        </button>
                                    @endif
                                </x-filament-tables::actions.cell>
                            @endif

                            @if (count($actions) && $actionsPosition === ActionsPosition::BeforeCells && (! $isReordering))
                                <x-filament-tables::actions.cell>
                                    <x-filament-tables::actions
                                        :actions="$actions"
                                        :alignment="$actionsAlignment"
                                        :record="$record"
                                    />
                                </x-filament-tables::actions.cell>
                            @endif

                            @if ($isSelectionEnabled && ($recordCheckboxPosition === RecordCheckboxPosition::BeforeCells) && (! $isReordering))
                                <x-filament-tables::selection.cell>
                                    @if ($isRecordSelectable($record))
                                        <x-filament-tables::selection.checkbox
                                            :label="__('filament-tables::table.fields.bulk_select_record.label', ['key' => $recordKey])"
                                            :value="$recordKey"
                                            x-model="selectedRecords"
                                            class="fi-ta-record-checkbox"
                                        />
                                    @endif
                                </x-filament-tables::selection.cell>
                            @endif

                            @foreach ($columns as $column)
                                @php
                                    $column->record($record);
                                    $column->rowLoop($loop);
                                @endphp

                                <x-filament-tables::cell
                                    :wire:key="$this->getId() . '.table.record.' . $recordKey . '.column.' . $column->getName()"
                                    :attributes="
                                        \Filament\Support\prepare_inherited_attributes($column->getExtraCellAttributeBag())
                                            ->class(['fi-table-cell-' . Str::of($column->getName())->camel()->kebab()])
                                    "
                                >
                                    <x-filament-tables::columns.column
                                        :column="$column"
                                        :is-click-disabled="$column->isClickDisabled() || $isReordering"
                                        :record="$record"
                                        :record-action="$recordAction"
                                        :record-key="$recordKey"
                                        :record-url="$recordUrl"
                                        :should-open-record-url-in-new-tab="$openRecordUrlInNewTab"
                                    />
                                </x-filament-tables::cell>
                            @endforeach

                            @if (count($actions) && in_array($actionsPosition, [ActionsPosition::AfterColumns, ActionsPosition::AfterCells]) && (! $isReordering))
                                <x-filament-tables::actions.cell>
                                    <x-filament-tables::actions
                                        :actions="$actions"
                                        :alignment="$actionsAlignment"
                                        :record="$record"
                                    />
                                </x-filament-tables::actions.cell>
                            @endif

                            @if ($isSelectionEnabled && $recordCheckboxPosition === RecordCheckboxPosition::AfterCells && (! $isReordering))
                                <x-filament-tables::selection.cell>
                                    @if ($isRecordSelectable($record))
                                        <x-filament-tables::selection.checkbox
                                            :label="__('filament-tables::table.fields.bulk_select_record.label', ['key' => $recordKey])"
                                            :value="$recordKey"
                                            x-model="selectedRecords"
                                            class="fi-ta-record-checkbox"
                                        />
                                    @endif
                                </x-filament-tables::selection.cell>
                            @endif
                        </x-filament-tables::row>

                        @if ($isMasterDetailEnabled && $isDetailExpanded)
                            <x-filament-tables::row :wire:key="$this->getId() . '.table.records.' . $recordKey . '.detail'">
                                <x-filament-tables::cell :colspan="$this->getMasterDetailColumnCount()" class="p-0">
                                    <div class="{{ $this->getMasterDetailWrapperClass($record) }}">
                                        @php
                                            $masterDetailComponent = $this->getMasterDetailLivewireComponent();
                                            $masterDetailParameters = $this->getMasterDetailParameters($record);
                                        @endphp

                                        @if (filled($masterDetailComponent))
                                            @livewire($masterDetailComponent, $masterDetailParameters, key($detailRecordKey))
                                        @endif
                                    </div>
                                </x-filament-tables::cell>
                            </x-filament-tables::row>
                        @endif
                    @endforeach
                </x-filament-tables::table>
            @elseif ($records === null)
                <div class="flex h-32 items-center justify-center">
                    <x-filament::loading-indicator class="h-8 w-8" />
                </div>
            @else
                <x-filament-tables::empty-state
                    :actions="$getEmptyStateActions()"
                    :description="$getEmptyStateDescription()"
                    :heading="$getEmptyStateHeading()"
                    :icon="$getEmptyStateIcon()"
                />
            @endif
        </div>

        @if ((($records instanceof \Illuminate\Contracts\Pagination\Paginator) || ($records instanceof \Illuminate\Contracts\Pagination\CursorPaginator)) &&
             ((! ($records instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)) || $records->total()))
            <x-filament::pagination
                :extreme-links="$hasExtremePaginationLinks()"
                :page-options="$getPaginationPageOptions()"
                :paginator="$records"
                class="fi-ta-pagination px-3 py-3 sm:px-6"
            />
        @endif
    </x-filament-tables::container>

    <x-filament-actions::modals />
</div>
