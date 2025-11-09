<?php

namespace Kapital\Filament\FormComponents\Components;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select as BaseSelect;
use Illuminate\View\ComponentAttributeBag;

class Select extends BaseSelect
{
    /**
     * Maximum number of selectable items (for multiple select)
     */
    protected int | Closure | null $maxSelectable = null;

    /**
     * Minimum number of selectable items (for multiple select)
     */
    protected int | Closure | null $minSelectable = null;

    /**
     * Whether to show "Select All" button
     */
    protected bool | Closure $hasSelectAllOption = false;

    /**
     * Whether to show "Deselect All" button
     */
    protected bool | Closure $hasDeselectAllOption = false;

    /**
     * Set maximum number of selectable items
     *
     * @param int|Closure $count
     * @return static
     */
    public function maxSelectable(int | Closure $count): static
    {
        $this->maxSelectable = $count;

        return $this;
    }

    /**
     * Get maximum number of selectable items
     *
     * @return int|null
     */
    public function getMaxSelectable(): ?int
    {
        return $this->evaluate($this->maxSelectable);
    }

    /**
     * Set minimum number of selectable items
     *
     * @param int|Closure $count
     * @return static
     */
    public function minSelectable(int | Closure $count): static
    {
        $this->minSelectable = $count;

        return $this;
    }

    /**
     * Get minimum number of selectable items
     *
     * @return int|null
     */
    public function getMinSelectable(): ?int
    {
        return $this->evaluate($this->minSelectable);
    }

    /**
     * Enable "Select All" option button
     *
     * @param bool|Closure $condition
     * @return static
     */
    public function selectAllOption(bool | Closure $condition = true): static
    {
        $this->hasSelectAllOption = $condition;

        // Set up the suffix action when enabled
        if ($this->evaluate($condition)) {
            $this->suffixAction(
                Action::make('selectAll')
                    ->label('Tümünü Seç')
                    ->icon('heroicon-o-check')
                    ->action(function () {
                        $currentState = $this->getState() ?? [];
                        $options = $this->getOptions();
                        $allValues = array_keys($options);

                        // Keep currently selected disabled options
                        $currentlyDisabledValues = collect($currentState)->filter(function ($value) use ($options) {
                            $label = $options[$value] ?? $value;
                            return $this->isOptionDisabled($value, $label);
                        });

                        // Get all selectable (non-disabled) options
                        $selectableValues = collect($allValues)->filter(function ($value) use ($options) {
                            $label = $options[$value] ?? $value;
                            return !$this->isOptionDisabled($value, $label);
                        });

                        // Merge: keep disabled + add all selectable
                        $finalValues = $currentlyDisabledValues->merge($selectableValues)->unique()->values()->toArray();

                        $this->state($finalValues);
                    })
            );
        }

        return $this;
    }

    /**
     * Check if "Select All" option is enabled
     *
     * @return bool
     */
    public function hasSelectAllOption(): bool
    {
        return $this->evaluate($this->hasSelectAllOption);
    }

    /**
     * Enable "Deselect All" option button
     *
     * @param bool|Closure $condition
     * @return static
     */
    public function deselectAllOption(bool | Closure $condition = true): static
    {
        $this->hasDeselectAllOption = $condition;

        // Set up the hint action when enabled
        if ($this->evaluate($condition)) {
            $this->hintAction(
                Action::make('deselectAll')
                    ->label('Tümünü Temizle')
                    ->icon('heroicon-o-x')
                    ->color('danger')
                    ->action(function () {
                        $currentState = $this->getState() ?? [];
                        $options = $this->getOptions();

                        // Keep only disabled options
                        $disabledValues = collect($currentState)->filter(function ($value) use ($options) {
                            $label = $options[$value] ?? $value;
                            return $this->isOptionDisabled($value, $label);
                        })->values()->toArray();

                        $this->state($disabledValues);
                    })
            );
        }

        return $this;
    }

    /**
     * Check if "Deselect All" option is enabled
     *
     * @return bool
     */
    public function hasDeselectAllOption(): bool
    {
        return $this->evaluate($this->hasDeselectAllOption);
    }

    /**
     * Transform options for JavaScript (Choices.js)
     * This method fixes the issue where disableOptionWhen doesn't work with multiple select
     *
     * @param array $options
     * @return array
     */
    protected function transformOptionsForJs(array $options): array
    {
        return collect($options)
            ->map(fn ($label, $value): array => [
                'label' => $label,
                'value' => strval($value),
                'disabled' => $this->isOptionDisabled($value, $label),
            ])
            ->values()
            ->all();
    }

    /**
     * Get validation rules for the component
     * Adds max and min validation rules based on maxSelectable and minSelectable
     *
     * @return array
     */
    public function getValidationRules(): array
    {
        $rules = parent::getValidationRules();

        if ($this->isMultiple()) {
            $maxSelectable = $this->getMaxSelectable();
            $minSelectable = $this->getMinSelectable();

            if ($maxSelectable !== null) {
                $rules[] = "max:{$maxSelectable}";
            }

            if ($minSelectable !== null) {
                $rules[] = "min:{$minSelectable}";
            }
        }

        return $rules;
    }

    /**
     * Get validation attributes for better error messages
     *
     * @return array
     */
    public function getValidationAttributes(): array
    {
        $attributes = parent::getValidationAttributes();

        if ($this->isMultiple()) {
            $maxSelectable = $this->getMaxSelectable();
            $minSelectable = $this->getMinSelectable();

            if ($maxSelectable !== null) {
                $attributes['max'] = $maxSelectable;
            }

            if ($minSelectable !== null) {
                $attributes['min'] = $minSelectable;
            }
        }

        return $attributes;
    }


    /**
     * Get extra Alpine.js attributes for the component
     * This allows us to pass maxSelectable to Choices.js
     *
     * @return ComponentAttributeBag
     */
    public function getExtraAlpineAttributeBag(): ComponentAttributeBag
    {
        $attributes = parent::getExtraAlpineAttributeBag();

        if ($this->isMultiple()) {
            $maxSelectable = $this->getMaxSelectable();

            if ($maxSelectable !== null) {
                $attributes = $attributes->merge(['max-item-count' => $maxSelectable]);
            }
        }

        return $attributes;
    }
}
