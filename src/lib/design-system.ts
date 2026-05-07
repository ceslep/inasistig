export const spacing = {
  xs: '4px',
  sm: '8px',
  md: '16px',
  lg: '24px',
  xl: '32px',
  '2xl': '48px',
  '3xl': '64px',
} as const;

export const borderRadius = {
  none: '0px',
  sm: '4px',
  md: '8px',
  lg: '12px',
  xl: '16px',
  '2xl': '24px',
  full: '9999px',
} as const;

export const shadows = {
  none: 'none',
  sm: '0 1px 2px 0 rgb(0 0 0 / 0.05)',
  md: '0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)',
  lg: '0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)',
  xl: '0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1)',
  '2xl': '0 25px 50px -12px rgb(0 0 0 / 0.25)',
  glow: (color: string) => `0 0 20px ${color}40, 0 0 40px ${color}20`,
} as const;

export const typography = {
  fontSize: {
    xs: '12px',
    sm: '14px',
    base: '16px',
    lg: '18px',
    xl: '20px',
    '2xl': '24px',
    '3xl': '30px',
    '4xl': '36px',
  },
  fontWeight: {
    normal: '400',
    medium: '500',
    semibold: '600',
    bold: '700',
  },
  lineHeight: {
    tight: '1.25',
    normal: '1.5',
    relaxed: '1.75',
  },
} as const;

export const transitions = {
  fast: '150ms ease',
  normal: '200ms ease',
  slow: '300ms ease',
  bounce: '300ms cubic-bezier(0.68, -0.55, 0.265, 1.55)',
} as const;

export const zIndex = {
  dropdown: '100',
  sticky: '200',
  fixed: '300',
  modalBackdrop: '400',
  modal: '500',
  popover: '600',
  tooltip: '700',
} as const;

export const categoryColors: Record<string, string> = {
  "CÁTEDRA DE LA PAZ": "#8b5cf6",
  "CIENCIAS NATURALES Y EDUCACIÓN AMBIENTAL": "#10b981",
  "CIENCIAS SOCIALES (HISTORIA, GEOGRAFÍA Y": "#f59e0b",
  "DIRECCIÓN DE GRUPO": "#3b82f6",
  "EDUCACIÓN ARTÍSTICA": "#f43f5e",
  "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES": "#ef4444",
  "EDUCACIÓN RELIGIOSA, ÉTICA Y V. HUMANOS": "#8b5cf6",
  "EMPRENDIMIENTO": "#f97316",
  "ESTADÍSTICA": "#06b6d4",
  "FILOSOFÍA Y CIENCIAS SOCIALES (CIENCIAS": "#6366f1",
  "FÍSICA": "#3b82f6",
  "INGLÉS": "#14b8a6",
  "LENGUA CASTELLANA": "#10b981",
  "MATEMÁTICAS": "#3b82f6",
  "PROYECTO Y EMPRENDIMIENTO": "#f97316",
  "QUÍMICA": "#06b6d4",
  "TECNOLOGÍA E INFORMÁTICA": "#6366f1",
  "ÉTICA PROFESIONAL": "#8b5cf6",
  "Estrategias de Enseñanza-Aprendizaje": "#6366f1",
  "Evaluación y Verificación de Saberes": "#10b981",
  "Enfoque STEM+ y Contexto Rural": "#f59e0b",
  "Prácticas de Laboratorio y Mantenimiento": "#f43f5e",
  "Ciudadanía Digital y Ética": "#8b5cf6",
  "Pensamiento Computacional y Programación": "#06b6d4",
  "Recursos Analógicos y Contingencias": "#f97316",
  "Ofimática y Competencias Productivas": "#14b8a6",
  "Gestión Administrativa y Proyectos Transversales": "#3b82f6",
};

export const getCategoryColor = (category: string): string => {
  return categoryColors[category] || "#6366f1";
};

export const animations = {
  fadeIn: `
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(5px); }
      to { opacity: 1; transform: translateY(0); }
    }
  `,
  scaleIn: `
    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }
  `,
  slideInRight: `
    @keyframes slideInRight {
      from { opacity: 0; transform: translateX(100%); }
      to { opacity: 1; transform: translateX(0); }
    }
  `,
  shimmer: `
    @keyframes shimmer {
      0% { background-position: -200% 0; }
      100% { background-position: 200% 0; }
    }
  `,
  pulse: `
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }
  `,
  bounce: `
    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-5px); }
    }
  `,
} as const;

export type Spacing = keyof typeof spacing;
export type BorderRadius = keyof typeof borderRadius;
export type Shadow = keyof typeof shadows;
export type TypographySize = keyof typeof typography.fontSize;
export type TypographyWeight = keyof typeof typography.fontWeight;