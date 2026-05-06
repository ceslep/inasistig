<script lang="ts">
    import { fade } from "svelte/transition";

    let { message = "Cargando datos...", show = $bindable(false) } = $props();
</script>

{#if show}
    <div
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-50/60 backdrop-blur-md"
        transition:fade={{ duration: 300 }}
    >
        <div class="relative flex flex-col items-center gap-6">
            <!-- Premium Animated Loader -->
            <div class="relative w-24 h-24">
                <!-- Outer Glowing Ring -->
                <div
                    class="absolute inset-0 rounded-full border-4 border-slate-200"
                ></div>

                <!-- Spinning Gradient Ring -->
                <div
                    class="absolute inset-0 rounded-full border-4 border-transparent border-t-blue-600 border-r-purple-600 animate-spin"
                    style="filter: drop-shadow(0 0 8px rgba(37, 99, 235, 0.3))"
                ></div>

                <!-- Inner Pulse Core -->
                <div
                    class="absolute inset-4 rounded-full bg-gradient-to-tr from-blue-500/20 to-purple-500/20 flex items-center justify-center animate-pulse"
                >
                    <svg
                        class="w-8 h-8 text-blue-600/40"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="1.5"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                        />
                    </svg>
                </div>
            </div>

            <!-- Text Content -->
            <div class="text-center space-y-2">
                <h3 class="text-lg font-bold text-slate-900 tracking-tight">
                    {message}
                </h3>
                <p
                    class="text-xs text-slate-500 font-medium uppercase tracking-[0.2em] animate-pulse"
                >
                    Por favor espera un momento
                </p>
            </div>
        </div>
    </div>
{/if}

<style>
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
    .animate-spin {
        animation: spin 1s cubic-bezier(0.55, 0.17, 0.2, 0.89) infinite;
    }
</style>
