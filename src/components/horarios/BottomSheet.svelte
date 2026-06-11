<script lang="ts">
  import { X } from "@lucide/svelte";

  let {
    open = $bindable(false),
    title = "",
    onClose,
    maxHeight = "85vh",
    showHandle = true,
    children,
  }: {
    open?: boolean;
    title?: string;
    onClose?: () => void;
    maxHeight?: string;
    showHandle?: boolean;
    children?: import("svelte").Snippet;
  } = $props();

  let isDragging = $state(false);
  let startY = $state(0);
  let translateY = $state(0);

  function handleTouchStart(e: TouchEvent) {
    isDragging = true;
    startY = e.touches[0].clientY;
  }

  function handleTouchMove(e: TouchEvent) {
    if (!isDragging) return;
    const diff = e.touches[0].clientY - startY;
    if (diff > 0) {
      translateY = diff;
    }
  }

  function handleTouchEnd() {
    if (isDragging && translateY > 100) {
      open = false;
      if (onClose) onClose();
    }
    isDragging = false;
    translateY = 0;
  }

  function handleBackdropClick(e: MouseEvent) {
    if (e.target === e.currentTarget) {
      open = false;
      if (onClose) onClose();
    }
  }

  function handleKeydown(e: KeyboardEvent) {
    if (e.key === "Escape") {
      open = false;
      if (onClose) onClose();
    }
  }
</script>

<svelte:window onkeydown={handleKeydown} />

{#if open}
  <!-- svelte-ignore a11y_no_noninteractive_tabindex -->
  <div
    class="bs-backdrop"
    onclick={handleBackdropClick}
    onkeydown={handleKeydown}
    role="dialog"
    aria-modal="true"
    aria-labelledby={title ? "bs-title" : undefined}
    tabindex="0"
    style="--ty: {translateY}px;"
  >
    <div
      class="bs-content"
      style="max-height: {maxHeight}; transform: translateY({translateY}px);"
      ontouchstart={handleTouchStart}
      ontouchmove={handleTouchMove}
      ontouchend={handleTouchEnd}
      role="region"
      aria-label="Contenido del panel"
    >
      {#if showHandle}
        <div class="bs-handle">
          <div class="bs-handle-bar"></div>
        </div>
      {/if}

      {#if title}
        <div class="bs-header">
          <h3 id="bs-title" class="bs-title">{title}</h3>
          <button
            type="button"
            onclick={() => { open = false; if (onClose) onClose(); }}
            class="bs-close"
            aria-label="Cerrar"
          >
            <X size={20} />
          </button>
        </div>
      {/if}

      <div class="bs-body">
        {#if children}
          {@render children()}
        {/if}
      </div>
    </div>
  </div>
{/if}

<style>
  .bs-backdrop {
    position: fixed;
    inset: 0;
    z-index: 50;
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
    display: flex;
    align-items: flex-end;
    justify-content: center;
    animation: bs-fade-in 0.2s ease-out;
  }

  .bs-content {
    width: 100%;
    max-width: 100%;
    border-radius: 1.5rem 1.5rem 0 0;
    background-color: rgb(var(--bg-primary));
    border: 1px solid rgb(var(--border-primary));
    border-bottom: none;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease-out;
    animation: bs-slide-up 0.3s ease-out;
    overscroll-behavior: contain;
    touch-action: pan-y;
  }

  @media (min-width: 640px) {
    .bs-content {
      max-width: 28rem;
      border-radius: 1rem;
      border-bottom: 1px solid rgb(var(--border-primary));
      margin-bottom: 2rem;
    }
  }

  .bs-handle {
    display: flex;
    justify-content: center;
    padding: 0.75rem 0 0.5rem;
  }

  .bs-handle-bar {
    width: 2.5rem;
    height: 0.25rem;
    border-radius: 9999px;
    background-color: rgb(var(--border-primary));
  }

  .bs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.5rem 1rem 0.75rem;
    border-bottom: 1px solid rgb(var(--border-primary));
  }

  .bs-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: rgb(var(--text-primary));
    margin: 0;
  }

  .bs-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 9999px;
    background-color: rgb(var(--bg-secondary));
    color: rgb(var(--text-secondary));
    transition: all 0.2s;
    border: none;
    cursor: pointer;
  }

  .bs-close:hover {
    background-color: rgb(var(--border-primary));
    color: rgb(var(--text-primary));
  }

  .bs-body {
    flex: 1;
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
  }

  @keyframes bs-fade-in {
    from { opacity: 0; }
    to { opacity: 1; }
  }

  @keyframes bs-slide-up {
    from { transform: translateY(100%); }
    to { transform: translateY(0); }
  }
</style>