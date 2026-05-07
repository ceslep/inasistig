<script lang="ts">
  import { Search, ChevronDown, Check, X, Loader2, BookOpen, User, GraduationCap } from '@lucide/svelte';
  import { fade, slide } from 'svelte/transition';
  import { getMateriaIcon, getMateriaColor, getGradoIcon, getDocenteIcon } from '../../lib/materiaIcons';

  type SelectType = 'default' | 'materia' | 'docente' | 'grado';

  interface Option {
    value: string;
    label: string;
    icon?: string;
    color?: string;
  }

  interface Props {
    id: string;
    label: string;
    value: string;
    options: Option[];
    placeholder?: string;
    onchange?: (value: string) => void;
    isLoading?: boolean;
    hasError?: boolean;
    errorMessage?: string;
    disabled?: boolean;
    selectType?: SelectType;
    showSearch?: boolean;
  }

  let {
    id,
    label,
    value = $bindable(),
    options,
    placeholder = "Seleccione una opción",
    onchange,
    isLoading = false,
    hasError = false,
    errorMessage,
    disabled = false,
    selectType = 'default',
    showSearch = true,
  }: Props = $props();

  let isOpen = $state(false);
  let searchTerm = $state("");
  let highlightedIndex = $state(-1);
  let inputRef = $state<HTMLInputElement>();
  let listboxRef = $state<HTMLDivElement>();
  let buttonRef = $state<HTMLButtonElement>();

  // Ocultar buscador si hay menos de 50 opciones
  const shouldShowSearch = $derived(showSearch && options.length >= 50);

  const getOptionIcon = (option: Option, index: number): string => {
    if (option.icon) return option.icon;
    switch (selectType) {
      case 'materia':
        return getMateriaIcon(option.label);
      case 'grado':
        return getGradoIcon(option.label);
      case 'docente':
        return getDocenteIcon();
      default:
        return "📄";
    }
  };

  const getOptionColor = (option: Option): string => {
    if (option.color) return option.color;
    if (selectType === 'materia') {
      return getMateriaColor(option.label);
    }
    return 'rgb(var(--accent-primary))';
  };

  const filteredOptions = $derived(
    searchTerm.trim()
      ? options.filter(opt =>
          opt.label.toLowerCase().includes(searchTerm.toLowerCase())
        )
      : options
  );

  const selectedOption = $derived(
    options.find(opt => opt.value === value)
  );

  const selectedIndex = $derived(
    filteredOptions.findIndex(opt => opt.value === value)
  );

  const selectedIcon = $derived(
    selectedOption ? getOptionIcon(selectedOption, selectedIndex) : null
  );

  const selectedColor = $derived(
    selectedOption ? getOptionColor(selectedOption) : null
  );

  const toggleOpen = () => {
    if (!disabled && !isLoading) {
      isOpen = !isOpen;
      if (isOpen) {
        searchTerm = "";
        highlightedIndex = selectedIndex >= 0 ? selectedIndex : -1;
        setTimeout(() => {
          inputRef?.focus();
          if (highlightedIndex >= 0) {
            const optionEl = listboxRef?.querySelector(`[data-index="${highlightedIndex}"]`);
            optionEl?.scrollIntoView({ block: 'nearest' });
          }
        }, 50);
      }
    }
  };

  const selectOption = (option: Option, index: number) => {
    value = option.value;
    isOpen = false;
    searchTerm = "";
    highlightedIndex = index;
    onchange?.(option.value);
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (!isOpen) {
      if (e.key === 'Enter' || e.key === ' ' || e.key === 'ArrowDown') {
        e.preventDefault();
        isOpen = true;
        searchTerm = "";
        highlightedIndex = selectedIndex >= 0 ? selectedIndex : 0;
        setTimeout(() => {
          inputRef?.focus();
          const optionEl = listboxRef?.querySelector(`[data-index="${highlightedIndex}"]`);
          optionEl?.scrollIntoView({ block: 'nearest' });
        }, 50);
      }
      return;
    }

    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        highlightedIndex = Math.min(highlightedIndex + 1, filteredOptions.length - 1);
        {
          const optionEl = listboxRef?.querySelector(`[data-index="${highlightedIndex}"]`);
          optionEl?.scrollIntoView({ block: 'nearest' });
        }
        break;
      case 'ArrowUp':
        e.preventDefault();
        highlightedIndex = Math.max(highlightedIndex - 1, 0);
        {
          const optionEl = listboxRef?.querySelector(`[data-index="${highlightedIndex}"]`);
          optionEl?.scrollIntoView({ block: 'nearest' });
        }
        break;
      case 'Enter':
        e.preventDefault();
        if (highlightedIndex >= 0 && filteredOptions[highlightedIndex]) {
          selectOption(filteredOptions[highlightedIndex], highlightedIndex);
        }
        break;
      case 'Escape':
        e.preventDefault();
        isOpen = false;
        searchTerm = "";
        break;
      case 'Home':
        e.preventDefault();
        highlightedIndex = 0;
        break;
      case 'End':
        e.preventDefault();
        highlightedIndex = filteredOptions.length - 1;
        break;
    }
  };

  const handleInputKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Escape') {
      isOpen = false;
      searchTerm = "";
    }
  };

  $effect(() => {
    if (!isOpen) {
      searchTerm = "";
      highlightedIndex = -1;
    }
  });

  $effect(() => {
    if (isOpen && highlightedIndex >= 0) {
      setTimeout(() => {
        const optionEl = listboxRef?.querySelector(`[data-index="${highlightedIndex}"]`);
        optionEl?.scrollIntoView({ block: 'nearest' });
      }, 10);
    }
  });
