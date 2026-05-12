<script lang="ts">
  import { fade, scale } from 'svelte/transition'
  import { Clock, X } from '@lucide/svelte'

  interface Props {
    value?: string
    label?: string
    disabled?: boolean
    class?: string
    onchange?: (value: string) => void
  }

  let {
    value = $bindable(''),
    label = 'Hora',
    disabled = false,
    class: className = '',
    onchange,
  }: Props = $props()

  let isOpen = $state(false)
  let isPM = $state(false)
  let selectingHours = $state(true)
  let selectedHour = $state(12)
  let selectedMinute = $state(0)
  let isDragging = $state(false)

  const HOURS = [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11]
  const MINUTES = [0, 5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 55]

  const HOUR_PRESETS = [
    { label: '7:00', value: '07:00' },
    { label: '7:30', value: '07:30' },
    { label: '8:00', value: '08:00' },
    { label: '8:30', value: '08:30' },
    { label: '9:00', value: '09:00' },
  ]

  const PM_PRESETS = [
    { label: '2:00', value: '14:00' },
    { label: '2:30', value: '14:30' },
    { label: '3:00', value: '15:00' },
    { label: '3:30', value: '15:30' },
    { label: '4:00', value: '16:00' },
  ]

  function parseValue(val: string) {
    if (!val) return { hour: 12, minute: 0, pm: false }
    const [h, m] = val.split(':')
    const hourInt = parseInt(h || '0')
    return {
      hour: hourInt % 12 || 12,
      minute: parseInt(m || '0'),
      pm: hourInt >= 12
    }
  }

  function openPicker() {
    if (disabled) return
    const parsed = parseValue(value)
    selectedHour = parsed.hour
    selectedMinute = parsed.minute
    isPM = parsed.pm
    selectingHours = true
    isOpen = true
  }

  function closePicker() {
    isOpen = false
  }

  function confirmTime() {
    let hour = selectedHour % 12
    if (isPM) hour += 12
    if (selectedHour === 12 && !isPM) hour = 0
    if (hour === 24) hour = 12
    const hourStr = hour.toString().padStart(2, '0')
    const minuteStr = selectedMinute.toString().padStart(2, '0')
    const newValue = `${hourStr}:${minuteStr}`
    value = newValue
    onchange?.(newValue)
    closePicker()
  }

  function selectPreset(h: number, m: number, pm: boolean) {
    selectedHour = h
    selectedMinute = m
    isPM = pm
    selectingHours = false
  }

  function handleClockClick(value: number) {
    if (selectingHours) {
      selectedHour = value
      selectingHours = false
    } else {
      selectedMinute = value
    }
  }

  function toggleMode() {
    selectingHours = !selectingHours
  }

  function toggleAMPM() {
    isPM = !isPM
  }

  function handleBackdropClick(e: MouseEvent) {
    if (e.target === e.currentTarget) {
      closePicker()
    }
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
      closePicker()
    }
  }

  function getHourPosition(h: number) {
    const radius = 85
    const angle = (h % 12) * 30 - 90
    const rad = (angle * Math.PI) / 180
    return {
      x: 100 + radius * Math.cos(rad),
      y: 100 + radius * Math.sin(rad)
    }
  }

  function getMinutePosition(m: number) {
    const radius = 75
    const angle = m * 6 - 90
    const rad = (angle * Math.PI) / 180
    return {
      x: 100 + radius * Math.cos(rad),
      y: 100 + radius * Math.sin(rad)
    }
  }

  function getHourHandAngle(): number {
    return (selectedHour % 12) * 30 + (selectingHours ? 0 : selectedMinute * 0.5) - 90
  }

  function getMinuteHandAngle(): number {
    return selectedMinute * 6 - 90
  }

  function formatDisplayValue(): string {
    const h = ((selectedHour % 12) || 12).toString().padStart(2, '0')
    const m = selectedMinute.toString().padStart(2, '0')
    return `${h}:${m}`
  }

  function formatValue(): string {
    if (!value) return ''
    const [h, m] = value.split(':')
    const hInt = parseInt(h || '0')
    const displayH = ((hInt % 12) || 12).toString()
    return `${displayH}:${m} ${hInt >= 12 ? 'PM' : 'AM'}`
  }
</script>

<svelte:window onkeydown={handleKeydown} />

