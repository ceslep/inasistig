<script lang="ts">
    import { onMount, onDestroy } from "svelte";

    interface Props {
        value?: string;
        placeholder?: string;
        dateFormat?: string;
        minDate?: string;
        maxDate?: string;
        disabled?: boolean;
        class?: string;
        onchange?: (value: string) => void;
    }

    let {
        value = $bindable(""),
        placeholder = "Seleccione fecha",
        dateFormat = "Y-m-d",
        minDate = undefined,
        maxDate = undefined,
        disabled = false,
        class: className = "",
        onchange,
    }: Props = $props();

    let inputElement = $state<HTMLInputElement | undefined>(undefined);
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    let flatpickrInstance: any = null;

    const initFlatpickr = async () => {
        if (!inputElement) return;

        const flatpickr = (await import("flatpickr")).default;
        const Spanish = (await import("flatpickr/dist/l10n/es.js")).Spanish;

        const options: Record<string, unknown> = {
            dateFormat: dateFormat,
            allowInput: true,
            enableTime: false,
            time_24hr: true,
            defaultDate: value || undefined,
            locale: Spanish,
            onChange: (selectedDates: Date[], dateStr: string) => {
                value = dateStr;
                if (onchange) {
                    onchange(dateStr);
                }
            },
        };

        if (minDate) {
            options.minDate = minDate;
        }
        if (maxDate) {
            options.maxDate = maxDate;
        }

        flatpickrInstance = flatpickr(inputElement, options);

        if (disabled && flatpickrInstance) {
            flatpickrInstance.set("disable", true);
        }
    };

    const destroyFlatpickr = () => {
        if (flatpickrInstance) {
            flatpickrInstance.destroy();
            flatpickrInstance = null;
        }
    };

    $effect(() => {
        if (flatpickrInstance && value !== undefined) {
            const currentDate = flatpickrInstance.selectedDates[0];
            const shouldBeDate = value ? new Date(value) : null;

            if (shouldBeDate) {
                const currentDateStr = currentDate ? flatpickrInstance.formatDate(currentDate, dateFormat) : null;
                const newDateStr = flatpickrInstance.formatDate(shouldBeDate, dateFormat);
                
                if (currentDateStr !== newDateStr) {
                    flatpickrInstance.setDate(value);
                }
            } else if (currentDate) {
                flatpickrInstance.clear();
            }
        }
    });

    $effect(() => {
        if (flatpickrInstance) {
            if (disabled) {
                flatpickrInstance.set("disable", true);
            } else {
                flatpickrInstance.set("disable", false);
            }
        }
    });

    onMount(() => {
        initFlatpickr();
    });

    onDestroy(() => {
        destroyFlatpickr();
    });
</script>

<div class="datepicker-wrapper {className}" class:disabled>
    <input
        bind:this={inputElement}
        type="text"
        {placeholder}
        class="flatpickr-input datepicker-input"
        class:disabled
    />
</div>

<style>
    .datepicker-wrapper {
        width: 100%;
        display: block;
    }

    .datepicker-wrapper :global(.flatpickr-input),
    .datepicker-wrapper :global(input.flatpickr-input) {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid rgb(var(--border-primary));
        background-color: rgb(var(--bg-secondary));
        color: rgb(var(--text-primary));
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: none;
    }

    .datepicker-wrapper :global(.flatpickr-input:hover),
    .datepicker-wrapper :global(input.flatpickr-input:hover) {
        border-color: rgb(var(--accent-primary) / 0.5);
    }

    .datepicker-wrapper :global(.flatpickr-input:focus),
    .datepicker-wrapper :global(input.flatpickr-input:focus) {
        outline: none;
        border-color: rgb(var(--accent-primary));
        box-shadow: 0 0 0 3px rgb(var(--accent-primary) / 0.15);
    }

    .datepicker-wrapper.disabled :global(.flatpickr-input),
    .datepicker-wrapper.disabled :global(input.flatpickr-input) {
        opacity: 0.6;
        cursor: not-allowed;
        pointer-events: none;
        background-color: rgb(var(--bg-tertiary));
    }

    .datepicker-wrapper.disabled {
        opacity: 0.6;
        pointer-events: none;
    }

    .datepicker-wrapper.disabled :global(input) {
        cursor: not-allowed;
    }

    @media (max-width: 640px) {
        .datepicker-wrapper :global(.flatpickr-input),
        .datepicker-wrapper :global(input.flatpickr-input) {
            font-size: 1rem;
            padding: 0.75rem;
        }
    }
</style>
