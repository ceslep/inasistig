<script lang="ts">
    import { onMount, onDestroy } from "svelte";

    interface Option {
        value: string;
        label: string;
        favorite?: boolean;
    }

    interface Props {
        options: Option[];
        value?: string;
        placeholder?: string;
        disabled?: boolean;
        allowEmpty?: boolean;
        searchable?: boolean;
        create?: boolean;
        showFavorite?: boolean;
        class?: string;
        onchange?: (value: string) => void;
    }

    let {
        options = [],
        value = $bindable(""),
        placeholder = "Seleccione una opción",
        disabled = false,
        allowEmpty = true,
        searchable = true,
        create = false,
        showFavorite = false,
        class: className = "",
        onchange,
    }: Props = $props();

    let selectElement = $state<HTMLSelectElement | undefined>(undefined);
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let tomSelectInstance: any = null;
    let isInitialized = $state(false);

    const syncOptions = () => {
        if (!tomSelectInstance) return;

        tomSelectInstance.clearOptions();

        options.forEach((opt) => {
            tomSelectInstance.addOption({
                value: opt.value,
                label: opt.label,
                favorite: opt.favorite || false,
            });
        });

        if (value && tomSelectInstance.getValue() !== value) {
            tomSelectInstance.setValue(value);
        }
    };

    const initTomSelect = async () => {
        if (!selectElement) return;

        const TomSelectLib = (await import("tom-select")).default;

        const plugins: string[] = [];
        if (allowEmpty) {
            plugins.push("clear_button");
        }
        if (searchable) {
            plugins.push("dropdown_input");
        }

        const settings: Record<string, unknown> = {
            plugins: plugins,
            persist: false,
            create: create,
            allowEmptyOption: allowEmpty,
            copyClassesToDropdown: true,
            dropdownParent: "body",
            labelField: "label",
            valueField: "value",
            searchField: ["label"],
        };

        if (showFavorite) {
            settings.render = {
                option: (data: { label: string; favorite?: boolean }, escape: (str: string) => string) => {
                    const star = data.favorite ? '<span class="ts-star">★</span>' : '';
                    const label = escape(data.label);
                    return `<div class="ts-option${data.favorite ? ' ts-option-favorite' : ''}">${star}${label}</div>`;
                },
                item: (data: { label: string; favorite?: boolean }, escape: (str: string) => string) => {
                    const star = data.favorite ? '<span class="ts-star">★</span>' : '';
                    const label = escape(data.label);
                    return `<div class="ts-item">${star}${label}</div>`;
                },
            };
        } else {
            settings.render = {
                no_results: () => '<div class="ts-no-results">No se encontraron resultados</div>',
                not_options: () => '<div class="ts-no-results">Sin opciones disponibles</div>',
            };
        }

        tomSelectInstance = new TomSelectLib(selectElement, settings);

        tomSelectInstance.on("change", (newValue: string) => {
            value = newValue || "";
            if (onchange) {
                onchange(value);
            }
        });

        syncOptions();

        isInitialized = true;
    };

    const destroyTomSelect = () => {
        if (tomSelectInstance) {
            tomSelectInstance.destroy();
            tomSelectInstance = null;
            isInitialized = false;
        }
    };

    $effect(() => {
        options;
        if (isInitialized && tomSelectInstance) {
            syncOptions();
        }
    });

    $effect(() => {
        if (isInitialized && tomSelectInstance) {
            const currentValue = tomSelectInstance.getValue();
            if (value !== currentValue) {
                if (value) {
                    tomSelectInstance.setValue(value);
                } else {
                    tomSelectInstance.clear();
                }
            }
        }
    });

    $effect(() => {
        if (isInitialized && tomSelectInstance) {
            if (disabled) {
                tomSelectInstance.disable();
            } else {
                tomSelectInstance.enable();
            }
        }
    });

    onMount(() => {
        initTomSelect();
    });

    onDestroy(() => {
        destroyTomSelect();
    });
