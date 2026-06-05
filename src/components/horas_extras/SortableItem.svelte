<script lang="ts">
  import { ChevronUp, ChevronDown as ChevronDownIcon, GripVertical } from '@lucide/svelte'

  interface Props {
    campo: {
      id: string
      label: string
      enabled: boolean
      order: number
    }
    index: number
    totalEnabled: number
    onToggle: (id: string) => void
    onMoveUp: (id: string) => void
    onMoveDown: (id: string) => void
    isDragging?: boolean
  }

  let { campo, index, totalEnabled, onToggle, onMoveUp, onMoveDown, isDragging = false }: Props = $props()
</script>

<div
  class="flex items-center gap-2 p-2 rounded-lg bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] mb-1 transition-opacity {isDragging ? 'opacity-50' : ''}"
>
  <GripVertical class="w-4 h-4 text-[rgb(var(--text-muted))] cursor-grab" />
  <input
    type="checkbox"
    checked={campo.enabled}
    onchange={() => onToggle(campo.id)}
    class="w-4 h-4 rounded border-[rgb(var(--border-primary))] cursor-pointer"
  />
  <span class="flex-1 text-sm text-[rgb(var(--text-primary))]">{campo.label}</span>
  {#if campo.enabled}
    <button
      type="button"
      onclick={() => onMoveUp(campo.id)}
      disabled={index === 0}
      class="p-1 rounded hover:bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-muted))] disabled:opacity-30 disabled:cursor-not-allowed"
    >
      <ChevronUp class="w-4 h-4" />
    </button>
    <button
      type="button"
      onclick={() => onMoveDown(campo.id)}
      disabled={index === totalEnabled - 1}
      class="p-1 rounded hover:bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-muted))] disabled:opacity-30 disabled:cursor-not-allowed"
    >
      <ChevronDownIcon class="w-4 h-4" />
    </button>
  {/if}
</div>