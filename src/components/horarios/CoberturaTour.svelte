<script lang="ts">
  import { tick } from "svelte";
  import { fade, scale } from "svelte/transition";
  import { X, ChevronLeft, ChevronRight, Check, HelpCircle } from "@lucide/svelte";

  export interface TourPaso {
    selector: string;
    titulo: string;
    descripcion: string;
    icono: typeof HelpCircle;
    color: string;
    step?: number;
  }

  let {
    pasos,
    onClose,
    onIrAStep,
    stepInicial = 0,
    onNoMostrar,
  }: {
    pasos: TourPaso[];
    onClose: () => void;
    onIrAStep?: (step: number) => void;
    stepInicial?: number;
    onNoMostrar?: () => void;
  } = $props();

  let indice = $state(stepInicial);
  let noMostrarMas = $state(false);
  const pasoActual = $derived(pasos[indice]);
  const esPrimero = $derived(indice === 0);
  const esUltimo = $derived(indice === pasos.length - 1);
  const IconoActual = $derived(pasoActual?.icono ?? HelpCircle);

  let rect = $state<{ top: number; left: number; width: number; height: number } | null>(null);

  const PADDING = 8;
  const GAP = 12;
  const TOOLTIP_W = 320;
  const TOOLTIP_H_EST = 220;

  async function medirElemento() {
    await tick();
    const el = document.querySelector(pasoActual?.selector ?? "");
    if (!el) { rect = null; return; }
    const r = el.getBoundingClientRect();
    rect = { top: r.top, left: r.left, width: r.width, height: r.height };
  }

  async function localizar() {
    const paso = pasoActual;
    if (!paso) return;
    await tick();
    const el = document.querySelector(paso.selector);
    if (el) el.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
    await tick();
    medirElemento();
  }

  $effect(() => {
    pasoActual;
    indice;
    setTimeout(() => localizar(), 50);
  });

  $effect(() => {
    if (stepInicial !== indice) {
      indice = stepInicial;
    }
  });

  function siguiente() {
    if (esUltimo) {
      onClose();
    } else {
      const sigPaso = pasos[indice + 1];
      indice += 1;
      const sigStep = sigPaso?.step;
      if (sigStep != null && onIrAStep) {
        onIrAStep(sigStep);
      }
    }
  }

  function anterior() {
    if (!esPrimero) indice -= 1;
  }

  function saltarAPaso(i: number) {
    if (i === indice) return;
    const pasoDestino = pasos[i];
    indice = i;
    const stepDestino = pasoDestino?.step;
    if (stepDestino && onIrAStep) {
      onIrAStep(stepDestino);
    }
  }

  function onNoMostrarMasChange() {
    if (noMostrarMas && onNoMostrar) {
      onNoMostrar();
    }
  }

  function onKeydown(e: KeyboardEvent) {
    if (e.key === "Escape") {
      e.preventDefault();
      onClose();
    } else if (e.key === "ArrowRight" || e.key === "Enter") {
      e.preventDefault();
      siguiente();
    } else if (e.key === "ArrowLeft") {
      e.preventDefault();
      anterior();
    }
  }

  const tooltipPos = $derived.by(() => {
    if (typeof window === "undefined") {
      return { centrado: true, top: 0, left: 0 };
    }
    if (!rect) {
      return { centrado: true, top: 0, left: 0 };
    }
    const vw = window.innerWidth;
    const vh = window.innerHeight;
    const espacioAbajo = vh - (rect.top + rect.height);
    const colocarAbajo = espacioAbajo > TOOLTIP_H_EST + GAP || espacioAbajo > rect.top;
    let top = colocarAbajo ? rect.top + rect.height + GAP : rect.top - TOOLTIP_H_EST - GAP;
    let left = rect.left + rect.width / 2 - TOOLTIP_W / 2;
    left = Math.max(GAP, Math.min(left, vw - TOOLTIP_W - GAP));
    top = Math.max(GAP, Math.min(top, vh - TOOLTIP_H_EST - GAP));
    return { centrado: false, top, left };
  });
</script>

<svelte:window onkeydown={onKeydown} />

