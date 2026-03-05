<script lang="ts">
  import type { Snippet } from "svelte";

  type Variant = "default" | "success" | "warning" | "danger" | "info" | "custom";

  let {
    variant = "default",
    color = "",
    class: className = "",
    children,
  }: {
    variant?: Variant;
    color?: string;
    class?: string;
    children: Snippet;
  } = $props();

  const variantClasses: Record<Variant, string> = {
    default:
      "bg-[rgb(var(--bg-tertiary))] text-[rgb(var(--text-muted))] border border-[rgb(var(--border-primary))]",
    success: "bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20",
    warning: "bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20",
    danger: "bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20",
    info: "bg-blue-500/10 text-blue-600 dark:text-blue-400 border border-blue-500/20",
    custom: "",
  };
</script>

<span
  class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {variantClasses[variant]} {className}"
  style={variant === "custom" && color
    ? `background-color: ${color}15; color: ${color}; border: 1px solid ${color}30;`
    : ""}
>
  {@render children()}
</span>
