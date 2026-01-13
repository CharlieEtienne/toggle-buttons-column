<?php

namespace CharlieEtienne\ToggleButtonsColumn;

use Closure;
use BackedEnum;
use Filament\Actions\Concerns as ActionsConcerns;
use Filament\Forms\Components\Concerns as FormsConcerns;
use Filament\Forms\View\FormsIconAlias;
use Filament\Schemas\Components\StateCasts\BooleanStateCast;
use Filament\Support\Components\Contracts\HasEmbeddedView;
use Filament\Support\Enums\Size;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Filament\Support\View\Concerns as ViewConcerns;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\Concerns as ColumnConcerns;
use Filament\Tables\Columns\Contracts\Editable;
use Illuminate\Support\Js;
use Illuminate\View\ComponentAttributeBag;

class ToggleButtonsColumn extends Column implements Editable, HasEmbeddedView
{
    use ActionsConcerns\HasSize;
    use ViewConcerns\CanGenerateButtonHtml;
    use ColumnConcerns\CanBeValidated;
    use ColumnConcerns\CanUpdateState;
    use FormsConcerns\CanDisableOptions;
    use FormsConcerns\HasColors;
    use FormsConcerns\HasEnum;
    use FormsConcerns\HasExtraInputAttributes;
    use FormsConcerns\HasIcons;
    use FormsConcerns\HasNestedRecursiveValidationRules;
    use FormsConcerns\HasOptions {
        getOptions as getBaseOptions;
    }

    protected bool|Closure $isMultiple = false;

    protected bool|Closure $isGrouped = false;

