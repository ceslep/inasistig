<script lang="ts">
  import { onMount } from 'svelte';
  import { Sparkles, X } from '@lucide/svelte';

  type ColorTheme = 'blue' | 'green' | 'purple' | 'emerald';

  interface Props {
    featureMessage: string;
    description: string;
    onTryNow: () => void;
    onDismiss: () => void;
    targetSelector?: string;
    showPopup: boolean;
    colorTheme?: ColorTheme;
  }

  let {
    featureMessage,
    description,
    onTryNow,
    onDismiss,
    targetSelector,
    showPopup,
    colorTheme = 'blue',
  }: Props = $props();

  let popupPosition = $state({ top: 0, left: 0 });

  const popupClasses = {
    arrowBorder: {
      blue: 'border-t-blue-600 dark:border-t-blue-400',
      green: 'border-t-green-600 dark:border-t-green-400',
      purple: 'border-t-purple-600 dark:border-t-purple-400',
      emerald: 'border-t-emerald-600 dark:border-t-emerald-400',
    },
    bgGradientFrom: {
      blue: 'from-blue-600 dark:from-blue-700',
      green: 'from-green-600 dark:from-green-700',
      purple: 'from-purple-600 dark:from-purple-700',
      emerald: 'from-emerald-600 dark:from-emerald-700',
    },
    bgGradientTo: {
      blue: 'to-indigo-600 dark:to-indigo-700',
      green: 'to-emerald-600 dark:to-emerald-700',
      purple: 'to-fuchsia-600 dark:to-fuchsia-700',
      emerald: 'to-teal-600 dark:to-teal-700',
    },
    borderColor: {
      blue: 'border-blue-400 dark:border-blue-500',
      green: 'border-green-400 dark:border-green-500',
      purple: 'border-purple-400 dark:border-purple-500',
      emerald: 'border-emerald-400 dark:border-emerald-500',
    },
    buttonTextColor: {
      blue: 'text-blue-600',
      green: 'text-green-600',
      purple: 'text-purple-600',
      emerald: 'text-emerald-600',
    },
    buttonHoverBg: {
      blue: 'hover:bg-blue-50',
      green: 'hover:bg-green-50',
      purple: 'hover:bg-purple-50',
      emerald: 'hover:bg-emerald-50',
    },
    descriptionColor: {
      blue: 'text-blue-100',
      green: 'text-green-100',
      purple: 'text-purple-100',
      emerald: 'text-emerald-100',
    },
  } as const;

  function updatePopupPosition() {
    if (typeof window === 'undefined') return;
    if (targetSelector) {
      const targetElement = document.querySelector(targetSelector);
      if (targetElement) {
        const rect = targetElement.getBoundingClientRect();
        popupPosition = {
          top: rect.bottom + 10,
          left: rect.left + rect.width / 2,
        };
        return;
      }
    }
    popupPosition = {
      top: window.innerHeight / 2 - 100,
      left: window.innerWidth / 2,
    };
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape' && showPopup) {
      onDismiss();
    }
  }

  onMount(() => {
    updatePopupPosition();
    window.addEventListener('resize', updatePopupPosition);
    return () => {
      window.removeEventListener('resize', updatePopupPosition);
    };
  });

  $effect(() => {
    if (showPopup) {
      updatePopupPosition();
    }
  });
</script>

<svelte:window onkeydown={handleKeydown} />

{#if showPopup}
  <div
    class="fixed inset-0 bg-black/30 z-40"
    role="presentation"
    onclick={onDismiss}
  ></div>

  <div
    class="fixed z-50 animate-bounce-in"
    style="top: {popupPosition.top}px; left: {popupPosition.left}px; transform: translateX(-50%);"
    role="dialog"
    aria-modal="true"
    aria-label={featureMessage}
  >
    {#if targetSelector}
    <div class="relative">
      <div
        class="absolute w-0 h-0 border-l-8 border-r-8 border-t-8 border-l-transparent border-r-transparent {popupClasses
          .arrowBorder[colorTheme]}"
        style="top: -8px; left: 50%; transform: translateX(-50%);"
      ></div>
    </div>
    {/if}

    <div
      class="bg-gradient-to-r {popupClasses.bgGradientFrom[
          colorTheme
        ]} {popupClasses.bgGradientTo[
          colorTheme
        ]} text-white p-4 rounded-xl shadow-2xl border {popupClasses
          .borderColor[
          colorTheme
        ]} min-w-[280px] max-w-[calc(100vw-20px)] md:max-w-[320px]"
      >
        <div class="flex items-center gap-2 mb-3">
          <div class="p-1.5 bg-white/20 rounded-lg">
            <Sparkles class="w-5 h-5" />
          </div>
          <span
            class="inline-block px-2 py-0.5 text-xs font-bold rounded-full bg-white/25 animate-pulse"
          >
            ¡NUEVO!
          </span>
        </div>

        <h3 class="font-bold text-base mb-2">
          {featureMessage}
        </h3>

        <p class="text-sm {popupClasses.descriptionColor[colorTheme]} mb-3">
          {description}
        </p>

        <div class="flex gap-2">
          <button
            onclick={onTryNow}
            class="px-3 py-2 rounded-lg text-sm bg-white {popupClasses
              .buttonTextColor[colorTheme]} font-semibold {popupClasses
              .buttonHoverBg[colorTheme]} transition-colors cursor-pointer"
          >
            Probar ahora
          </button>
          <button
            onclick={onDismiss}
            class="px-3 py-2 rounded-lg text-sm hover:bg-white/20 transition-colors cursor-pointer"
            aria-label="Cerrar popup"
          >
            Más tarde
          </button>
        </div>

        <button
          onclick={onDismiss}
          class="absolute top-2 right-2 p-1 rounded-lg hover:bg-white/20 transition-colors cursor-pointer"
          aria-label="Cerrar notificación"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
  </div>
{/if}

<style>
  @keyframes bounce-in {
    0% {
      opacity: 0;
      transform: translateX(-50%) translateY(-20px) scale(0.9);
    }
    60% {
      opacity: 1;
      transform: translateX(-50%) translateY(2px) scale(1.02);
    }
    100% {
      opacity: 1;
      transform: translateX(-50%) translateY(0) scale(1);
    }
  }
  .animate-bounce-in {
    animation: bounce-in 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
  }
</style>
