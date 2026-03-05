<script lang="ts">
  import type { Snippet } from "svelte";

  type Variant = "default" | "glass" | "outlined";

  let {
    variant = "default",
    padding = "p-6",
    class: className = "",
    children,
    ...rest
  }: {
    variant?: Variant;
    padding?: string;
    class?: string;
    children: Snippet;
    [key: string]: any;
  } = $props();

  const variantClasses: Record<Variant, string> = {
    default:
      "bg-[rgb(var(--card-bg))] border border-[rgb(var(--card-border))] shadow-sm",
    glass:
      "bg-[rgb(var(--card-bg))]/60 backdrop-blur-xl border border-[rgb(var(--card-border))] shadow-lg",
    outlined:
      "border border-[rgb(var(--border-primary))] bg-transparent",
  };
</script>

<div
  class="rounded-2xl transition-colors duration-200 {variantClasses[variant]} {padding} {className}"
  {...rest}
>
  {@render children()}
</div>
