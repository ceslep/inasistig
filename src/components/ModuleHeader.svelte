<script lang="ts">
  import type { Snippet } from 'svelte'

  import { ArrowLeft, Sun, Moon, CloudMoon } from '@lucide/svelte'

  import { theme, type Theme } from '../lib/themeStore'
  import UserAvatar from './UserAvatar.svelte'

  interface Props {
    title: string
    subtitle?: string
    onBack: () => void
    actions?: Snippet
  }

  let { title, subtitle, onBack, actions }: Props = $props()

  const toggleTheme = () => {
    theme.update((t: Theme) => {
      if (t === 'light') return 'dim'
      if (t === 'dim') return 'dark'
      return 'light'
    })
  }
</script>

<header
  class="sticky top-0 z-30 w-full border-b border-[rgb(var(--border-primary))] bg-[rgb(var(--bg-primary))]/95 backdrop-blur-sm transition-colors duration-200"
>
  <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3 sm:px-6">
    <!-- Back button -->
    <button
      onclick={onBack}
      class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] transition-colors duration-200 cursor-pointer"
      aria-label="Volver al dashboard"
    >
      <ArrowLeft class="h-5 w-5 text-[rgb(var(--text-primary))]" />
    </button>

    <!-- Title -->
    <div class="min-w-0 flex-1">
      <h1 class="truncate text-lg font-bold text-[rgb(var(--text-primary))] sm:text-xl">
        {title}
      </h1>
      {#if subtitle}
        <p class="truncate text-xs font-medium text-[rgb(var(--text-muted))]">
          {subtitle}
        </p>
      {/if}
    </div>

    <!-- Module-specific actions -->
    {#if actions}
      <div class="flex items-center gap-2">
        {@render actions()}
      </div>
    {/if}

    <!-- Theme toggle -->
    <button
      onclick={toggleTheme}
      class="flex min-h-[44px] min-w-[44px] items-center justify-center rounded-xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] transition-colors duration-200 cursor-pointer"
      aria-label="Cambiar tema"
    >
      {#if $theme === 'light'}
        <Sun class="h-5 w-5 text-amber-500" />
      {:else if $theme === 'dim'}
        <CloudMoon class="h-5 w-5 text-indigo-400" />
      {:else}
        <Moon class="h-5 w-5 text-indigo-500" />
      {/if}
    </button>

    <!-- User avatar -->
    <UserAvatar />
  </div>
</header>
