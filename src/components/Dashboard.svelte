<script lang="ts">
  import { onMount } from "svelte";

  import { fade, fly } from "svelte/transition";
  import asistenciaHero from "../assets/asistencia_hero.png";
  import anotadorHero from "../assets/anotador_hero.png";
  import diarioHero from "../assets/diario_hero.png";
  import logoEie from "../assets/eie.png";
  import { theme, type Theme } from "../lib/themeStore";

  export let onSelect: (view: string) => void;

  let mounted = false;

  onMount(() => {
    mounted = true;
  });

  const toggleTheme = () => {
    theme.update((t) => {
      if (t === "light") return "dim";
      if (t === "dim") return "dark";
      return "light";
    });
  };

  const modules = [
    {
      id: "inasistencia",
      title: "Registro Diario",
      subtitle: "Gestión de Asistencia",
      description:
        "Control preciso de inasistencias y novedades diarias del aula.",
      image: asistenciaHero,
      color: "from-blue-600/20 to-indigo-600/20",
      accent: "blue",
      tag: "Operativo",
    },
    {
      id: "anotador",
      title: "Anotador de Clase",
      subtitle: "Seguimiento Ágil",
      description:
        "Registro dinámico de incidencias y avances pedagógicos por sesión.",
      image: anotadorHero,
      color: "from-purple-600/20 to-pink-600/20",
      accent: "purple",
      tag: "Académico",
    },
    {
      id: "diario",
      title: "Diario de Campo",
      subtitle: "Reflexión Docente",
      description:
        "Espacio para la reflexión profunda y documentación pedagógica.",
      image: diarioHero,
      color: "from-emerald-600/20 to-teal-600/20",
      accent: "emerald",
      tag: "Estratégico",
    },
  ];
</script>

<div
  class="min-h-screen w-full flex flex-col items-center p-6 lg:p-12 transition-colors duration-700 bg-[rgb(var(--bg-primary))] text-[rgb(var(--text-primary))]"