</script>

<div class="tom-select-container {className}">
    <select
        bind:this={selectElement}
        class="tom-select-native"
    >
        {#if !options.length || allowEmpty}
            <option value="">{placeholder}</option>
        {/if}
        {#each options as opt}
            <option value={opt.value}>{opt.label}</option>
        {/each}
    </select>
</div>

<style>
    .tom-select-container {
        width: 100%;
    }

    .tom-select-container :global(.ts-wrapper) {
        width: 100%;
        border-radius: 0.5rem;
    }

    .tom-select-container :global(.ts-control) {
        border-radius: 0.5rem;
        padding: 0.625rem 0.75rem;
        border: 1px solid rgb(var(--border-primary));
        background-color: rgb(var(--bg-secondary));
        color: rgb(var(--text-primary));
        font-size: 0.875rem;
    }

    .tom-select-container :global(.ts-control:hover) {
        border-color: rgb(var(--accent-primary) / 0.5);
    }

    .tom-select-container :global(.ts-control:focus),
    .tom-select-container :global(.ts-wrapper.focus .ts-control) {
        border-color: rgb(var(--accent-primary));
        box-shadow: 0 0 0 3px rgb(var(--accent-primary) / 0.15);
        outline: none;
    }

    .tom-select-container :global(.ts-dropdown) {
        background-color: rgb(var(--bg-secondary));
        border: 1px solid rgb(var(--border-primary));
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 
                    0 2px 4px -2px rgb(0 0 0 / 0.1);
        z-index: 9999;
        max-height: 280px;
        overflow-y: auto;
    }

    .tom-select-container :global(.ts-dropdown .option) {
        padding: 0.625rem 0.75rem;
        border-radius: 0.375rem;
        color: rgb(var(--text-primary));
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .tom-select-container :global(.ts-dropdown .option:hover) {
        background-color: rgb(var(--accent-primary) / 0.1);
        color: rgb(var(--accent-primary));
    }

    .tom-select-container :global(.ts-dropdown .selected) {
        background-color: rgb(var(--accent-primary) / 0.15);
        color: rgb(var(--accent-primary));
        font-weight: 500;
    }

    .tom-select-container :global(.ts-option-favorite) {
        background-color: rgb(var(--accent-primary) / 0.08);
        font-weight: 500;
    }

    .tom-select-container :global(.ts-option-favorite:hover) {
        background-color: rgb(var(--accent-primary) / 0.15) !important;
    }

    .tom-select-container :global(.ts-star) {
        color: #f59e0b;
        margin-right: 6px;
        font-size: 1rem;
        text-shadow: 0 0 2px rgb(245 158 11 / 0.3);
    }

    .tom-select-container :global(.ts-placeholder) {
        color: rgb(var(--text-muted));
    }

    .tom-select-container :global(.ts-no-results) {
        padding: 0.75rem;
        color: rgb(var(--text-muted));
        font-style: italic;
        text-align: center;
    }

    .tom-select-container :global(.ts-wrapper.ts-disabled .ts-control) {
        background-color: rgb(var(--bg-tertiary));
        opacity: 0.6;
        cursor: not-allowed;
    }

    .tom-select-container :global(.ts-arrow) {
        opacity: 0.5;
    }

    .tom-select-container :global(.ts-wrapper.focus .ts-arrow) {
        opacity: 1;
    }

    .tom-select-container :global(.ts-clear-button) {
        color: rgb(var(--text-muted));
    }

    .tom-select-container :global(.ts-clear-button:hover) {
        color: rgb(var(--accent-primary));
    }

    @media (max-width: 640px) {
        .tom-select-container :global(.ts-control) {
            font-size: 1rem;
            padding: 0.75rem;
        }

        .tom-select-container :global(.ts-dropdown) {
            max-height: 200px;
        }

        .tom-select-container :global(.ts-dropdown .option) {
            padding: 0.75rem;
            font-size: 1rem;
        }
    }
</style>
