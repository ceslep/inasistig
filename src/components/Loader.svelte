<script lang="ts">
  export let size = "medium"; // small, medium, large
  export let message = "";

  const sizeClasses = {
    small: "w-8 h-8",
    medium: "w-16 h-16",
    large: "w-24 h-24",
  };

  $: sizeClass = sizeClasses[size as keyof typeof sizeClasses] || sizeClasses.medium;
</script>

<div class="flex flex-col items-center justify-center gap-4 p-8 animate-in fade-in duration-500">
  <div class="relative {sizeClass}">
    <!-- Sparkle / Glow Effect -->
    <div class="absolute inset-0 bg-indigo-500/20 blur-xl rounded-full animate-pulse"></div>
    
    <!-- Outer Ring -->
    <div class="absolute inset-0 rounded-full border-2 border-indigo-500/20 border-t-indigo-500 animate-spin"></div>
    
    <!-- Inner Ring (Reverse) -->
    <div class="absolute inset-2 rounded-full border-2 border-violet-500/20 border-b-violet-500 animate-[spin_1.5s_linear_infinite_reverse]"></div>
    
    <!-- Center Dot/Core -->
    <div class="absolute inset-[35%] bg-gradient-to-tr from-indigo-600 to-violet-600 rounded-full shadow-[0_0_15px_rgba(99,102,241,0.5)] animate-pulse"></div>
  </div>

  {#if message}
    <p class="text-sm font-medium tracking-wide animate-pulse" style="color: rgb(var(--text-secondary));">
      {message}
    </p>
  {/if}
</div>

<style>
  @keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
  }
</style>
