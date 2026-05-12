<script lang="ts">
    import TimeSelector from './TimeSelector.svelte'

    interface Props {
        horaInicio?: string;
        horaFin?: string;
        labelInicio?: string;
        labelFin?: string;
        showDuration?: boolean;
        disabled?: boolean;
        class?: string;
        onchange?: (props: { horaInicio: string; horaFin: string }) => void;
    }

    let {
        horaInicio = $bindable(""),
        horaFin = $bindable(""),
        labelInicio = "Hora inicio",
        labelFin = "Hora fin",
        showDuration = true,
        disabled = false,
        class: className = "",
        onchange,
    }: Props = $props();

    const PRESETS_AM = [
        { label: "7:00", value: "07:00" },
        { label: "7:30", value: "07:30" },
        { label: "8:00", value: "08:00" },
        { label: "8:30", value: "08:30" },
        { label: "9:00", value: "09:00" },
    ];

    const PRESETS_PM = [
        { label: "2:00", value: "14:00" },
        { label: "2:30", value: "14:30" },
        { label: "3:00", value: "15:00" },
        { label: "3:30", value: "15:30" },
        { label: "4:00", value: "16:00" },
    ];

    const ALL_PRESETS = [...PRESETS_AM, ...PRESETS_PM];

    let durationMinutes = $derived(calculateDuration(horaInicio, horaFin));

    function calculateDuration(inicio: string, fin: string): number {
        if (!inicio || !fin) return 0;
        const [inicioH, inicioM] = inicio.split(":").map(Number);
        const [finH, finM] = fin.split(":").map(Number);
        const totalMinInicio = inicioH * 60 + inicioM;
        const totalMinFin = finH * 60 + finM;
        let diff = totalMinFin - totalMinInicio;
        if (diff < 0) diff += 24 * 60;
        return diff;
    }

    function formatDuration(minutes: number): string {
        if (minutes === 0) return "";
        const h = Math.floor(minutes / 60);
        const m = minutes % 60;
        if (h === 0) return `${m} min`;
        if (m === 0) return `${h} hr`;
        return `${h} hr ${m} min`;
    }

    function selectPreset(preset: { label: string; value: string }, isInicio: boolean) {
        if (isInicio) {
            horaInicio = preset.value;
        } else {
            horaFin = preset.value;
        }
        onchange?.({ horaInicio, horaFin });
    }

    function handleManualChange(isInicio: boolean, e: Event) {
        const input = e.target as HTMLInputElement;
        if (isInicio) {
            horaInicio = input.value;
        } else {
            horaFin = input.value;
        }
        onchange?.({ horaInicio, horaFin });
    }
</script>

<div class="trp-wrapper {className}" class:disabled>
    <div class="trp-grid">
        <div class="trp-field">
            <span class="trp-label">{labelInicio} *</span>
            <div class="trp-presets">
                {#each PRESETS_AM as preset}
                    <button
                        type="button"
                        class="trp-preset"
                        class:active={horaInicio === preset.value}
                        onclick={() => selectPreset(preset, true)}
                        {disabled}
                    >
                        {preset.label}
                    </button>
                {/each}
            </div>
            <div class="trp-input-row">
                <TimeSelector bind:value={horaInicio} label={labelInicio} />
                <span class="trp-am-pm">
                    {#if horaInicio}
                        {@const h = parseInt(horaInicio.split(":")[0])}
                        {h < 12 ? "AM" : "PM"}
                    {/if}
                </span>
            </div>
        </div>

        <div class="trp-divider">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </div>

        <div class="trp-field">
            <span class="trp-label">{labelFin}</span>
            <div class="trp-presets">
                {#each ALL_PRESETS as preset}
                    <button
                        type="button"
                        class="trp-preset"
                        class:active={horaFin === preset.value}
                        onclick={() => selectPreset(preset, false)}
                        {disabled}
                    >
                        {preset.label}
                    </button>
                {/each}
            </div>
            <div class="trp-input-row">
                <TimeSelector bind:value={horaFin} label={labelFin} />
                <span class="trp-am-pm">
                    {#if horaFin}
                        {@const h = parseInt(horaFin.split(":")[0])}
                        {h < 12 ? "AM" : "PM"}
                    {/if}
                </span>
            </div>
        </div>
    </div>

    {#if showDuration && durationMinutes > 0}
        <div class="trp-duration">
            <span class="trp-duration-icon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </span>
            Duración: <strong>{formatDuration(durationMinutes)}</strong>
        </div>
    {/if}
</div>

<style>
    .trp-wrapper {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .trp-wrapper.disabled {
        opacity: 0.6;
        pointer-events: none;
    }

    .trp-grid {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        gap: 0.75rem;
        align-items: start;
    }

    .trp-field {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .trp-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: rgb(var(--text-secondary, 100 100 100));
    }

    .trp-presets {
        display: flex;
        flex-wrap: wrap;
        gap: 0.25rem;
    }

    .trp-preset {
        padding: 0.25rem 0.5rem;
        font-size: 0.7rem;
        font-weight: 500;
        border-radius: 0.375rem;
        border: 1px solid rgb(var(--border-primary));
        background-color: rgb(var(--bg-secondary));
        color: rgb(var(--text-primary));
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .trp-preset:hover {
        border-color: rgb(var(--accent-primary));
        background-color: rgb(var(--accent-primary) / 0.1);
    }

    .trp-preset.active {
        background-color: rgb(var(--accent-primary));
        border-color: rgb(var(--accent-primary));
        color: white;
    }

    .trp-input-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .trp-input {
        flex: 1;
        padding: 0.5rem 0.625rem;
        border-radius: 0.5rem;
        border: 1px solid rgb(var(--border-primary));
        background-color: rgb(var(--bg-secondary));
        color: rgb(var(--text-primary));
        font-size: 0.875rem;
        font-family: inherit;
        cursor: pointer;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        outline: none;
    }

    .trp-input:hover:not(:disabled) {
        border-color: rgb(var(--accent-primary) / 0.5);
    }

    .trp-input:focus {
        border-color: rgb(var(--accent-primary));
        box-shadow: 0 0 0 3px rgb(var(--accent-primary) / 0.15);
    }

    .trp-am-pm {
        font-size: 0.7rem;
        font-weight: 600;
        color: rgb(var(--text-muted));
        min-width: 2rem;
    }

    .trp-divider {
        display: flex;
        align-items: center;
        justify-content: center;
        padding-top: 1.5rem;
        color: rgb(var(--text-muted));
    }

    .trp-duration {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        font-size: 0.75rem;
        color: rgb(var(--text-secondary, 100 100 100));
        padding: 0.375rem 0.625rem;
        background-color: rgb(var(--bg-secondary));
        border-radius: 0.375rem;
        width: fit-content;
    }

    .trp-duration-icon {
        display: flex;
        color: rgb(var(--accent-primary));
    }

    .trp-duration strong {
        color: rgb(var(--accent-primary));
    }

    @media (max-width: 640px) {
        .trp-grid {
            grid-template-columns: 1fr;
        }

        .trp-divider {
            padding: 0.25rem 0;
            transform: rotate(90deg);
        }

        .trp-presets {
            max-width: 100%;
        }

        .trp-preset {
            font-size: 0.8rem;
            padding: 0.375rem 0.5rem;
        }
    }
</style>