<script>
  let { size = 80, color = 'white' } = $props()
</script>

<div class="loader" style="--size: {size}px; --color: {color}"></div>

<style>
  .loader {
    width: var(--size);
    height: var(--size);
    border-radius: 50%;
    border: 3px solid transparent;
    border-top-color: var(--color);
    border-right-color: var(--color);
    animation: spin 0.7s ease-in-out infinite;
  }

  @keyframes spin {
    to {
      transform: rotate(360deg);
    }
  }
</style>
