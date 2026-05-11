<script lang="ts">
  import { Calendar, ChevronLeft, ChevronRight, X } from '@lucide/svelte';
  import { fade, scale } from 'svelte/transition';

  interface Props {
    id: string;
    label?: string;
    value?: string;
    placeholder?: string;
    dateFormat?: string;
    minDate?: string;
    maxDate?: string;
    hasError?: boolean;
    disabled?: boolean;
    class?: string;
    onchange?: (value: string) => void;
  }

  let {
    id,
    label = '',
    value = $bindable(''),
    placeholder = 'Seleccione fecha',
    dateFormat = 'Y-m-d',
    minDate = undefined,
    maxDate = undefined,
    hasError = false,
    disabled = false,
    class: className = '',
    onchange,
  }: Props = $props();

  let isOpen = $state(false);
  let triggerRef = $state<HTMLButtonElement>();
  let popupRef = $state<HTMLDivElement>();

  const today = new Date();
  let currentMonth = $state(today.getMonth());
  let currentYear = $state(today.getFullYear());

  const monthNames = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
  ];

  const dayNames = ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'];

  const selectedDate = $derived(
    value ? new Date(value + 'T00:00:00') : null
  );

  const daysInMonth = $derived(() => {
    const year = currentYear;
    const month = currentMonth;
    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const days: (number | null)[] = [];
    
    for (let i = 0; i < firstDay; i++) {
      days.push(null);
    }
    for (let i = 1; i <= daysInMonth; i++) {
      days.push(i);
    }
    return days;
  });

  const formattedValue = $derived(() => {
    if (!value) return '';
    const date = new Date(value + 'T00:00:00');
    return date.toLocaleDateString('es-CO', {
      weekday: 'short',
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    });
  });

  const isToday = (day: number) => {
    return day === today.getDate() && 
           currentMonth === today.getMonth() && 
           currentYear === today.getFullYear();
  };

  const isSelected = (day: number) => {
    if (!selectedDate) return false;
    return day === selectedDate.getDate() && 
           currentMonth === selectedDate.getMonth() && 
           currentYear === selectedDate.getFullYear();
  };

  const selectDay = (day: number) => {
    const date = new Date(currentYear, currentMonth, day);
    const isoDate = date.toLocaleDateString('en-CA');
    value = isoDate;
    isOpen = false;
    onchange?.(isoDate);
  };

  const prevMonth = () => {
    if (currentMonth === 0) {
      currentMonth = 11;
      currentYear--;
    } else {
      currentMonth--;
    }
  };

  const nextMonth = () => {
    if (currentMonth === 11) {
      currentMonth = 0;
      currentYear++;
    } else {
      currentMonth++;
    }
  };

  const goToToday = () => {
    currentMonth = today.getMonth();
    currentYear = today.getFullYear();
    selectDay(today.getDate());
  };

  const clearDate = () => {
    value = '';
    isOpen = false;
    onchange?.('');
  };

  const toggleOpen = () => {
    if (!disabled) {
      isOpen = !isOpen;
      if (isOpen && value) {
        const date = new Date(value + 'T00:00:00');
        currentMonth = date.getMonth();
        currentYear = date.getFullYear();
      }
    }
  };

  const handleBackdropClick = (e: MouseEvent) => {
    if (e.target === e.currentTarget) {
      isOpen = false;
    }
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (!isOpen) {
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
        e.preventDefault();
        isOpen = true;
      }
      return;
    }

    switch (e.key) {
      case 'Escape':
        isOpen = false;
        break;
    }
  };
</script>