<div class="tc-wrapper {className}" class:disabled>
  <span class="tc-label">{label}</span>
  <button
    type="button"
    class="tc-trigger"
    onclick={openPicker}
    {disabled}
    aria-haspopup="dialog"
    aria-expanded={isOpen}
  >
    <Clock class="tc-trigger-icon" />
    <span class="tc-trigger-text">{formatValue() || 'Seleccionar...'}</span>
  </button>
</div>

{#if isOpen}
  <!-- svelte-ignore a11y_no_static_element_interactions -->
  <!-- svelte-ignore a11y_click_events_have_key_events -->
  <div
    class="tc-backdrop"
    onclick={handleBackdropClick}
    transition:fade={{ duration: 150 }}
  >
    <div
      class="tc-popup"
      transition:scale={{ duration: 200, start: 0.95 }}
      role="dialog"
      aria-modal="true"
      aria-label="Selector de hora"
      tabindex="-1"
    >
      <div class="tc-popup-header">
        <span class="tc-popup-title">{label}</span>
        <button type="button" class="tc-close-btn" onclick={closePicker} aria-label="Cerrar">
          <X class="w-5 h-5" />
        </button>
      </div>

      <div class="tc-clock-container">
        <div class="tc-clock-face">
          <svg viewBox="0 0 200 200" class="tc-clock-svg">
            <circle cx="100" cy="100" r="95" class="tc-clock-bg" />
            <circle cx="100" cy="100" r="3" class="tc-clock-center" />

            {#if selectingHours}
              {#each HOURS as h}
                {@const pos = getHourPosition(h)}
                {@const isSelected = h === selectedHour}
                <!-- svelte-ignore a11y_no_static_element_interactions -->
                <!-- svelte-ignore a11y_click_events_have_key_events -->
                <g class="tc-hour-group" onclick={() => handleClockClick(h)} style="cursor: pointer;" role="button" tabindex="0" onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') handleClockClick(h) }}>
                  <circle
                    cx={pos.x}
                    cy={pos.y}
                    r={isSelected ? 16 : 12}
                    class="tc-hour-bubble {isSelected ? 'selected' : ''}"
                  />
                  <text
                    x={pos.x}
                    y={pos.y + 4}
                    class="tc-hour-text {isSelected ? 'selected' : ''}"
                    text-anchor="middle"
                    dominant-baseline="middle"
                  >
                    {h}
                  </text>
                </g>
              {/each}
              <line
                x1="100" y1="100"
                x2={100 + 55 * Math.cos((getHourHandAngle()) * Math.PI / 180)}
                y2={100 + 55 * Math.sin((getHourHandAngle()) * Math.PI / 180)}
                class="tc-hand tc-hour-hand"
              />
            {:else}
              {#each MINUTES as m}
                {@const pos = getMinutePosition(m)}
                {@const isSelected = m === selectedMinute}
                <!-- svelte-ignore a11y_no_static_element_interactions -->
                <!-- svelte-ignore a11y_click_events_have_key_events -->
                <g class="tc-minute-group" onclick={() => handleClockClick(m)} style="cursor: pointer;" role="button" tabindex="0" onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') handleClockClick(m) }}>
                  <circle
                    cx={pos.x}
                    cy={pos.y}
                    r={isSelected ? 14 : 10}
                    class="tc-minute-bubble {isSelected ? 'selected' : ''}"
                  />
                  <text
                    x={pos.x}
                    y={pos.y + 3}
                    class="tc-minute-text {isSelected ? 'selected' : ''}"
                    text-anchor="middle"
                    dominant-baseline="middle"
                  >
                    {m.toString().padStart(2, '0')}
                  </text>
                </g>
              {/each}
              <line
                x1="100" y1="100"
                x2={100 + 60 * Math.cos((getMinuteHandAngle()) * Math.PI / 180)}
                y2={100 + 60 * Math.sin((getMinuteHandAngle()) * Math.PI / 180)}
                class="tc-hand tc-minute-hand"
              />
            {/if}
          </svg>

          <button
            type="button"
            class="tc-mode-btn"
            onclick={toggleMode}
          >
            {selectingHours ? 'Horas' : 'Minutos'}
          </button>
        </div>

        <div class="tc-display">
          <span class="tc-display-time">{formatDisplayValue()}</span>
          <button
            type="button"
            class="tc-am-pm-btn {isPM ? 'pm' : 'am'}"
            onclick={toggleAMPM}
          >
            {isPM ? 'PM' : 'AM'}
          </button>
        </div>
      </div>

      <div class="tc-presets">
        <div class="tc-preset-group">
          <span class="tc-preset-label">AM</span>
          <div class="tc-preset-buttons">
            {#each HOUR_PRESETS as preset}
              {@const [h, m] = preset.value.split(':').map(Number)}
              <button
                type="button"
                class="tc-preset-btn"
                onclick={() => selectPreset(h === 0 ? 12 : h % 12 === 0 ? 12 : h % 12, m, false)}
              >
                {preset.label}
              </button>
            {/each}
          </div>
        </div>
        <div class="tc-preset-group">
          <span class="tc-preset-label">PM</span>
          <div class="tc-preset-buttons">
            {#each PM_PRESETS as preset}
              {@const [h, m] = preset.value.split(':').map(Number)}
              <button
                type="button"
                class="tc-preset-btn"
                onclick={() => selectPreset(h % 12 === 0 ? 12 : h % 12, m, true)}
              >
                {preset.label}
              </button>
            {/each}
          </div>
        </div>
      </div>

      <div class="tc-popup-footer">
        <button type="button" class="tc-btn tc-btn-cancel" onclick={closePicker}>
          Cancelar
        </button>
        <button type="button" class="tc-btn tc-btn-confirm" onclick={confirmTime}>
          Confirmar
        </button>
      </div>
    </div>
  </div>
{/if}

<style>
  .tc-wrapper {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
  }

  .tc-wrapper.disabled {
    opacity: 0.6;
    pointer-events: none;
  }

  .tc-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: rgb(var(--text-secondary, 100 100 100));
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .tc-trigger {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 0.875rem;
    background: rgb(var(--bg-secondary));
    border: 1px solid rgb(var(--border-primary));
    border-radius: 0.625rem;
    cursor: pointer;
    transition: all 0.2s ease;
    color: rgb(var(--text-primary));
  }

  .tc-trigger:hover {
    border-color: rgb(var(--accent-primary));
    background: rgb(var(--bg-primary));
  }

  .tc-trigger:focus {
    outline: none;
    border-color: rgb(var(--accent-primary));
    box-shadow: 0 0 0 3px rgba(var(--accent-primary), 0.15);
  }

  .tc-trigger-icon {
    width: 1.125rem;
    height: 1.125rem;
    color: rgb(var(--text-muted));
    flex-shrink: 0;
  }

  .tc-trigger-text {
    flex: 1;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 500;
  }

  .tc-backdrop {
    position: fixed;
    inset: 0;
    z-index: 100;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    backdrop-filter: blur(4px);
  }

  .tc-popup {
    background: rgb(var(--bg-primary));
    border: 1px solid rgb(var(--border-primary));
    border-radius: 1rem;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    width: 100%;
    max-width: 360px;
    overflow: hidden;
  }

  .tc-popup-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.875rem 1rem;
    border-bottom: 1px solid rgb(var(--border-primary));
    background: rgb(var(--bg-secondary));
  }

  .tc-popup-title {
    font-size: 0.875rem;
    font-weight: 700;
    color: rgb(var(--text-primary));
    text-transform: uppercase;
    letter-spacing: 0.05em;
  }

  .tc-close-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    color: rgb(var(--text-muted));
    transition: all 0.15s ease;
  }

  .tc-close-btn:hover {
    background: rgb(var(--bg-primary));
    color: rgb(var(--text-primary));
  }

  .tc-clock-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1.5rem 1rem 1rem;
    gap: 1rem;
  }

  .tc-clock-face {
    position: relative;
    width: 200px;
    height: 200px;
  }

  .tc-clock-svg {
    width: 100%;
    height: 100%;
  }

  .tc-clock-bg {
    fill: rgb(var(--bg-primary));
    stroke: rgb(var(--border-primary));
    stroke-width: 2;
  }

  .tc-clock-center {
    fill: rgb(var(--accent-primary));
  }

  .tc-hour-bubble,
  .tc-minute-bubble {
    fill: rgb(var(--bg-secondary));
    stroke: rgb(var(--border-primary));
    stroke-width: 1.5;
    transition: all 0.2s ease;
  }

  .tc-hour-bubble.selected,
  .tc-minute-bubble.selected {
    fill: rgb(var(--accent-primary));
    stroke: rgb(var(--accent-primary));
    filter: drop-shadow(0 2px 4px rgba(var(--accent-primary), 0.3));
  }

  .tc-hour-text,
  .tc-minute-text {
    font-size: 10px;
    font-weight: 600;
    fill: rgb(var(--text-primary));
    pointer-events: none;
    user-select: none;
  }

  .tc-hour-text.selected,
  .tc-minute-text.selected {
    fill: white;
  }

  .tc-hand {
    stroke: rgb(var(--accent-primary));
    stroke-width: 3;
    stroke-linecap: round;
  }

  .tc-hour-hand {
    stroke-width: 4;
  }

  .tc-minute-hand {
    stroke-width: 2;
    stroke: rgb(var(--text-secondary));
  }

  .tc-mode-btn {
    position: absolute;
    bottom: -8px;
    left: 50%;
    transform: translateX(-50%);
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    background: rgb(var(--accent-primary));
    color: white;
    font-size: 0.625rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 2px solid rgb(var(--bg-primary));
    transition: all 0.2s ease;
  }

  .tc-mode-btn:hover {
    filter: brightness(1.1);
  }

  .tc-display {
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .tc-display-time {
    font-size: 2.5rem;
    font-weight: 700;
    color: rgb(var(--text-primary));
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.02em;
  }

  .tc-am-pm-btn {
    padding: 0.5rem 0.75rem;
    border-radius: 0.5rem;
    background: rgb(var(--bg-secondary));
    border: 1px solid rgb(var(--border-primary));
    color: rgb(var(--text-secondary));
    font-size: 0.75rem;
    font-weight: 700;
    transition: all 0.2s ease;
  }

  .tc-am-pm-btn:hover {
    border-color: rgb(var(--accent-primary));
    color: rgb(var(--accent-primary));
  }

  .tc-am-pm-btn.pm {
    background: rgb(var(--accent-primary));
    border-color: rgb(var(--accent-primary));
    color: white;
  }

  .tc-presets {
    padding: 0.75rem 0.875rem;
    border-top: 1px solid rgb(var(--border-primary));
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .tc-preset-group {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .tc-preset-label {
    font-size: 0.625rem;
    font-weight: 700;
    color: rgb(var(--text-muted));
    text-transform: uppercase;
    min-width: 1.5rem;
  }

  .tc-preset-buttons {
    display: flex;
    gap: 0.375rem;
    flex-wrap: wrap;
  }

  .tc-preset-btn {
    padding: 0.375rem 0.625rem;
    border-radius: 0.375rem;
    background: rgb(var(--bg-secondary));
    border: 1px solid rgb(var(--border-primary));
    color: rgb(var(--text-secondary));
    font-size: 0.75rem;
    font-weight: 600;
    transition: all 0.15s ease;
  }

  .tc-preset-btn:hover {
    border-color: rgb(var(--accent-primary));
    color: rgb(var(--accent-primary));
    background: rgb(var(--bg-primary));
  }

  .tc-popup-footer {
    display: flex;
    gap: 0.5rem;
    padding: 0.875rem;
    border-top: 1px solid rgb(var(--border-primary));
    background: rgb(var(--bg-secondary));
  }

  .tc-btn {
    flex: 1;
    padding: 0.625rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.15s ease;
  }

  .tc-btn-cancel {
    background: rgb(var(--bg-primary));
    border: 1px solid rgb(var(--border-primary));
    color: rgb(var(--text-secondary));
  }

  .tc-btn-cancel:hover {
    border-color: rgb(var(--text-muted));
    color: rgb(var(--text-primary));
  }

  .tc-btn-confirm {
    background: rgb(var(--accent-primary));
    border: 1px solid rgb(var(--accent-primary));
    color: white;
  }

  .tc-btn-confirm:hover {
    filter: brightness(1.1);
  }

  @media (max-width: 380px) {
    .tc-popup {
      max-width: 100%;
      border-radius: 0.75rem;
    }

    .tc-clock-face {
      width: 180px;
      height: 180px;
    }

    .tc-display-time {
      font-size: 2rem;
    }
  }
</style>