<script lang="ts">
  import { onMount, onDestroy } from "svelte";
  // The message for the feature. This should be passed as a prop.
  export let featureMessage: string;
  // The main description for the feature.
  export let description: string;
  // Callback for when the "Try now" button is clicked.
  export let onTryNow: () => void;
  // Callback for when the "Later" or close button is clicked.
  export let onDismiss: () => void;
  // Selector for the target element the popup should point to.
  export let targetSelector: string | undefined = undefined; // Made optional
  // Boolean to control the visibility of the popup.
  export let showPopup: boolean;
  export let colorTheme: "blue" | "green" | "purple" | "emerald" = "blue"; // Default to blue

  let popupPosition = { top: 0, left: 0 };

  // Reactive styles based on colorTheme
  $: popupClasses = {
    arrowBorder: {
      blue: "border-t-blue-600 dark:border-t-blue-400",
      green: "border-t-green-600 dark:border-t-green-400",
      purple: "border-t-purple-600 dark:border-t-purple-400",
      emerald: "border-t-emerald-600 dark:border-t-emerald-400", // Added emerald
    },
    bgGradientFrom: {
      blue: "from-blue-600 dark:from-blue-700",
      green: "from-green-600 dark:from-green-700",
      purple: "from-purple-600 dark:from-purple-700",
      emerald: "from-emerald-600 dark:from-emerald-700", // Added emerald
    },
    bgGradientTo: {
      blue: "to-indigo-600 dark:to-indigo-700",
      green: "to-emerald-600 dark:to-emerald-700",
      purple: "to-fuchsia-600 dark:to-fuchsia-700",
      emerald: "to-teal-600 dark:to-teal-700", // Added emerald
    },
    borderColor: {
      blue: "border-blue-400 dark:border-blue-500",
      green: "border-green-400 dark:border-green-500",
      purple: "border-purple-400 dark:border-purple-500",
      emerald: "border-emerald-400 dark:border-emerald-500", // Added emerald
    },
    buttonTextColor: {
      blue: "text-blue-600",
      green: "text-green-600",
      purple: "text-purple-600",
      emerald: "text-emerald-600", // Added emerald
    },
    buttonHoverBg: {
      blue: "hover:bg-blue-50",
      green: "hover:bg-green-50",
      purple: "hover:bg-purple-50",
      emerald: "hover:bg-emerald-50", // Added emerald
    },
    descriptionColor: {
      blue: "text-blue-100",
      green: "text-green-100",
      purple: "text-purple-100",
      emerald: "text-emerald-100", // Added emerald
    }
  };

  function updatePopupPosition() {
    if (typeof window !== "undefined" && targetSelector) { // Check if targetSelector is defined
      const targetElement = document.querySelector(targetSelector);
      if (targetElement) {
        const rect = targetElement.getBoundingClientRect();
        // Position the popup above and centered on the target element
        popupPosition = {
          top: rect.bottom + 10, // 10px below the target
          left: rect.left + rect.width / 2,
        };
      }
    } else { // Default position if no targetSelector
      popupPosition = {
        top: window.innerHeight / 2 - 100, // Center vertically
        left: window.innerWidth / 2,      // Center horizontally
      };
    }
  }

  onMount(() => {
    // Initial positioning
    updatePopupPosition();
    // Update position on window resize
    window.addEventListener("resize", updatePopupPosition);
    return () => {
      window.removeEventListener("resize", updatePopupPosition);
    };
  });

  // Reactive statement to update position if showPopup changes (e.g., target appears)
  $: if (showPopup) {
    updatePopupPosition();
  }
</script>

{#if showPopup}
  <!-- Overlay oscuro para destacar el botón -->
  <div
    class="fixed inset-0 bg-black/30 z-40"
    style="pointer-events: none;"
  ></div>

  <!-- Popup que señala al botón de filtros -->
  <div
    class="fixed z-50 animate-bounce-in"
    style="top: {popupPosition.top}px; left: {popupPosition.left}px; transform: translateX(-50%);"
  >
    <!-- Flecha apuntando hacia abajo (al botón) -->
    {#if targetSelector}
    <div class="relative">
      <div
        class="absolute w-0 h-0 border-l-8 border-r-8 border-t-8 border-l-transparent border-r-transparent {popupClasses
          .arrowBorder[colorTheme]}"
        style="top: -8px; left: 50%; transform: translateX(-50%);"
      ></div>
    </div>
    {/if}

    <!-- Contenido del popup -->
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
        <!-- Header con ícono y texto NUEVO -->
        <div class="flex items-center gap-2 mb-3">
          <div class="p-1.5 bg-white/20 rounded-lg">
            <svg
              class="w-5 h-5"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
              />
            </svg>
          </div>
          <span
            class="inline-block px-2 py-0.5 text-xs font-bold rounded-full bg-white/25 animate-pulse"
          >
            ¡NUEVO!
          </span>
        </div>

        <!-- Mensaje principal -->
        <h3 class="font-bold text-base mb-2">
          {featureMessage}
        </h3>

        <!-- Descripción -->
        <p class="text-sm {popupClasses.descriptionColor[colorTheme]} mb-3">
          {description}
        </p>

        <!-- Botones de acción -->
        <div class="flex gap-2">
          <button
            on:click={onTryNow}
            class="px-3 py-2 rounded-lg text-sm bg-white {popupClasses
              .buttonTextColor[colorTheme]} font-semibold {popupClasses
              .buttonHoverBg[colorTheme]} transition-colors"
          >
            Probar ahora
          </button>
          <button
            on:click={onDismiss}
            class="px-3 py-2 rounded-lg text-sm hover:bg-white/20 transition-colors"
            aria-label="Cerrar popup"
          >
            Más tarde
          </button>
        </div>

        <!-- Botón cerrar -->
        <button
          on:click={onDismiss}
          class="absolute top-2 right-2 p-1 rounded-lg hover:bg-white/20 transition-colors"
          aria-label="Cerrar notificación"
        >
          <svg
            class="w-4 h-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>
  </div>{/if}

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