<div class="date-picker-container {className}">
  {#if label}
    <label
      for={id}
      class="date-picker-label"
    >
      {label}
    </label>
  {/if}

  <div class="relative">
    <button
      type="button"
      bind:this={triggerRef}
      onclick={toggleOpen}
      onkeydown={handleKeydown}
      disabled={disabled}
      class="date-picker-trigger"
      class:date-picker-trigger--open={isOpen}
      class:date-picker-trigger--error={hasError}
      class:date-picker-trigger--disabled={disabled}
      aria-haspopup="dialog"
      aria-expanded={isOpen}
      aria-label={label || placeholder}
    >
      <Calendar class="date-picker-trigger__icon" />
      {#if value}
        <span class="date-picker-trigger__value">{formattedValue()}</span>
      {:else}
        <span class="date-picker-trigger__placeholder">{placeholder}</span>
      {/if}
      
      {#if value && !disabled}
        <span
          role="button"
          tabindex="0"
          onclick={(e) => { e.stopPropagation(); clearDate(); }}
          onkeydown={(e) => { if (e.key === 'Enter' || e.key === ' ') { e.stopPropagation(); clearDate(); } }}
          class="date-picker-trigger__clear"
          aria-label="Limpiar fecha"
        >
          <X class="w-4 h-4" />
        </span>
      {:else}
        <ChevronRight class="date-picker-trigger__arrow {isOpen ? 'date-picker-trigger__arrow--open' : ''}" />
      {/if}
    </button>

    {#if isOpen}
      <div
        class="date-picker-backdrop"
        onclick={handleBackdropClick}
        onkeydown={(e) => e.key === 'Escape' && (isOpen = false)}
        role="presentation"
        transition:fade={{ duration: 150 }}
      >
        <div
          bind:this={popupRef}
          class="date-picker-popup"
          role="dialog"
          aria-modal="true"
          aria-label="Seleccionar fecha"
          tabindex="-1"
          transition:scale={{ duration: 150, start: 0.95 }}
          onclick={(e) => e.stopPropagation()}
          onkeydown={(e) => e.stopPropagation()}
        >
          <!-- Header -->
          <div class="date-picker-popup__header">
            <button
              type="button"
              onclick={prevMonth}
              class="date-picker-popup__nav"
              aria-label="Mes anterior"
            >
              <ChevronLeft class="w-5 h-5" />
            </button>
            
            <div class="date-picker-popup__month-year">
              <span class="date-picker-popup__month">{monthNames[currentMonth]}</span>
              <span class="date-picker-popup__year">{currentYear}</span>
            </div>
            
            <button
              type="button"
              onclick={nextMonth}
              class="date-picker-popup__nav"
              aria-label="Mes siguiente"
            >
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>

          <!-- Day names -->
          <div class="date-picker-popup__weekdays">
            {#each dayNames as day}
              <span class="date-picker-popup__weekday">{day}</span>
            {/each}
          </div>

          <!-- Days grid -->
          <div class="date-picker-popup__days">
            {#each daysInMonth() as day}
              {#if day === null}
                <span class="date-picker-popup__day date-picker-popup__day--empty"></span>
              {:else}
                <button
                  type="button"
                  onclick={() => selectDay(day)}
                  class="date-picker-popup__day"
                  class:date-picker-popup__day--today={isToday(day)}
                  class:date-picker-popup__day--selected={isSelected(day)}
                >
                  {day}
                </button>
              {/if}
            {/each}
          </div>

          <!-- Footer -->
          <div class="date-picker-popup__footer">
            <button
              type="button"
              onclick={goToToday}
              class="date-picker-popup__today"
            >
              Hoy
            </button>
          </div>
        </div>
      </div>
    {/if}
  </div>
</div>

<style>
  .date-picker-container {
    width: 100%;
  }

  .date-picker-label {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: rgb(var(--text-secondary));
  }

  .relative {
    position: relative;
  }

  .date-picker-trigger {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    border-radius: 0.75rem;
    border: 1.5px solid rgb(var(--border-primary));
    background-color: rgb(var(--bg-secondary));
    color: rgb(var(--text-primary));
    font-size: 0.9375rem;
    text-align: left;
    cursor: pointer;
    transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
  }

  .date-picker-trigger:hover:not(:disabled) {
    border-color: rgb(var(--accent-primary));
    background-color: rgb(var(--bg-primary));
  }

  .date-picker-trigger:focus-visible {
    outline: 2px solid rgb(var(--accent-primary));
    outline-offset: 2px;
  }

  .date-picker-trigger--open {
    border-color: rgb(var(--accent-primary));
    box-shadow: 0 0 0 3px rgb(var(--accent-primary) / 0.15);
  }

  .date-picker-trigger--error {
    border-color: #ef4444 !important;
  }

  .date-picker-trigger--disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .date-picker-trigger__icon {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
    color: rgb(var(--accent-primary));
  }

  .date-picker-trigger__value {
    flex: 1;
    font-weight: 500;
  }

  .date-picker-trigger__placeholder {
    flex: 1;
    color: rgb(var(--text-muted));
  }

  .date-picker-trigger__clear {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem;
    border-radius: 0.375rem;
    color: rgb(var(--text-muted));
    transition: all 150ms;
  }

  .date-picker-trigger__clear:hover {
    background-color: rgb(var(--border-primary));
    color: rgb(var(--text-primary));
  }

  .date-picker-trigger__arrow {
    width: 1.25rem;
    height: 1.25rem;
    flex-shrink: 0;
    color: rgb(var(--text-muted));
    transition: transform 200ms;
  }

  .date-picker-trigger__arrow--open {
    transform: rotate(180deg);
  }

  .date-picker-backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 20vh;
    background-color: rgb(0 0 0 / 0.4);
    backdrop-filter: blur(4px);
  }

  .date-picker-popup {
    position: relative;
    width: 100%;
    max-width: 320px;
    margin: 0 1rem;
    padding: 1rem;
    border-radius: 1rem;
    border: 1px solid rgb(var(--border-primary));
    background-color: rgb(var(--card-bg));
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.25);
  }

  .date-picker-popup__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1rem;
  }

  .date-picker-popup__nav {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    border-radius: 0.5rem;
    color: rgb(var(--text-secondary));
    transition: all 150ms;
  }

  .date-picker-popup__nav:hover {
    background-color: rgb(var(--accent-primary) / 0.1);
    color: rgb(var(--accent-primary));
  }

  .date-picker-popup__month-year {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-weight: 600;
    color: rgb(var(--text-primary));
  }

  .date-picker-popup__month {
    font-size: 1rem;
  }

  .date-picker-popup__year {
    font-size: 0.875rem;
    color: rgb(var(--text-secondary));
  }

  .date-picker-popup__weekdays {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
    margin-bottom: 0.5rem;
  }

  .date-picker-popup__weekday {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem;
    font-size: 0.75rem;
    font-weight: 500;
    color: rgb(var(--text-muted));
  }

  .date-picker-popup__days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
  }

  .date-picker-popup__day {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.25rem;
    height: 2.25rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    color: rgb(var(--text-primary));
    transition: all 150ms;
  }

  .date-picker-popup__day:hover:not(.date-picker-popup__day--empty) {
    background-color: rgb(var(--accent-primary) / 0.1);
  }

  .date-picker-popup__day--today {
    font-weight: 700;
    color: rgb(var(--accent-primary));
    background-color: rgb(var(--accent-primary) / 0.1);
  }

  .date-picker-popup__day--selected {
    background-color: rgb(var(--accent-primary)) !important;
    color: white !important;
    font-weight: 600;
  }

  .date-picker-popup__day--empty {
    pointer-events: none;
  }

  .date-picker-popup__footer {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
    padding-top: 0.75rem;
    border-top: 1px solid rgb(var(--border-primary));
  }

  .date-picker-popup__today {
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: rgb(var(--accent-primary));
    transition: all 150ms;
  }

  .date-picker-popup__today:hover {
    background-color: rgb(var(--accent-primary) / 0.1);
  }

  /* Mobile responsive */
  @media (max-width: 768px) {
    .date-picker-backdrop {
      align-items: flex-end;
      padding: 0;
      background-color: rgb(0 0 0 / 0.5);
    }

    .date-picker-popup {
      max-width: 100%;
      margin: 0;
      border-radius: 1.5rem 1.5rem 0 0;
      padding-bottom: 1.5rem;
    }

    .date-picker-popup__day {
      width: 100%;
      height: 3rem;
    }
  }

  @media (prefers-reduced-motion: reduce) {
    .date-picker-trigger,
    .date-picker-popup__nav,
    .date-picker-popup__day,
    .date-picker-popup__today {
      transition: none;
    }
  }
</style>