>
  <!-- Decorative background elements -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
    <div
      class="absolute -top-[20%] -left-[10%] w-[60%] h-[60%] bg-indigo-500/5 blur-[120px] rounded-full animate-pulse"
    ></div>
    <div
      class="absolute -bottom-[20%] -right-[10%] w-[60%] h-[60%] bg-purple-500/5 blur-[120px] rounded-full animate-pulse"
      style="animation-delay: 2s"
    ></div>
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-[0.02] mix-blend-overlay"
    ></div>
  </div>

  {#if mounted}
    <!-- Content Header -->
    <header
      class="max-w-7xl w-full mb-6 lg:mb-8 text-center relative"
      in:fly={{ y: -20, duration: 800, delay: 100 }}
    >
      <!-- Theme Switcher -->
      <div class="absolute right-0 top-0">
        <button
          on:click={toggleTheme}
          class="p-3 rounded-2xl bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] hover:border-[rgb(var(--accent-primary))] transition-all duration-300 group"
          aria-label="Toggle Theme"
        >
          {#if $theme === "light"}
            <svg
              class="w-6 h-6 text-amber-500"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 16.95l.707.707M7.636 7.636l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"
              />
            </svg>
          {:else if $theme === "dim"}
            <svg
              class="w-6 h-6 text-indigo-400"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
              />
            </svg>
          {:else}
            <svg
              class="w-6 h-6 text-indigo-500"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M16.95 16.95l.707.707M7.636 7.636l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"
              />
            </svg>
          {/if}
        </button>
      </div>

      <div class="inline-block mb-4 relative">
        <div
          class="absolute inset-0 bg-indigo-500/20 blur-2xl rounded-full"
        ></div>
        <img
          src={logoEie}
          alt="EIE Logo"
          class="relative h-12 md:h-16 object-contain hover:scale-110 transition-transform duration-700 ease-out cursor-none"
        />
      </div>

      <div class="space-y-1">
        <h1 class="text-3xl md:text-5xl font-black tracking-tight">
          <span
            class="bg-gradient-to-b from-[rgb(var(--text-primary))] to-[rgb(var(--text-secondary))] bg-clip-text text-transparent"
          >
            Ecosistema Digital
          </span>
          <span class="text-[rgb(var(--accent-primary))]">EIE</span>
        </h1>
        <p
          class="text-base md:text-lg text-[rgb(var(--text-muted))] max-w-xl mx-auto font-medium"
        >
          Potenciando la excelencia pedagógica a través de herramientas
          inteligentes y diseño centrado en el docente.
        </p>
      </div>
    </header>

    <!-- Bento Grid Implementation -->
    <div
      class="max-w-7xl w-full grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8"
    >
      {#each modules as module, i}
        <button
          on:click={() => onSelect(module.id)}
          class="group relative flex flex-col justify-between overflow-hidden rounded-[2.5rem] border border-[rgb(var(--card-border))] bg-[rgb(var(--card-bg))] backdrop-blur-xl transition-all duration-500 hover:border-[rgb(var(--accent-primary))]/30 hover:bg-[rgb(var(--bg-secondary))] p-1"
          in:fly={{ y: 40, duration: 800, delay: 200 + i * 150 }}
        >
          <!-- Inner Glow / Highlight -->
          <div
            class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/10 to-transparent"
          ></div>

          <div class="relative p-8 flex flex-col h-full">
            <!-- Header section of the card -->
            <div class="flex justify-between items-start mb-12">
              <div class="flex flex-col gap-1">
                <span
                  class="text-[10px] font-bold uppercase tracking-[0.2em] text-[rgb(var(--accent-primary))]"
                >
                  {module.subtitle}
                </span>
                <h2
                  class="text-2xl font-bold tracking-tight text-[rgb(var(--text-primary))] group-hover:text-[rgb(var(--accent-primary))] transition-colors duration-300"
                >
                  {module.title}
                </h2>
              </div>

              <!-- Tag/Badge -->
              <span
                class="px-3 py-1 rounded-full text-[10px] font-bold bg-[rgb(var(--bg-tertiary))] border border-[rgb(var(--border-primary))] text-[rgb(var(--text-muted))] uppercase tracking-wider"
              >
                {module.tag}
              </span>
            </div>

            <!-- Content section -->
            <div class="flex flex-col gap-6 mt-auto">
              <!-- Futuristic Visual Element (Smaller Image) -->
              <div
                class="relative w-full aspect-video rounded-3xl overflow-hidden bg-black/20 border border-white/5"
              >
                <div
                  class="absolute inset-0 bg-gradient-to-br {module.color} mix-blend-overlay z-10"
                ></div>
                <img
                  src={module.image}
                  alt={module.title}
                  class="w-3/4 h-3/4 mx-auto object-cover grayscale-[0.5] group-hover:grayscale-0 group-hover:scale-110 transition-all duration-700 ease-in-out opacity-60 group-hover:opacity-100"
                />

                <!-- Overlay details -->
                <div
                  class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-500 z-20"
                >
                  <div
                    class="p-3 rounded-full bg-white/10 backdrop-blur-md border border-white/20"
                  >
                    <svg
                      class="w-6 h-6 text-white"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M14 5l7 7m0 0l-7 7m7-7H3"
                      />
                    </svg>
                  </div>
                </div>
              </div>

              <p
                class="text-sm font-medium leading-relaxed text-[rgb(var(--text-muted))] line-clamp-2"
              >
                {module.description}
              </p>
            </div>

            <!-- Bottom Action -->
            <div class="mt-8 flex items-center justify-between">
              <div class="flex -space-x-2">
                <!-- Decorative user bubbles or stats placeholders -->
                <div
                  class="w-6 h-6 rounded-full border border-black bg-indigo-500/20"
                ></div>
                <div
                  class="w-6 h-6 rounded-full border border-black bg-purple-500/20"
                ></div>
              </div>

              <div
                class="text-[10px] font-bold text-[rgb(var(--text-muted))] tracking-[0.1em] group-hover:text-[rgb(var(--accent-primary))] transition-colors uppercase"
              >
                Explorar &rarr;
              </div>
            </div>
          </div>

          <!-- Hover Gradient Background -->
          <div
            class="absolute inset-0 bg-gradient-to-br {module.color} opacity-0 group-hover:opacity-[0.03] transition-opacity duration-500 pointer-events-none"
          ></div>
        </button>
      {/each}
    </div>

    <!-- Footer / Status Bar -->
    <footer
      class="mt-20 flex flex-col md:flex-row items-center gap-8 text-[rgb(var(--text-muted))] text-xs font-bold tracking-widest uppercase"
      in:fade={{ duration: 1000, delay: 800 }}
    >
      <div
        class="flex items-center gap-4 px-6 py-3 rounded-full bg-[rgb(var(--bg-secondary))] border border-[rgb(var(--border-primary))] backdrop-blur-sm"
      >
        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
        SISTEMA OPERATIVO
      </div>
      <div
        class="hidden md:block w-px h-4 bg-[rgb(var(--border-primary))]"
      ></div>
      <div>v2.0.4 PLATINUM</div>
      <div
        class="hidden md:block w-px h-4 bg-[rgb(var(--border-primary))]"
      ></div>
      <div>2026 EIE DIGITAL</div>
    </footer>
  {/if}
</div>

<style>
  :global(body) {
    background: rgb(var(--bg-primary));
    color: rgb(var(--text-primary));
    overflow-x: hidden;
  }

  /* Custom glass effect */
  .backdrop-blur-xl {
    backdrop-filter: blur(24px) saturate(180%);
    -webkit-backdrop-filter: blur(24px) saturate(180%);
  }

  /* Smooth scroll */
  :global(html) {
    scroll-behavior: smooth;
  }

  /* Animation for background pulses */
  @keyframes pulse {
    0%,
    100% {
      opacity: 0.3;
      transform: scale(1);
    }
    50% {
      opacity: 0.5;
      transform: scale(1.1);
    }
  }

  /* Custom utilities if needed */
  .tracking-widest {
    letter-spacing: 0.1em;
  }
</style>
