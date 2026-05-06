<script lang="ts">
  import { fade } from "svelte/transition";

  interface Props {
    day: number;
    value: string;
    onSelect: (val: string) => void;
    categories: any[];
    isCurrentMonth?: boolean;
    weekday?: string;
  }

  let {
    day,
    value,
    onSelect,
    categories,
    isCurrentMonth = true,
    weekday = "",
  }: Props = $props();

  let showMenu = $state(false);
  let buttonRef = $state<HTMLButtonElement | null>(null);
  let menuPosition = $state<{
    top: number;
    left: number | "auto";
    right: number | "auto";
  }>({
    top: 0,
    left: 0 as number | "auto",
    right: "auto",
  });

  const selectedCategory = $derived(categories.find((c) => c.id === value));

  function calculateMenuPosition() {
    if (!buttonRef) return;
    const rect = buttonRef.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    const menuWidth = 224; // w-56 = 14rem = 224px
    const menuMaxHeight = 288; // max-h-72 = 18rem = 288px

    // Check if we're on mobile or desktop
    const isMobile = viewportWidth < 768;

    if (isMobile) {
      // On mobile, center the menu horizontally
      const leftPosition = Math.max(
        8,
        Math.min(
          viewportWidth - menuWidth - 8,
          (viewportWidth - menuWidth) / 2,
        ),
      );

      // Position below button, but check if it fits
      let topPosition = rect.bottom + 8;

      // If menu would go off bottom, position it above the button
      if (topPosition + menuMaxHeight > viewportHeight - 8) {
        topPosition = Math.max(8, rect.top - menuMaxHeight - 8);
      }

      menuPosition = {
        top: topPosition,
        left: leftPosition,
        right: "auto",
      };
    } else {
      // On desktop, position from right
      const rightPosition = viewportWidth - rect.right;
      let topPosition = rect.bottom + 8;

      // If menu would go off bottom, position it above
      if (topPosition + menuMaxHeight > viewportHeight - 8) {
        topPosition = Math.max(8, rect.top - menuMaxHeight - 8);
      }

      menuPosition = {
        top: topPosition,
        left: "auto",
        right: rightPosition,
      };
    }
  }

  function handleSelect(id: string) {
    onSelect(id);
    showMenu = false;
  }
</script>

<div
  class="relative p-2 border border-slate-100 transition-all duration-300
    {isCurrentMonth
    ? 'bg-white hover:bg-slate-50'
    : 'bg-slate-50 opacity-40 pointer-events-none'}
    {day === 0
    ? 'hidden md:block h-24 md:h-32'
    : 'h-auto md:h-32 min-h-[4rem]'}"
>
  <div
    class="flex md:flex-row justify-between items-center md:items-start gap-4 h-full"
  >
    <!-- Day Number and Weekday (Mobile) -->
    <div class="flex flex-row md:flex-col items-center md:items-start gap-2">
      <span
        class="text-sm md:text-xs font-bold {selectedCategory
          ? 'text-slate-900'
          : 'text-slate-400'}"
      >
        {day !== 0 ? day : ""}
      </span>
      {#if weekday && day !== 0}
        <span
          class="md:hidden text-[10px] font-bold text-slate-400 uppercase tracking-wider"
        >
          {weekday}
        </span>
      {/if}
    </div>

    <!-- Category Display (Center-right on mobile, below on desktop) -->
    <div class="flex-1 md:absolute md:inset-x-2 md:bottom-2">
      {#if selectedCategory}
        <div
          class="p-1 px-3 md:px-1.5 rounded-full md:rounded-lg {selectedCategory.color} flex flex-row md:flex-col items-center justify-center text-center gap-2 md:gap-1 shadow-sm transition-all animate-in fade-in zoom-in duration-300"
          in:fade
        >
          <span class="text-sm md:text-lg">{selectedCategory.icon}</span>
          <span
            class="text-[9px] font-bold text-white leading-tight uppercase line-clamp-1 md:line-clamp-2"
          >
            {selectedCategory.shortLabel || selectedCategory.label}
          </span>
        </div>
      {/if}
    </div>

    <!-- Action Button -->
    {#if isCurrentMonth && day !== 0}
      <button
        bind:this={buttonRef}
        onclick={() => {
          if (!showMenu) {
            calculateMenuPosition();
          }
          showMenu = !showMenu;
        }}
        class="w-8 h-8 md:w-6 md:h-6 rounded-full flex items-center justify-center bg-slate-50 md:bg-transparent border border-slate-100 md:border-none hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition-all active:scale-90"
      >
        <span class="text-xl md:text-lg">+</span>
      </button>
    {/if}
  </div>

  {#if showMenu}
    <div
      class="fixed w-56 bg-white border border-slate-200 rounded-2xl shadow-2xl z-[9999] p-2 space-y-1 max-h-72 overflow-y-auto custom-scrollbar animate-in slide-in-from-top-2 duration-200"
      style="top: {menuPosition.top}px; left: {menuPosition.left === 'auto'
        ? 'auto'
        : menuPosition.left + 'px'}; right: {menuPosition.right === 'auto'
        ? 'auto'
        : menuPosition.right + 'px'};"
      in:fade={{ duration: 150 }}
    >
      <div class="px-3 py-2 border-b border-slate-50 mb-1">
        <p
          class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest"
        >
          Opciones para el día {day}
        </p>
      </div>
      {#each categories as category}
        <button
          onclick={() => handleSelect(category.id)}
          class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-[11px] font-semibold text-slate-600 hover:text-slate-900 hover:bg-slate-50 transition-all active:scale-[0.98]"
        >
          <span class="text-base">{category.icon}</span>
          <span class="flex-1 text-left">{category.label}</span>
          {#if value === category.id}
            <span
              class="w-2 h-2 rounded-full bg-blue-500 shadow-sm shadow-blue-200"
            ></span>
          {/if}
        </button>
      {/each}
      {#if value}
        <div class="h-px bg-slate-100 my-2"></div>
        <button
          onclick={() => handleSelect("")}
          class="w-full text-center py-2.5 text-[10px] text-red-500 hover:text-red-600 hover:bg-red-50/50 rounded-xl font-bold uppercase tracking-wider transition-colors"
        >
          Borrar Registro
        </button>
      {/if}
    </div>
  {/if}
</div>

{#if showMenu}
  <button
    class="fixed inset-0 z-[9998] w-full h-full border-none bg-transparent cursor-default"
    onclick={() => (showMenu = false)}
    aria-label="Cerrar menú"
  ></button>
{/if}
