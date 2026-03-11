<script lang="ts">
  import { isOnline, pendingCount, isSyncing } from "../../lib/networkStore";
  import { WifiOff, CloudUpload } from "lucide-svelte";
</script>

<div
  class="flex items-center gap-4 px-6 py-3 rounded-full bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] backdrop-blur-sm"
>
  {#if $isOnline}
    {#if $isSyncing}
      <CloudUpload class="w-4 h-4 text-blue-400 animate-pulse" />
      <span>SINCRONIZANDO...</span>
    {:else if $pendingCount > 0}
      <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
      <span>{$pendingCount} PENDIENTE{$pendingCount > 1 ? "S" : ""}</span>
    {:else}
      <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
      <span>SISTEMA OPERATIVO</span>
    {/if}
  {:else}
    <WifiOff class="w-4 h-4 text-red-400" />
    <span>SIN CONEXIÓN</span>
    {#if $pendingCount > 0}
      <span
        class="ml-1 px-2 py-0.5 rounded-full bg-red-500/20 text-red-400 text-[10px] font-bold"
      >
        {$pendingCount}
      </span>
    {/if}
  {/if}
</div>
