<script lang="ts">
    interface Props {
        value?: string;
        placeholder?: string;
        minDate?: string;
        maxDate?: string;
        disabled?: boolean;
        class?: string;
        onchange?: (value: string) => void;
    }

    let {
        value = $bindable(""),
        placeholder = "Seleccione fecha",
        minDate = undefined,
        maxDate = undefined,
        disabled = false,
        class: className = "",
        onchange,
    }: Props = $props();

    function handleChange(e: Event) {
        const input = e.target as HTMLInputElement;
        value = input.value;
        onchange?.(input.value);
    }
</script>

<div class="dp-wrapper {className}" class:disabled>
    <input
        type="date"
        value={value ?? ""}
        min={minDate ?? ""}
        max={maxDate ?? ""}
        {disabled}
        class="dp-input"
        class:has-value={!!value}
        onchange={handleChange}
    />
    {#if !value}
        <span class="dp-placeholder">{placeholder}</span>
    {/if}
</div>

<style>
    .dp-wrapper {
        position: relative;
        width: 100%;
        display: block;
    }

    .dp-input {
        width: 100%;
        padding: 0.625rem 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid rgb(var(--border-primary));
        background-color: rgb(var(--bg-secondary));
        color: rgb(var(--text-primary));
        font-size: 0.875rem;
        font-family: inherit;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        box-shadow: none;
        box-sizing: border-box;
        outline: none;
    }

    /* Sin value: ocultar el dd/mm/aaaa nativo para mostrar nuestro placeholder */
    .dp-input:not(.has-value)::-webkit-datetime-edit {
        color: transparent;
    }

    /* Con value: mostrar con color normal */
    .dp-input.has-value::-webkit-datetime-edit {
        color: rgb(var(--text-primary));
    }

    /* Firefox */
    .dp-input:not(.has-value) {
        color: transparent;
    }
    .dp-input.has-value {
        color: rgb(var(--text-primary));
    }

    .dp-placeholder {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: rgb(var(--text-tertiary, 150 150 150));
        font-size: 0.875rem;
        pointer-events: none;
        user-select: none;
    }

    .dp-input:hover:not(:disabled) {
        border-color: rgb(var(--accent-primary) / 0.5);
    }

    .dp-input:focus {
        border-color: rgb(var(--accent-primary));
        box-shadow: 0 0 0 3px rgb(var(--accent-primary) / 0.15);
        /* Al enfocar mostrar siempre el texto nativo */
        color: rgb(var(--text-primary));
    }

    .dp-input:focus::-webkit-datetime-edit {
        color: rgb(var(--text-primary));
    }

    .dp-input:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        background-color: rgb(var(--bg-tertiary));
    }

    .dp-input::-webkit-calendar-picker-indicator {
        opacity: 0.5;
        cursor: pointer;
    }

    .dp-input::-webkit-calendar-picker-indicator:hover {
        opacity: 1;
    }

    .dp-wrapper.disabled {
        opacity: 0.6;
        pointer-events: none;
    }

    @media (max-width: 640px) {
        .dp-input {
            font-size: 1rem;
            padding: 0.75rem;
        }

        .dp-placeholder {
            font-size: 1rem;
        }
    }
</style>