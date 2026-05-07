<script lang="ts">
  import { Check } from '@lucide/svelte';
  import { scale } from 'svelte/transition';

  interface Props {
    checked: boolean;
    onchange: () => void;
    color: string;
    label?: string;
    text: string;
  }

  let { checked = $bindable(), onchange, color, label, text = $bindable() }: Props = $props();

  const handleClick = () => {
    checked = !checked;
    onchange();
  };

  const handleKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      handleClick();
    }
  };
</script>

<div
  class="relative group flex flex-col p-0 rounded-2xl border transition-all duration-200 shadow-sm hover:shadow-md cursor-pointer"
  role="checkbox"
  aria-checked={checked}
  tabindex="0"
  onclick={handleClick}
  onkeydown={handleKeydown}
  style="
    background-color: {checked ? `${color}10` : `rgb(var(--bg-secondary))`};
    border-color: {checked ? color : `rgb(var(--border-primary))`};
  "
>
  <div class="flex items-start gap-1 p-4">
    <label class="flex-shrink-0 cursor-pointer p-1 mt-1">
      <input
        type="checkbox"
        bind:checked
        onchange={onchange}
        class="hidden"
      />
      <div
        class="w-5 h-5 rounded border flex items-center justify-center transition-all duration-200"
        style="
          border-color: {checked ? color : 'rgb(var(--border-primary))'};
          background-color: {checked ? color : 'transparent'};
          transform: {checked ? 'scale(1)' : 'scale(0.9)'};
        "
      >
        {#if checked}
          <div transition:scale={{ duration: 150, start: 0.5 }}>
            <Check class="w-3 h-3 text-white" />
          </div>
        {/if}
      </div>
    </label>

    <textarea
      bind:value={text}
      rows="6"
      class="w-full bg-transparent border-none focus:ring-0 text-sm font-medium leading-relaxed resize-none p-1 transition-colors min-h-[140px]"
      style="color: rgb(var(--text-primary));"
      placeholder="Escriba aquí..."
      onclick={(e) => e.stopPropagation()}
      onkeydown={(e) => e.stopPropagation()}
    ></textarea>
  </div>

  {#if checked}
    <div
      class="absolute bottom-2 right-2 px-2 py-0.5 rounded-full text-xs font-medium text-white"
      style="background-color: {color}"
      transition:scale={{ duration: 200 }}
    >
      Seleccionada
    </div>
  {/if}
</div>

<style>
  div[role="checkbox"]:focus-within {
    outline: 2px solid rgb(var(--accent-primary));
    outline-offset: 2px;
  }
</style>