    protected bool|Closure $areButtonLabelsHidden = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disabledClick();
        $this->defaultSize(Size::ExtraSmall);
    }

    public function grouped(bool|Closure $condition = true): static
    {
        $this->isGrouped = $condition;

        return $this;
    }

    public function isGrouped(): bool
    {
        return (bool) $this->evaluate($this->isGrouped);
    }

    public function boolean(?string $trueLabel = null, ?string $falseLabel = null): static
    {
        $this->options([
            1 => $trueLabel ?? __('filament-forms::components.toggle_buttons.boolean.true'),
            0 => $falseLabel ?? __('filament-forms::components.toggle_buttons.boolean.false'),
        ]);

        $this->colors([
            1 => 'success',
            0 => 'danger',
        ]);

        $this->icons([
            1 => FilamentIcon::resolve(FormsIconAlias::COMPONENTS_TOGGLE_BUTTONS_BOOLEAN_TRUE) ?? Heroicon::Check,
            0 => FilamentIcon::resolve(FormsIconAlias::COMPONENTS_TOGGLE_BUTTONS_BOOLEAN_FALSE) ?? Heroicon::XMark,
        ]);

        $this->stateCast(app(BooleanStateCast::class, ['isStoredAsInt' => true]));

        return $this;
    }

    public function hiddenButtonLabels(bool|Closure $condition = true): static
    {
        $this->areButtonLabelsHidden = $condition;

        return $this;
    }

    public function areButtonLabelsHidden(): bool
    {
        return (bool) $this->evaluate($this->areButtonLabelsHidden);
    }

    public function multiple(bool|Closure $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function isMultiple(): bool
    {
        return (bool) $this->evaluate($this->isMultiple);
    }

    public function getDefaultState(): mixed
    {
        $state = parent::getDefaultState();

        if (is_bool($state)) {
            return $state ? 1 : 0;
        }

        return $state;
    }

    public function toEmbeddedHtml(): string
    {
        $id = $this->getName();
        $isDisabled = $this->isDisabled();
        $isGrouped = $this->isGrouped();
        $isMultiple = $this->isMultiple();
        $state = $this->getState();
        $size = $this->getSize();
        $recordKey = $this->getRecordKey();
        $areButtonLabelsHidden = $this->areButtonLabelsHidden();
        $extraInputAttributeBag = $this->getExtraInputAttributeBag()->class(['fi-fo-toggle-buttons-input']);
        $options = $this->getOptions();
        $attributes = $this->getExtraAttributeBag();
        $attributes = $attributes->class([
            'fi-fo-toggle-buttons',
            'fi-inline' => ! $isGrouped,
            'fi-btn-group' => $isGrouped,
        ]);

        ob_start(); ?>

        <div
            class="fi-fo-toggle-buttons-wrp"
            wire:ignore.self
            x-load="true"
            x-load-src="<?= FilamentAsset::getAlpineComponentSrc('columns/toggle', 'filament/tables') ?>"
            x-data="{
                error: undefined,
                isLoading: false,
                state: <?= Js::from($state) ?>,
                init() {
                    Livewire.hook(
                        'commit',
                        ({ component, commit, succeed, fail, respond }) => {
                            succeed(({ snapshot, effect }) => {
                                this.$nextTick(() => {
                                    if (this.isLoading) {
                                        return
                                    }
                                    if (
                                        component.id !==
                                        this.$root.closest('[wire\\:id]')?.attributes[
                                            'wire:id'
                                        ].value
                                    ) {
                                        return
                                    }
                                    const serverState = this.getServerState()
                                    if (
                                        serverState === undefined ||
                                        Alpine.raw(this.state) === serverState
                                    ) {
                                        return
                                    }
                                    this.state = serverState
                                })
                            })
                        },
                    )
                    this.$watch('state', async () => {
                        const serverState = this.getServerState()
                        console.log(serverState)
                        if (
                            serverState === undefined ||
                            this.getNormalizedState() === serverState
                        ) {
                            return
                        }
                        this.isLoading = true
                        const response = await this.$wire.updateTableColumnState(
                            <?= Js::from($id) ?>,
                            <?= Js::from($recordKey) ?>,
                            this.state,
                        )
                        this.error = response?.error ?? undefined
                        if (!this.error && this.$refs.serverState) {
                            this.$refs.serverState.value = this.getNormalizedState()
                        }
                        this.isLoading = false
                    })
                },
                getServerState() {
                    if (!this.$refs.serverState) {
                        return undefined
                    }
                    return [null, undefined].includes(this.$refs.serverState.value)
                        ? ''
                        : this.$refs.serverState.value.replaceAll(
                              '\\' + String.fromCharCode(34),
                              String.fromCharCode(34),
                          )
                },
                getNormalizedState() {
                    const state = Alpine.raw(this.state)
                    if ([null, undefined].includes(state)) {
                        return ''
                    }
                    return state
                }
            }"
        >
            <input type="hidden" value="<?= str(($state instanceof BackedEnum) ? $state->value : $state)->replace('"', '\\"') ?>" x-ref="serverState" />

            <div
                <?= $attributes->toHtml(); ?>
            >
                <?php foreach ($options as $value => $label) {
                    $inputId = "{$recordKey}-{$id}-{$value}";
                    $shouldOptionBeDisabled = $isDisabled || $this->isOptionDisabled($value, $label);
                    $color = $this->getColor($value);
                    $icon = $this->getIcon($value);
                    ?>
                    <?php if (! $isGrouped) { ?>
                        <div class="fi-fo-toggle-buttons-btn-ctn">
                    <?php } ?>
                        <input
                            <?= $shouldOptionBeDisabled ? 'disabled' : '' ?>
                            id="<?= $inputId ?>"
                            <?php if (! $isMultiple) { ?>
                                name="<?= $recordKey ?>-<?= $id ?>"
                            <?php } ?>
                            type="<?= $isMultiple ? 'checkbox' : 'radio' ?>"
                            value="<?= $value ?>"
                            <?= $extraInputAttributeBag->toHtml() ?>
                            x-model="state"
                        />

                        <?= $this->generateButtonHtml(
                            attributes: (new ComponentAttributeBag)->merge(['for' => $inputId], escape: false)->class(['fi-fo-toggle-buttons-btn']),
                            color: $color,
                            icon: $icon,
                            isDisabled: $shouldOptionBeDisabled,
                            isLabelSrOnly: $areButtonLabelsHidden,
                            label: $label,
                            size: $size,
                            tag: 'label',
                        ) ?>
                    <?php if (! $isGrouped) { ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>

        <?php return ob_get_clean();
    }
}
