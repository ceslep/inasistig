<script lang="ts">
  import { fly, fade } from "svelte/transition";
  import { CheckCircle, XCircle, AlertTriangle, Info, X } from "lucide-svelte";

  type ToastType = "success" | "error" | "warning" | "info";

  interface ToastItem {
    id: number;
    type: ToastType;
    message: string;
    duration?: number;
  }

  let toasts = $state<ToastItem[]>([]);
  let nextId = 0;

  const iconMap = {
    success: CheckCircle,
    error: XCircle,
    warning: AlertTriangle,
    info: Info,
  };

  const colorMap: Record<ToastType, string> = {
    success: "bg-emerald-500",
    error: "bg-red-500",
    warning: "bg-amber-500",
    info: "bg-blue-500",
  };

  function removeToast(id: number) {
    toasts = toasts.filter((t) => t.id !== id);
  }

  export function show(
    type: ToastType,
    message: string,
    duration: number = 4000,
  ) {
    const id = nextId++;
    toasts = [...toasts, { id, type, message, duration }];
    if (duration > 0) {
      setTimeout(() => removeToast(id), duration);
    }
  }
</script>

<div class="fixed top-4 right-4 z-[100] flex flex-col gap-3 pointer-events-none">
  {#each toasts as toast (toast.id)}
    <div
      class="pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border border-white/10 backdrop-blur-xl bg-[rgb(var(--card-bg))]/95 max-w-sm"
      in:fly={{ x: 100, duration: 300 }}
      out:fade={{ duration: 200 }}
    >
      <div
        class="flex-shrink-0 w-8 h-8 rounded-lg {colorMap[toast.type]} flex items-center justify-center"
      >
        {#if toast.type === "success"}
          <CheckCircle class="w-4 h-4 text-white" />
        {:else if toast.type === "error"}
          <XCircle class="w-4 h-4 text-white" />
        {:else if toast.type === "warning"}
          <AlertTriangle class="w-4 h-4 text-white" />
        {:else}
          <Info class="w-4 h-4 text-white" />
        {/if}
      </div>
      <p
        class="text-sm font-medium flex-1"
        style="color: rgb(var(--text-primary));"
      >
        {toast.message}
      </p>
      <button
        onclick={() => removeToast(toast.id)}
        class="flex-shrink-0 p-1 rounded-lg hover:bg-[rgb(var(--bg-secondary))] transition-colors"
      >
        <X class="w-4 h-4" style="color: rgb(var(--text-muted));" />
      </button>
    </div>
  {/each}
</div>
