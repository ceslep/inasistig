export const materiaIcons: Record<string, string> = {
  MATEMÁTICAS: "📐",
  "LENGUA CASTELLANA": "📝",
  INGLÉS: "🌍",
  "CIENCIAS NATURALES": "🔬",
  "CIENCIAS NATURALES Y EDUCACIÓN AMBIENTAL": "🌿",
  "CIENCIAS SOCIALES": "🏛️",
  "CIENCIAS SOCIALES (HISTORIA, GEOGRAFÍA Y": "📜",
  FÍSICA: "⚡",
  QUÍMICA: "🧪",
  "EDUCACIÓN ARTÍSTICA": "🎨",
  "EDUCACIÓN FÍSICA": "⚽",
  "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES": "🏃",
  "TECNOLOGÍA": "💻",
  "TECNOLOGÍA E INFORMÁTICA": "🖥️",
  EMPRENDIMIENTO: "💼",
  FILOSOFÍA: "🤔",
  "FILOSOFÍA Y CIENCIAS SOCIALES": "📚",
  ESTADÍSTICA: "📊",
  "EDUCACIÓN RELIGIOSA": "⛪",
  "EDUCACIÓN RELIGIOSA, ÉTICA Y V. HUMANOS": "🕊️",
  ÉTICA: "⚖️",
  "ÉTICA PROFESIONAL": "🎯",
  "DIRECCIÓN DE GRUPO": "👥",
  "PROYECTO Y EMPRENDIMIENTO": "🚀",
  "CÁTEDRA DE LA PAZ": "🕊️",
};

export const materiaColors: Record<string, string> = {
  MATEMÁTICAS: "#3b82f6",
  "LENGUA CASTELLANA": "#10b981",
  INGLÉS: "#14b8a6",
  "CIENCIAS NATURALES": "#10b981",
  "CIENCIAS NATURALES Y EDUCACIÓN AMBIENTAL": "#22c55e",
  "CIENCIAS SOCIALES": "#f59e0b",
  "CIENCIAS SOCIALES (HISTORIA, GEOGRAFÍA Y": "#f59e0b",
  FÍSICA: "#6366f1",
  QUÍMICA: "#06b6d4",
  "EDUCACIÓN ARTÍSTICA": "#f43f5e",
  "EDUCACIÓN FÍSICA": "#ef4444",
  "EDUCACIÓN FÍSICA, RECREACIÓN Y DEPORTES": "#dc2626",
  "TECNOLOGÍA": "#8b5cf6",
  "TECNOLOGÍA E INFORMÁTICA": "#7c3aed",
  EMPRENDIMIENTO: "#f97316",
  FILOSOFÍA: "#6366f1",
  "FILOSOFÍA Y CIENCIAS SOCIALES": "#4f46e5",
  ESTADÍSTICA: "#0ea5e9",
  "EDUCACIÓN RELIGIOSA": "#a855f7",
  "EDUCACIÓN RELIGIOSA, ÉTICA Y V. HUMANOS": "#9333ea",
  ÉTICA: "#8b5cf6",
  "ÉTICA PROFESIONAL": "#7c3aed",
  "DIRECCIÓN DE GRUPO": "#2563eb",
  "PROYECTO Y EMPRENDIMIENTO": "#ea580c",
  "CÁTEDRA DE LA PAZ": "#06b6d4",
};

export const gradoIcons: Record<string, string> = {
  "0": "🔰",
  "1": "1️⃣",
  "2": "2️⃣",
  "3": "3️⃣",
  "4": "4️⃣",
  "5": "5️⃣",
  "6": "6️⃣",
  "7": "7️⃣",
  "8": "8️⃣",
  "9": "9️⃣",
  "10": "🔟",
};

export const docenteIcons: Record<string, string> = {
  default: "👨‍🏫",
};

export function getMateriaIcon(materia: string): string {
  const upper = materia.toUpperCase();
  for (const [key, icon] of Object.entries(materiaIcons)) {
    if (upper.includes(key.toUpperCase()) || key.toUpperCase().includes(upper)) {
      return icon;
    }
  }
  return "📖";
}

export function getMateriaColor(materia: string): string {
  const upper = materia.toUpperCase();
  for (const [key, color] of Object.entries(materiaColors)) {
    if (upper.includes(key.toUpperCase()) || key.toUpperCase().includes(upper)) {
      return color;
    }
  }
  return "#6366f1";
}

export function getGradoIcon(grado: string): string {
  const num = grado.replace(/[^0-9]/g, "");
  return gradoIcons[num] || "📚";
}

export function getDocenteIcon(): string {
  return docenteIcons.default;
}