<script lang="ts">
  import { isOnline, pendingCount, isSyncing } from "../../lib/networkStore";
  import { WifiOff, CloudUpload, Loader2 } from '@lucide/svelte';
</script>

<div class="flex items-center gap-3 px-4 py-2 rounded-full bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))]">
  {#if $isOnline}
    {#if $isSyncing}
      <Loader2 class="w-4 h-4 text-blue-400 animate-spin" />
      <span class="text-sm">SINCRONIZANDO...</span>
    {:else if $pendingCount > 0}
      <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
      <span class="text-sm font-medium text-amber-600">{$pendingCount} PENDIENTE{$pendingCount > 1 ? 'S' : ''}</span>
    {:else}
      <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
      <span class="text-sm">SISTEMA OPERATIVO</span>
    {/if}
  {:else}
    <WifiOff class="w-4 h-4 text-red-400" />
    <span class="text-sm">SIN CONEXIÓN</span>
    {#if $pendingCount > 0}
      <span class="px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 text-xs font-bold">{$pendingCount}</span>
    {/if}
  {/if}
</div>