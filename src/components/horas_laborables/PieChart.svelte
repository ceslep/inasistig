<script lang="ts">
  import { onMount, onDestroy } from "svelte";
  import Chart from "chart.js/auto";

  interface Category {
    id: string;
    label: string;
    color: string;
    icon: string;
  }

// ✅ Props en modo runes: sin export let, sin genéricos, sin argumentos
  /** @type {{ data?: Record<string, number>, categories?: Category[], title?: string }} */
  const props = $props();

  // ✅ Usar snapshot para obtener datos reales del proxy
  const data = $derived.by(() => $state.snapshot(props.data ?? {}));
  const categories = props.categories ?? [];
  const title = props.title ?? "";

  // ✅ Referencia al canvas: no reactivo → usar $state.raw
  let chartElement = $state.raw<HTMLCanvasElement | undefined>(undefined);
  let chartInstance: any = null;

  // Mapeo de colores Tailwind a hex
  function getTailwindColor(colorClass: string): string {
    const colorMap: Record<string, string> = {
      "bg-blue-500": "#3b82f6",
      "bg-green-500": "#10b981",
      "bg-purple-500": "#a855f7",
      "bg-emerald-600": "#059669",
      "bg-cyan-600": "#0891b2",
      "bg-rose-500": "#f43f5e",
      "bg-stone-500": "#78716c",
      "bg-slate-600": "#475569",
      "bg-red-500": "#ef4444",
      "bg-teal-500": "#14b8a6",
      "bg-amber-500": "#f59e0b",
      "bg-pink-500": "#ec4899",
      "bg-violet-500": "#8b5cf6",
      "bg-orange-500": "#f97316",
      "bg-yellow-500": "#eab308",
      "bg-indigo-600": "#4f46e5",
      "bg-sky-500": "#0ea5e9",
      "bg-red-700": "#b91c1c",
      "bg-orange-700": "#c2410c",
      "bg-slate-400": "#94a3b8",
    };
    return colorMap[colorClass] || "#6b7280";
  }

function createChart() {
    const currentData = data;
    if (!chartElement || Object.keys(currentData).length === 0) {
      return;
    }

    const labels = Object.keys(currentData).map((key) => {
      const category = categories.find((cat: Category) => cat.id === key);
      return category ? category.label : key;
    });

    const values = Object.values(currentData);
    const colors = Object.keys(currentData).map((key) => {
      const category = categories.find((cat: Category) => cat.id === key);
      return category ? getTailwindColor(category.color) : "#6b7280";
    });

    if (chartInstance) {
      chartInstance.destroy();
    }

    // ✅ ¡Sintaxis CORRECTA para Chart.js!
    chartInstance = new Chart(chartElement, {
      type: "pie",
      data: {
        labels,
        datasets: [
          {
            data: values,
            backgroundColor: colors,
            borderColor: "#ffffff",
            borderWidth: 2,
            hoverOffset: 4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
            labels: {
              padding: 15,
              font: {
                size: 11,
              },
            },
          },
          tooltip: {
            callbacks: {
              label: function (context: any) {
                const label = context.label || "";
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce(
                  (sum: number, val: number) => sum + val,
                  0,
                );
                const percentage =
                  total > 0 ? ((value / total) * 100).toFixed(1) : "0";
                return `${label}: ${value} días (${percentage}%)`;
              },
            },
          },
        },
      },
    });
  }

onMount(() => {
    const currentData = data;
    if (chartElement && Object.keys(currentData).length > 0) {
      createChart();
    }
  });

  onDestroy(() => {
    if (chartInstance) {
      chartInstance.destroy();
      chartInstance = null;
    }
  });

// ✅ Reactividad con runes
  $effect(() => {
    const currentData = data;
    console.log("PieChart $effect: Checking data for rendering. Data:", currentData, "isEmpty:", Object.keys(currentData).length === 0);
    if (chartElement) {
      if (Object.keys(currentData).length > 0) {
        createChart();
      } else if (chartInstance) {
        chartInstance.destroy();
        chartInstance = null;
      }
    }
  });
</script>

{#each [data] as currentData}
  {#if Object.keys(currentData).length > 0}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-lg">
      <h3 class="text-lg font-semibold text-slate-800 mb-4">{title}</h3>
      <div class="relative" style="height: 300px;">
        <canvas bind:this={chartElement}></canvas>
      </div>
      <div class="mt-4 text-center text-sm text-slate-500">
        Total: {Object.values(currentData).reduce(
          (sum: number, value: unknown) => sum + (value as number),
          0,
        )} días registrados
      </div>
    </div>
  {:else}
    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-lg">
      <h3 class="text-lg font-semibold text-slate-800 mb-4">{title}</h3>
      <div class="flex items-center justify-center h-64 text-slate-400">
        <div class="text-center">
          <div class="text-4xl mb-2">📊</div>
          <p class="text-sm">No hay datos disponibles para mostrar</p>
        </div>
      </div>
    </div>
  {/if}
{/each}

<style>
  canvas {
    max-height: 300px;
    width: 100% !important;
    height: 100% !important;
  }
</style>
