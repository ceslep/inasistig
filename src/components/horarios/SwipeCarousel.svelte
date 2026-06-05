<script lang="ts">
  let {
    currentStep = $bindable(0),
    totalSteps = 3,
    onStepChange,
    children,
  }: {
    currentStep?: number;
    totalSteps?: number;
    onStepChange?: (step: number) => void;
    children?: import("svelte").Snippet[];
  } = $props();

  let startX = $state(0);
  let currentX = $state(0);
  let isDragging = $state(false);
  let translateX = $state(0);

  function goToStep(step: number) {
    if (step >= 0 && step < totalSteps) {
      currentStep = step;
      if (onStepChange) onStepChange(step);
    }
  }

  function handleTouchStart(e: TouchEvent) {
    isDragging = true;
    startX = e.touches[0].clientX;
    currentX = e.touches[0].clientX;
  }

  function handleTouchMove(e: TouchEvent) {
    if (!isDragging) return;
    currentX = e.touches[0].clientX;
    const diff = currentX - startX;
    translateX = diff * 0.3;
  }

  function handleTouchEnd() {
    if (!isDragging) return;
    const diff = currentX - startX;
    const threshold = 80;

    if (diff < -threshold && currentStep < totalSteps - 1) {
      goToStep(currentStep + 1);
    } else if (diff > threshold && currentStep > 0) {
      goToStep(currentStep - 1);
    }

    isDragging = false;
    translateX = 0;
  }

  function handleDotClick(step: number) {
    goToStep(step);
  }
</script>

<div class="swipe-container">
  <div
    class="swipe-track"
    style="transform: translateX(calc(-{currentStep * 100}% + {translateX}px));"
    ontouchstart={handleTouchStart}
    ontouchmove={handleTouchMove}
    ontouchend={handleTouchEnd}
  >
    {#if children}
      {#each children as child}
        <div class="swipe-page">
          {@render child()}
        </div>
      {/each}
    {/if}
  </div>

  {#if totalSteps > 1}
    <div class="swipe-dots" role="tablist" aria-label="Pasos">
      {#each Array(totalSteps) as _, i}
        <button
          type="button"
          role="tab"
          aria-selected={currentStep === i}
          aria-label="Paso {i + 1}"
          onclick={() => handleDotClick(i)}
          class="swipe-dot"
          class:active={currentStep === i}
          style="--dot-opacity: {currentStep === i ? 1 : 0.4};"
        >
          <span class="swipe-dot-inner"></span>
          {#if currentStep === i}
            <span class="sr-only">(actual)</span>
          {/if}
        </button>
      {/each}
    </div>
  {/if}
</div>

<style>
  .swipe-container {
    position: relative;
    width: 100%;
    overflow: hidden;
  }

  .swipe-track {
    display: flex;
    transition: transform 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    will-change: transform;
  }

  .swipe-page {
    flex: 0 0 100%;
    width: 100%;
    min-height: 200px;
  }

  .swipe-dots {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 0;
  }

  .swipe-dot {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2rem;
    height: 2rem;
    background: transparent;
    border: none;
    cursor: pointer;
    padding: 0;
    transition: all 0.2s;
  }

  .swipe-dot-inner {
    width: 0.5rem;
    height: 0.5rem;
    border-radius: 9999px;
    background-color: rgb(var(--accent-primary));
    opacity: var(--dot-opacity, 0.4);
    transition: all 0.2s;
  }

  .swipe-dot.active .swipe-dot-inner {
    width: 1.5rem;
    height: 0.5rem;
    opacity: 1;
  }

  .sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
  }
</style>