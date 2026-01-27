<script lang="ts">
  import { onMount } from "svelte";
  export let onBack: () => void;
  let isDarkMode = true;

  onMount(() => {
    isDarkMode = document.documentElement.classList.contains("dark");
  });
</script>

<div
  class="min-h-screen p-6 sm:p-12 transition-colors duration-500 {isDarkMode
    ? 'bg-[#09090b] text-white'
    : 'bg-slate-50 text-slate-900'}"
>
  <div class="max-w-6xl mx-auto">
    <button
      on:click={onBack}
      class="group mb-12 flex items-center gap-3 px-6 py-3 rounded-2xl border transition-all duration-300 hover:scale-105 active:scale-95
             {isDarkMode
        ? 'bg-white/5 border-white/10 hover:bg-white/10'
        : 'bg-white border-slate-200 hover:shadow-lg'}"
    >
      <svg
        class="w-5 h-5 transition-transform group-hover:-translate-x-1"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M10 19l-7-7m0 0l7-7m-7 7h18"
        />
      </svg>
      <span class="font-semibold text-sm">Volver al Dashboard</span>
    </button>

    <div
      class="relative p-16 border rounded-[3rem] text-center overflow-hidden animate-in fade-in slide-in-from-bottom-8 duration-700
             {isDarkMode
        ? 'bg-white/[0.03] border-white/10'
        : 'bg-white border-slate-200 shadow-xl shadow-slate-200/50'}"
    >
      <!-- Background Glow -->
      <div
        class="absolute -top-[20%] -right-[20%] w-[50%] h-[50%] bg-emerald-500/10 blur-[100px] rounded-full"
      ></div>

      <div class="relative z-10">
        <div
          class="w-24 h-24 bg-emerald-500/10 text-emerald-500 rounded-3xl flex items-center justify-center mx-auto mb-8 border border-emerald-500/20"
        >
          <svg
            class="w-12 h-12"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"
            />
          </svg>
        </div>
        <h1 class="text-5xl font-bold mb-6 tracking-tight">Diario de Campo</h1>
        <p class="text-xl opacity-60 max-w-xl mx-auto leading-relaxed">
          Un espacio diseñado para capturar la esencia de tus observaciones y
          reflexiones pedagógicas cotidianas.
        </p>
        <div
          class="mt-12 inline-flex items-center gap-2 px-4 py-2 bg-emerald-500/10 text-emerald-500 rounded-full text-sm font-bold uppercase tracking-widest border border-emerald-500/20"
        >
          <span class="relative flex h-2 w-2">
            <span
              class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"
            ></span>
            <span
              class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"
            ></span>
          </span>
          En Desarrollo
        </div>
      </div>
    </div>
  </div>
</div>

<style>
  @keyframes animate-in {
    from {
      opacity: 0;
      transform: translateY(2rem);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }
</style>
