<script lang="ts">
  import { fade, slide } from "svelte/transition";

  interface Props {
    day: number;
    value: string;
    onSelect: (val: string) => void;
  }

  let { day, value, onSelect }: Props = $props();

  const categories = [
    { id: "normal", label: "Trabajo Normal", color: "bg-blue-500", icon: "💼" },
    {
      id: "ordinario",
      label: "Permiso Ordinario",
      color: "bg-green-500",
      icon: "📝",
    },
    {
      id: "sindical",
      label: "Permiso Sindical",
      color: "bg-purple-500",
      icon: "⚖️",
    },
    {
      id: "luto",
      label: "Licencia por Luto",
      color: "bg-gray-600",
      icon: "✝️",
    },
    {
      id: "medica",
      label: "Incapacidad Médica",
      color: "bg-red-500",
      icon: "🏥",
    },
    {
      id: "secretaria",
      label: "Capacitaciones Secretaría",
      color: "bg-amber-500",
      icon: "🎓",
    },
  ];
</script>

<div
  class="group grid grid-cols-1 md:grid-cols-7 items-center gap-2 p-3 rounded-xl transition-all duration-300 hover:bg-white/5 border border-transparent hover:border-white/10"
  in:fade={{ delay: day * 20 }}
>
  <div class="flex items-center gap-3">
    <span
      class="flex items-center justify-center w-8 h-8 rounded-full bg-white/10 text-white font-medium text-sm group-hover:scale-110 transition-transform"
    >
      {day}
    </span>
    <span
      class="md:hidden text-white/50 text-xs font-medium uppercase tracking-wider"
      >Actividad para el día {day}</span
    >
  </div>

  <div class="col-span-1 md:col-span-6 flex flex-wrap gap-2">
    {#each categories as category}
      <button
        onclick={() => onSelect(category.id)}
        class="relative flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition-all duration-300
          {value === category.id
          ? `${category.color} text-white shadow-lg shadow-${category.color.split('-')[1]}-500/30 scale-105`
          : 'bg-white/5 text-white/60 hover:bg-white/10 hover:text-white'}"
      >
        <span class="text-sm">{category.icon}</span>
        <span>{category.label}</span>

        {#if value === category.id}
          <div
            class="absolute -inset-0.5 rounded-lg opacity-30 blur-sm {category.color}"
            transition:fade
          ></div>
        {/if}
      </button>
    {/each}
  </div>
</div>
