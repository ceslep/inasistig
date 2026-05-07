<script lang="ts">
  interface Props {
    class?: string;
    width?: string;
    height?: string;
    rounded?: 'none' | 'sm' | 'md' | 'lg' | 'full';
  }

  let {
    class: className = '',
    width = '100%',
    height = '1rem',
    rounded = 'md',
  }: Props = $props();

  const roundedClasses = {
    none: 'rounded-none',
    sm: 'rounded',
    md: 'rounded-lg',
    lg: 'rounded-xl',
    full: 'rounded-full',
  };
</script>

<div
  class="animate-pulse {roundedClasses[rounded]} {className}"
  style="width: {width}; height: {height}; background: linear-gradient(90deg, rgb(var(--bg-secondary)) 25%, rgb(var(--card-bg)) 50%, rgb(var(--bg-secondary)) 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite;"
></div>

<style>
  @keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
  }
</style>