</script>

<div class="space-y-1.5 select-field-container">
  <label
    for={id}
    class="block text-sm font-medium"
    style="color: rgb(var(--text-secondary))"
  >
    {label}
  </label>

  <div class="relative">
    <button
      bind:this={buttonRef}
      type="button"
      onclick={toggleOpen}
      onkeydown={handleKeydown}
      disabled={disabled || isLoading}
      class="
        select-trigger
        {isOpen ? 'select-trigger--open' : ''}
        {hasError ? 'select-trigger--error' : ''}
        {disabled ? 'select-trigger--disabled' : ''}
      "
      style="
        --border-color: {hasError ? '#ef4444' : 'rgb(var(--border-primary))'};
      "
      aria-haspopup="listbox"
      aria-expanded={isOpen}
      aria-label={label}
    >
      {#if isLoading}
        <span class="flex items-center gap-2 select-trigger__content select-trigger__content--loading">
          <Loader2 class="w-4 h-4 animate-spin" style="color: rgb(var(--text-muted))" />
          <span style="color: rgb(var(--text-muted))">Cargando...</span>
        </span>
      {:else if selectedOption}
        <span class="flex items-center gap-3 select-trigger__content">
          {#if selectedIcon}
            <span class="select-trigger__icon-badge" style="background-color: {selectedColor}20; color: {selectedColor}">
              {selectedIcon}
            </span>
          {/if}
          <span class="select-trigger__value">{selectedOption.label}</span>
        </span>
      {:else}
        <span class="select-trigger__placeholder" style="color: rgb(var(--text-muted))">{placeholder}</span>
      {/if}

      <span class="select-trigger__arrow" class:select-trigger__arrow--open={isOpen}>
        <ChevronDown class="w-5 h-5" style="color: rgb(var(--text-muted))" />
      </span>
    </button>

    {#if value && !isOpen && !disabled}
      <button
        onclick={(e) => { e.stopPropagation(); value = ""; onchange?.(""); }}
        class="absolute right-10 top-1/2 -translate-y-1/2 p-1.5 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20 transition-all group"
        aria-label="Limpiar selección"
      >
        <X class="w-4 h-4 text-red-400 group-hover:text-red-600 dark:group-hover:text-red-300 transition-colors" />
      </button>
    {/if}

    {#if isOpen}
      <div
        bind:this={listboxRef}
        transition:slide={{ duration: 200, easing: (t) => t * (2 - t) }}
        class="select-dropdown"
        role="listbox"
      >
        <!-- Contador de opciones -->
        <div class="select-dropdown__count">
          {options.length} opción{options.length !== 1 ? 'es' : ''}
        </div>
        
        {#if shouldShowSearch}
        <div class="select-dropdown__search">
          <Search class="select-dropdown__search-icon" />
          <input
            bind:this={inputRef}
            type="text"
            bind:value={searchTerm}
            onkeydown={handleInputKeydown}
            placeholder="Buscar..."
            class="select-dropdown__search-input"
          />
        </div>
        {/if}

        <div class="select-dropdown__options">
          {#if filteredOptions.length === 0}
            <div class="select-dropdown__empty">
              No se encontraron opciones
            </div>
          {:else}
            {#each filteredOptions as option, index}
              <button
                type="button"
                data-index={index}
                onclick={() => selectOption(option, index)}
                class="
                  select-option
                  {index === highlightedIndex ? 'select-option--highlighted' : ''}
                  {option.value === value ? 'select-option--selected' : ''}
                "
                role="option"
                aria-selected={option.value === value}
              >
                <span class="select-option__content">
                  {#if getOptionIcon(option, index)}
                    <span 
                      class="select-option__icon" 
                      style="background-color: {getOptionColor(option)}20; color: {getOptionColor(option)}"
                    >
                      {getOptionIcon(option, index)}
                    </span>
                  {/if}
                  <span class="select-option__label">{option.label}</span>
                </span>
                {#if option.value === value}
                  <span class="select-option__check" style="color: {getOptionColor(option)}">
                    <Check class="w-4 h-4" />
                  </span>
                {/if}
              </button>
            {/each}
          {/if}
        </div>

        {#if filteredOptions.length > 0}
          <div class="select-dropdown__footer">
            <span class="select-dropdown__count">
              {filteredOptions.length} opción{filteredOptions.length !== 1 ? 'es' : ''}
            </span>
          </div>
        {/if}
      </div>
    {/if}
  </div>

  {#if hasError && errorMessage}
    <p class="text-xs text-red-500 select-field-error" transition:fade>{errorMessage}</p>
  {/if}
</div>

<svelte:window onclick={(e) => {
  if (isOpen && !(e.target as Element).closest('.select-field-container')) {
    isOpen = false;
  }
}} />

<style>
  .select-trigger {
    width: 100%;
    padding: 12px 16px;
    border-radius: 12px;
    border: 1.5px solid var(--border-color);
    background-color: rgb(var(--bg-secondary));
    color: rgb(var(--text-primary));
    font-size: 0.9375rem;
    text-align: left;
    cursor: pointer;
    transition: all 200ms cubic-bezier(0.4, 0, 0.2, 1);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    outline: none;
    position: relative;
  }

  .select-trigger:hover:not(:disabled) {
    border-color: rgb(var(--accent-primary));
    background-color: rgb(var(--bg-primary));
  }

  .select-trigger:focus-visible {
    outline: 2px solid rgb(var(--accent-primary));
    outline-offset: 2px;
  }

  .select-trigger--open {
    border-color: rgb(var(--accent-primary));
    box-shadow: 0 0 0 3px rgb(var(--accent-primary) / 0.15);
  }

  .select-trigger--error {
    border-color: #ef4444 !important;
  }

  .select-trigger--error:focus-visible {
    outline-color: #ef4444;
  }

  .select-trigger--disabled {
    opacity: 0.5;
    cursor: not-allowed;
    background-color: rgb(var(--bg-secondary) / 0.5);
  }

  .select-trigger__content {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    min-width: 0;
  }

  .select-trigger__content--loading {
    opacity: 0.6;
  }

  .select-trigger__value {
    font-weight: 500;
    color: rgb(var(--text-primary));
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .select-trigger__placeholder {
    font-size: 0.9375rem;
  }

  .select-trigger__icon-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .select-trigger__arrow {
    flex-shrink: 0;
    transition: transform 300ms cubic-bezier(0.4, 0, 0.2, 1);
  }

  .select-trigger__arrow--open {
    transform: rotate(180deg);
  }

  .select-dropdown {
    position: absolute;
    z-index: 50;
    width: 100%;
    margin-top: 8px;
    background-color: rgb(var(--card-bg));
    border: 1.5px solid rgb(var(--border-primary));
    border-radius: 12px;
    box-shadow: 0 10px 40px -10px rgb(0 0 0 / 0.25), 0 0 0 1px rgb(var(--border-primary) / 0.1);
    overflow: hidden;
    animation: dropdown-enter 200ms ease-out;
  }

  @keyframes dropdown-enter {
    from {
      opacity: 0;
      transform: translateY(-8px) scale(0.96);
    }
    to {
      opacity: 1;
      transform: translateY(0) scale(1);
    }
  }

  .select-dropdown__search {
    position: relative;
    padding: 12px;
    border-bottom: 1px solid rgb(var(--border-primary));
  }

  .select-dropdown__search-input {
    width: 100%;
    padding: 10px 12px 10px 40px;
    border-radius: 8px;
    border: 1.5px solid rgb(var(--border-primary));
    background-color: rgb(var(--bg-secondary));
    color: rgb(var(--text-primary));
    font-size: 0.875rem;
    outline: none;
    transition: all 200ms;
  }

  .select-dropdown__search-input:focus {
    border-color: rgb(var(--accent-primary));
    box-shadow: 0 0 0 3px rgb(var(--accent-primary) / 0.15);
  }

  .select-dropdown__search-input::placeholder {
    color: rgb(var(--text-muted));
  }

  .select-dropdown__options {
    max-height: 280px;
    overflow-y: auto;
    padding: 4px;
  }

  .select-dropdown__empty {
    padding: 32px 16px;
    text-align: center;
    color: rgb(var(--text-muted));
    font-size: 0.875rem;
  }

  .select-option {
    width: 100%;
    padding: 10px 12px;
    text-align: left;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    border: none;
    background: transparent;
    cursor: pointer;
    transition: all 150ms;
    border-radius: 8px;
    margin: 2px 0;
  }

  .select-option:hover,
  .select-option--highlighted {
    background-color: rgb(var(--bg-secondary));
  }

  .select-option--selected {
    background-color: rgb(var(--accent-primary) / 0.1);
  }

  .select-option--selected:hover,
  .select-option--selected.select-option--highlighted {
    background-color: rgb(var(--accent-primary) / 0.15);
  }

  .select-option__content {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
  }

  .select-option__icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 8px;
    font-size: 1rem;
    flex-shrink: 0;
  }

  .select-option__label {
    color: rgb(var(--text-primary));
    font-size: 0.9375rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .select-option--selected .select-option__label {
    font-weight: 500;
  }

  .select-option__check {
    flex-shrink: 0;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgb(var(--accent-primary));
    color: white;
    animation: check-appear 200ms ease-out;
  }

  @keyframes check-appear {
    from {
      transform: scale(0);
    }
    to {
      transform: scale(1);
    }
  }

  .select-dropdown__footer {
    padding: 10px 12px;
    border-top: 1px solid rgb(var(--border-primary));
    background-color: rgb(var(--bg-secondary) / 0.5);
  }

  .select-dropdown__count {
    font-size: 0.75rem;
    color: rgb(var(--text-muted));
  }

  .select-field-error {
    animation: error-shake 300ms ease-out;
  }

  @keyframes error-shake {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
  }

  .select-dropdown__options::-webkit-scrollbar {
    width: 6px;
  }

  .select-dropdown__options::-webkit-scrollbar-track {
    background: transparent;
  }

  .select-dropdown__options::-webkit-scrollbar-thumb {
    background-color: rgb(var(--border-primary));
    border-radius: 3px;
  }

  .select-dropdown__options::-webkit-scrollbar-thumb:hover {
    background-color: rgb(var(--text-muted));
  }

  /* Contador de opciones */
  .select-dropdown__count {
    padding: 8px 16px;
    font-size: 0.75rem;
    font-weight: 500;
    color: rgb(var(--text-muted));
    border-bottom: 1px solid rgb(var(--border-primary));
    background-color: rgb(var(--bg-secondary) / 0.5);
  }

  /* Media queries para mobile */
  @media (max-width: 768px) {
    .select-dropdown {
      animation: none;
      box-shadow: 0 4px 20px -5px rgb(0 0 0 / 0.2);
    }

    .select-trigger {
      padding: 14px 16px;
    }

    .select-option {
      padding: 12px 16px;
      min-height: 48px;
    }

    .select-dropdown__options {
      max-height: 280px;
    }
  }

  @media (max-width: 480px) {
    .select-dropdown {
      max-height: 60vh;
    }

    .select-trigger {
      font-size: 1rem;
    }
  }
</style>