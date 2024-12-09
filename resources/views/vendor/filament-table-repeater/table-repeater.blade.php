@php
    use Filament\Forms\Components\Actions\Action;

    $containers = $getChildComponentContainers();

    $addAction = $getAction($getAddActionName());
    $cloneAction = $getAction($getCloneActionName());
    $deleteAction = $getAction($getDeleteActionName());
    $moveDownAction = $getAction($getMoveDownActionName());
    $moveUpAction = $getAction($getMoveUpActionName());
    $reorderAction = $getAction($getReorderActionName());
    $extraItemActions = $getExtraItemActions();

    $isAddable = $isAddable();
    $isCloneable = $isCloneable();
    $isCollapsible = $isCollapsible();
    $isDeletable = $isDeletable();
    $isReorderable = $isReorderable();
    $isReorderableWithButtons = $isReorderableWithButtons();
    $isReorderableWithDragAndDrop = $isReorderableWithDragAndDrop();

    $statePath = $getStatePath();

    $columnLabels = $getColumnLabels();
    $colStyles = $getColStyles();

@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div {{-- x-data="{ state: $wire.entangle('{{ $getStatePath() }}') }"  --}} x-data="{ isCollapsed: @js($isCollapsed()) }"
        x-on:repeater-collapse.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = true)"
        x-on:repeater-expand.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = false)"
        {{ $attributes->merge($getExtraAttributes(), escape: false)->class(['bg-white border border-gray-300 shadow-sm rounded-xl relative dark:bg-gray-800 dark:border-gray-600']) }}>

        <div @class([
            'filament-tables-header',
            'flex items-center h-10 overflow-hidden border-b bg-gray-50 rounded-t-xl',
            'dark:bg-gray-800 dark:border-gray-700',
        ])>

            <div class="flex-1"></div>
            @if ($isCollapsible)
                <div>
                    <button x-on:click="isCollapsed = !isCollapsed" type="button" @class([
                        'flex items-center justify-center flex-none w-10 h-10 text-gray-400 transition hover:text-gray-300',
                        'dark:text-gray-400 dark:hover:text-gray-500',
                    ])>
                        <x-heroicon-s-minus-small class="w-4 h-4" x-show="! isCollapsed" />

                        <span class="sr-only" x-show="! isCollapsed">
                            {{ __('forms::components.repeater.buttons.collapse_item.label') }}
                        </span>

                        <x-heroicon-s-plus-small class="w-4 h-4" x-show="isCollapsed" x-cloak />

                        <span class="sr-only" x-show="isCollapsed" x-cloak>
                            {{ __('forms::components.repeater.buttons.expand_item.label') }}
                        </span>
                    </button>
                </div>
            @endif
        </div>

        <div class="px-4{{ $isAddable ? '' : ' py-2' }}">
            <table class="w-full mx-4 text-left table-auto it-table-repeater rtl:text-right" x-show="! isCollapsed">
                <thead>
                    <tr>

                        @foreach ($columnLabels as $columnLabel)
                            @if ($columnLabel['display'])
                                <th class="p-2 it-table-repeater-cell-label"
                                    @if ($colStyles && isset($colStyles[$columnLabel['component']])) style="{{ $colStyles[$columnLabel['component']] }}" @endif>
                                    <span>
                                        {{ $columnLabel['name'] }}
                                    </span>
                                </th>
                            @else
                                <th style="display: none"></th>
                            @endif
                        @endforeach

                        @if (
                            $isReorderableWithDragAndDrop ||
                                $isReorderableWithButtons ||
                                $isCloneable ||
                                $isDeletable ||
                                filled($extraItemActions))
                            <th></th>
                        @endif
                    </tr>
                </thead>

                <tbody @if ($isReorderable) {{-- :wire:end.stop="'mountFormComponentAction(\'' . $statePath . '\', \'reorder\', { items: $event.target.sortable.toArray() })'" --}}
                       x-sortable @endif>

                    @foreach ($containers as $uuid => $item)
                        @php
                            $visibleExtraItemActions = array_filter(
                                $extraItemActions,
                                fn(Action $action): bool => $action(['item' => $uuid])->isVisible(),
                            );
                        @endphp
                        <tr class="it-table-repeater-row"
                            x-on:repeater-collapse.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = true)"
                            x-on:repeater-expand.window="$event.detail === '{{ $getStatePath() }}' && (isCollapsed = false)"
                            wire:key="{{ $this->getId() }}.{{ $item->getStatePath() }}.{{ $field::class }}.item"
                            x-sortable-item="{{ $uuid }}">

                            @foreach ($item->getComponents() as $component)
                                <td class="px-1 py-2 align-top it-table-repeater-cell"
                                    @if ($component->isHidden() || $component instanceof \Filament\Forms\Components\Hidden) style="display:none" @endif
                                    @if ($colStyles && isset($colStyles[$component->getName()])) style="{{ $colStyles[$component->getName()] }}" @endif>
                                    {{ $component }}
                                </td>
                            @endforeach

                            @if (
                                $isReorderableWithDragAndDrop ||
                                    $isReorderableWithButtons ||
                                    filled($itemLabel) ||
                                    $isCloneable ||
                                    $isDeletable ||
                                    $isCollapsible ||
                                    $visibleExtraItemActions)
                                <td class="flex items-center py-2 gap-x-3 max-w-20">
                                    @if ($isReorderableWithDragAndDrop || $isReorderableWithButtons)
                                        @if ($isReorderableWithDragAndDrop)
                                            <div x-sortable-handle>
                                                {{ $reorderAction }}
                                            </div>
                                        @endif

                                        @if ($isReorderableWithButtons)
                                            <div class="flex items-center justify-center">
                                                {{ $moveUpAction(['item' => $uuid])->disabled($loop->first) }}
                                            </div>

                                            <div class="flex items-center justify-center">
                                                {{ $moveDownAction(['item' => $uuid])->disabled($loop->last) }}
                                            </div>
                                        @endif
                                    @endif

                                    @if ($isCloneable || $isDeletable)
                                        @if ($cloneAction->isVisible())
                                            <div>
                                                {{ $cloneAction(['item' => $uuid]) }}
                                            </div>
                                        @endif

                                        @if ($isDeletable)
                                            <div>
                                                {{ $deleteAction(['item' => $uuid]) }}
                                            </div>
                                        @endif
                                    @endif

                                    @foreach ($visibleExtraItemActions as $extraItemAction)
                                        <div class="py-2">
                                            {{ $extraItemAction(['item' => $uuid]) }}
                                        </div>
                                    @endforeach

                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>

            </table>

            <div class="p-2 text-xs text-center text-gray-400" x-show="isCollapsed" x-cloak>
                {{ __('filament-table-repeater::components.table-repeater.collapsed') }}
            </div>
        </div>

        @if ($isAddable)
            <div class="relative flex justify-center py-2">
                {{ $addAction }}
            </div>
        @endif

    </div>

</x-dynamic-component>