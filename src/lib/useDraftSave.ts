const DEFAULT_KEY = 'anotador_draft';
const SAVE_DELAY = 2000;

export interface DraftData {
  docente: string;
  materia: string;
  grado: string;
  horas: string;
  fecha: string;
  anotacion: string;
  observacion: string;
  timestamp: number;
}

export interface DiarioDraftData {
  formData: {
    fecha: string;
    docente: string;
    materia: string;
    grado: string;
    horas: string;
  };
  selectedDiarioAnots: string[];
  selectedMaterias: { materia: string; horas: string }[];
  timestamp: number;
}

export interface InasistenciaDraftData {
  formData: {
    docente: string;
    materia: string;
    horas: string;
    grado: string;
    fecha: string;
    observaciones: string;
  };
  inasistencias: {
    nombre: string;
    motivo: string;
    horas: string;
    observaciones: string;
  }[];
  timestamp: number;
}

export function useDraftSave(customKey?: string) {
  const STORAGE_KEY = customKey || DEFAULT_KEY;
  let saveTimeout: ReturnType<typeof setTimeout> | null = null;

  const saveDraft = (data: Partial<DraftData> | Partial<DiarioDraftData> | Partial<InasistenciaDraftData>) => {
    if (saveTimeout) {
      clearTimeout(saveTimeout);
    }
    
    saveTimeout = setTimeout(() => {
      try {
        if ('formData' in data && 'selectedDiarioAnots' in data) {
          // Diario draft
          const draft: DiarioDraftData = {
            formData: data.formData as DiarioDraftData['formData'],
            selectedDiarioAnots: data.selectedDiarioAnots || [],
            selectedMaterias: data.selectedMaterias || [],
            timestamp: Date.now(),
          };
          localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
        } else if ('formData' in data && 'inasistencias' in data) {
          // Inasistencia draft
          const draft: InasistenciaDraftData = {
            formData: data.formData as InasistenciaDraftData['formData'],
            inasistencias: data.inasistencias || [],
            timestamp: Date.now(),
          };
          localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
        } else {
          // Anotador draft
          const d = data as Partial<DraftData>;
          const draft: DraftData = {
            docente: d.docente || '',
            materia: d.materia || '',
            grado: d.grado || '',
            horas: d.horas || '',
            fecha: d.fecha || new Date().toISOString().split('T')[0],
            anotacion: d.anotacion || '',
            observacion: d.observacion || '',
            timestamp: Date.now(),
          };
          localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
        }
      } catch (e) {
        console.warn('No se pudo guardar el borrador:', e);
      }
    }, SAVE_DELAY);
  };

  const loadDraft = (): DraftData | DiarioDraftData | InasistenciaDraftData | null => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const draft = JSON.parse(saved);
        const hoursSinceSave = (Date.now() - draft.timestamp) / (1000 * 60 * 60);
        if (hoursSinceSave < 24) {
          return draft;
        }
        clearDraft();
      }
    } catch (e) {
      console.warn('No se pudo cargar el borrador:', e);
    }
    return null;
  };

  const clearDraft = () => {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
      console.warn('No se pudo eliminar el borrador:', e);
    }
  };

  const hasDraft = (): boolean => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      return !!saved;
    } catch {
      return false;
    }
  };

  return {
    saveDraft,
    loadDraft,
    clearDraft,
    hasDraft,
  };
}