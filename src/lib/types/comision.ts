export type Periodo = 'UNO' | 'DOS' | 'TRES' | 'CUATRO';

export type Decision = 'promovido' | 'no_promovido' | 'plan_mejoramiento' | 'normal' | 'pendiente';

export interface DecisionEstudiante {
  nombre: string;
  grado: string;
  materias: string[];
  decision: Decision;
  observaciones: string;
}

export interface ActaComision {
  fecha: string;
  periodo: Periodo;
  grado: string;
  presidente: string;
  docentes: string;
  estudiantes: DecisionEstudiante[];
  creado_por: string;
  timestamp: string;
}

export interface DecisionOption {
  value: Decision;
  label: string;
  icon: string;
  bgColor: string;
  textColor: string;
  darkBgColor: string;
  darkTextColor: string;
}

export const DECISIONES: DecisionOption[] = [
  {
    value: 'promovido',
    label: 'Promovido',
    icon: '✅',
    bgColor: 'bg-green-50',
    textColor: 'text-green-700',
    darkBgColor: 'dark:bg-green-950',
    darkTextColor: 'dark:text-green-300',
  },
  {
    value: 'no_promovido',
    label: 'No Promovido',
    icon: '❌',
    bgColor: 'bg-red-50',
    textColor: 'text-red-700',
    darkBgColor: 'dark:bg-red-950',
    darkTextColor: 'dark:text-red-300',
  },
  {
    value: 'plan_mejoramiento',
    label: 'Plan de Mejoramiento',
    icon: '📋',
    bgColor: 'bg-amber-50',
    textColor: 'text-amber-700',
    darkBgColor: 'dark:bg-amber-950',
    darkTextColor: 'dark:text-amber-300',
  },
  {
    value: 'normal',
    label: 'Normal',
    icon: '✓',
    bgColor: 'bg-blue-50',
    textColor: 'text-blue-700',
    darkBgColor: 'dark:bg-blue-950',
    darkTextColor: 'dark:text-blue-300',
  },
  {
    value: 'pendiente',
    label: 'Pendiente',
    icon: '⏳',
    bgColor: 'bg-zinc-100',
    textColor: 'text-zinc-600',
    darkBgColor: 'dark:bg-zinc-800',
    darkTextColor: 'dark:text-zinc-400',
  },
];

export const PERIDOS: Periodo[] = ['UNO', 'DOS', 'TRES', 'CUATRO'];