<div class="tour-root" transition:fade={{ duration: 150 }}>
  <!-- Recorte: caja transparente con sombra gigante que oscurece todo lo demás. -->
  {#if rect}
    <div
      class="tour-spotlight motion-safe:tour-pulse"
      style="
        top: {rect.top - PADDING}px;
        left: {rect.left - PADDING}px;
        width: {rect.width + PADDING * 2}px;
        height: {rect.height + PADDING * 2}px;
      "
    ></div>
  {:else}
    <!-- Sin elemento: oscurecer toda la pantalla. -->
    <div class="tour-backdrop-full"></div>
  {/if}

  <!-- Tooltip -->
  <div
    class="tour-tooltip"
    role="dialog"
    aria-modal="true"
    aria-labelledby="tour-titulo"
    tabindex="-1"
    transition:scale={{ duration: 160, start: 0.95 }}
    style={tooltipPos.centrado
      ? "top: 50%; left: 50%; transform: translate(-50%, -50%);"
      : `top: ${tooltipPos.top}px; left: ${tooltipPos.left}px;`}
  >
    <button class="tour-cerrar" onclick={onClose} aria-label="Saltar tour" title="Saltar tour">
      <X size={18} />
    </button>

    <div class="tour-icono" style="background-color: {pasoActual.color}1a; color: {pasoActual.color};">
      {#key indice}
        <span class="motion-safe:tour-bounce">
          <IconoActual size={26} aria-hidden="true" />
        </span>
      {/key}
    </div>

    <h3 id="tour-titulo" class="tour-titulo">{pasoActual.titulo}</h3>
    <p class="tour-desc">{pasoActual.descripcion}</p>

    <!-- Puntos de progreso -->
    <div class="tour-dots" role="tablist" aria-label="Pasos del tour">
      {#each pasos as _, i (i)}
        <button
          type="button"
          role="tab"
          aria-selected={i === indice}
          aria-label="Ir al paso {i + 1}"
          class="tour-dot"
          class:tour-dot--activo={i === indice}
          style={i === indice ? `background-color: ${pasoActual.color};` : ""}
          onclick={() => saltarAPaso(i)}
        ></button>
      {/each}
    </div>

<div class="tour-acciones">
      <label class="tour-no-mostrar">
        <input type="checkbox" bind:checked={noMostrarMas} onchange={onNoMostrarMasChange} />
        <span>No volver a mostrar</span>
      </label>
      <div class="tour-navegacion">
        <button class="tour-btn tour-btn--sec" onclick={anterior} disabled={esPrimero}>
          <ChevronLeft size={16} aria-hidden="true" />
          Anterior
        </button>
        <span class="tour-paso-lbl">{indice + 1} / {pasos.length}</span>
        <button class="tour-btn tour-btn--pri" style="background-color: {pasoActual.color};" onclick={siguiente}>
          {#if esUltimo}
            <Check size={16} aria-hidden="true" />
            Entendido!
          {:else}
            Siguiente
            <ChevronRight size={16} aria-hidden="true" />
          {/if}
        </button>
      </div>
    </div>
  </div>
</div>

<style>
  .tour-root {
    position: fixed;
    inset: 0;
    z-index: 200;
  }

  .tour-spotlight {
    position: fixed;
    border-radius: 12px;
    box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.6);
    border: 2px solid rgb(var(--accent-primary));
    pointer-events: none;
    transition: top 0.25s ease, left 0.25s ease, width 0.25s ease, height 0.25s ease;
  }

  .tour-backdrop-full {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.6);
    pointer-events: none;
  }

  :global(.tour-pulse) {
    animation: tourPulse 1.8s ease-in-out infinite;
  }

  @keyframes tourPulse {
    0%,
    100% {
      box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.6), 0 0 0 0 rgb(var(--accent-primary) / 0.45);
    }
    50% {
      box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.6), 0 0 0 8px rgb(var(--accent-primary) / 0);
    }
  }

  .tour-tooltip {
    position: fixed;
    width: 320px;
    max-width: calc(100vw - 24px);
    padding: 1.25rem;
    border-radius: 1rem;
    background-color: rgb(var(--card-bg));
    border: 1px solid rgb(var(--border-primary));
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    z-index: 201;
  }

  .tour-cerrar {
    position: absolute;
    top: 0.75rem;
    right: 0.75rem;
    width: 1.75rem;
    height: 1.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 9999px;
    color: rgb(var(--text-secondary));
    transition: background-color 0.15s;
  }

  .tour-cerrar:hover {
    background-color: rgb(var(--bg-secondary));
  }

  .tour-icono {
    width: 3rem;
    height: 3rem;
    border-radius: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 0.75rem;
  }

  :global(.tour-bounce) {
    display: inline-flex;
    animation: tourBounce 0.6s ease;
  }

  @keyframes tourBounce {
    0% {
      transform: scale(0.4);
      opacity: 0;
    }
    60% {
      transform: scale(1.12);
    }
    100% {
      transform: scale(1);
      opacity: 1;
    }
  }

  .tour-titulo {
    font-size: 1.05rem;
    font-weight: 700;
    color: rgb(var(--text-primary));
    margin: 0 0 0.35rem 0;
  }

  .tour-desc {
    font-size: 0.9rem;
    line-height: 1.5;
    color: rgb(var(--text-secondary));
    margin: 0 0 1rem 0;
  }

  .tour-dots {
    display: flex;
    gap: 0.4rem;
    justify-content: center;
    margin-bottom: 1rem;
  }

  .tour-dot {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    background-color: rgb(var(--border-primary));
    transition: all 0.2s;
  }

  .tour-dot--activo {
    width: 1.4rem;
  }

  .tour-acciones {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  .tour-no-mostrar {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.75rem;
    color: rgb(var(--text-secondary));
    cursor: pointer;
    user-select: none;
  }

  .tour-no-mostrar input {
    width: 14px;
    height: 14px;
    accent-color: rgb(var(--accent-primary));
    cursor: pointer;
  }

  .tour-navegacion {
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .tour-paso-lbl {
    font-size: 0.75rem;
    color: rgb(var(--text-secondary));
  }

  .tour-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
    padding: 0.5rem 0.9rem;
    border-radius: 0.6rem;
    font-size: 0.85rem;
    font-weight: 600;
    transition: opacity 0.15s, background-color 0.15s;
    min-height: 40px;
  }

  .tour-btn--sec {
    background-color: rgb(var(--bg-secondary));
    color: rgb(var(--text-primary));
    border: 1px solid rgb(var(--border-primary));
  }

  .tour-btn--sec:disabled {
    opacity: 0.4;
    cursor: not-allowed;
  }

  .tour-btn--pri {
    color: white;
  }

  .tour-btn--pri:hover {
    opacity: 0.92;
  }
</style>
